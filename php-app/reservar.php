<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    $formErrors = ['Este recurso solo acepta solicitudes de reserva enviadas mediante POST.'];
    require __DIR__ . '/index.php';
    exit;
}

$formValues = [];
foreach (['guest_name', 'guest_email', 'room', 'check_in', 'check_out', 'guests', 'notes'] as $field) {
    $value = $_POST[$field] ?? '';
    $formValues[$field] = is_string($value) ? trim($value) : '';
}

$submittedToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';
$formErrors = [];

if (!is_string($submittedToken) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $submittedToken)) {
    http_response_code(403);
    $formErrors[] = 'La solicitud venció o el código de seguridad no es válido. Vuelve a cargar la página e inténtalo otra vez.';
} else {
    $rooms = hotelRooms();
    $guestName = $formValues['guest_name'];
    $guestEmail = $formValues['guest_email'];
    $roomId = $formValues['room'];
    $checkIn = parseHotelDate($formValues['check_in']);
    $checkOut = parseHotelDate($formValues['check_out']);
    $guestsValue = $formValues['guests'];
    $notes = $formValues['notes'];

    if (!isValidTextLength($guestName, 2, 100) || preg_match('/[\x00-\x1F\x7F]/u', $guestName) === 1) {
        $formErrors[] = 'Escribe un nombre de entre 2 y 100 caracteres.';
    }

    if (strlen($guestEmail) > 254 || filter_var($guestEmail, FILTER_VALIDATE_EMAIL) === false) {
        $formErrors[] = 'Ingresa un correo electrónico válido.';
    }

    if (!isset($rooms[$roomId])) {
        $formErrors[] = 'Selecciona una habitación disponible.';
    }

    if ($checkIn === null || $checkIn->format('Y-m-d') < (new DateTimeImmutable('today', new DateTimeZone('America/Lima')))->format('Y-m-d')) {
        $formErrors[] = 'Selecciona una fecha de llegada válida, a partir de hoy.';
    }

    if ($checkOut === null || ($checkIn !== null && $checkOut <= $checkIn)) {
        $formErrors[] = 'La salida debe ser posterior a la fecha de llegada.';
    } elseif ($checkIn !== null && $checkIn->diff($checkOut)->days > 30) {
        $formErrors[] = 'La estancia no puede superar 30 noches.';
    }

    if ($guestsValue === '' || !ctype_digit($guestsValue) || (int) $guestsValue < 1 || (int) $guestsValue > 4) {
        $formErrors[] = 'Selecciona entre 1 y 4 huéspedes.';
    } elseif (isset($rooms[$roomId]) && (int) $guestsValue > $rooms[$roomId]['capacity']) {
        $formErrors[] = 'La habitación seleccionada admite hasta '.$rooms[$roomId]['capacity'].' huéspedes.';
    }

    if (!isValidTextLength($notes, 0, 600) || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', $notes) === 1) {
        $formErrors[] = 'La preferencia debe tener como máximo 600 caracteres.';
    }
}

if ($formErrors !== []) {
    http_response_code(http_response_code() === 200 ? 422 : http_response_code());
    require __DIR__ . '/index.php';
    exit;
}

$room = $rooms[$roomId];
$nights = $checkIn->diff($checkOut)->days;
$reservation = [
    'reference' => 'AUR-'.strtoupper(bin2hex(random_bytes(4))),
    'guest_name' => $guestName,
    'guest_email' => $guestEmail,
    'room' => $roomId,
    'room_name' => $room['name'],
    'rate' => $room['rate'],
    'check_in' => $checkIn->format('d/m/Y'),
    'check_out' => $checkOut->format('d/m/Y'),
    'guests' => $guestsValue,
    'nights' => $nights,
    'total' => $room['rate'] * $nights,
    'notes' => $notes,
    'created_at' => (new DateTimeImmutable('now', new DateTimeZone('America/Lima')))->format('d/m/Y H:i'),
];

if (!isset($_SESSION['reservas']) || !is_array($_SESSION['reservas'])) {
    $_SESSION['reservas'] = [];
}
$_SESSION['reservas'][] = $reservation;
$_SESSION['reservas'] = array_slice($_SESSION['reservas'], -50);
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$_SESSION['reserva_recibida'] = ['reference' => $reservation['reference']];

header('Location: index.php#mis-reservas', true, 303);
exit;
