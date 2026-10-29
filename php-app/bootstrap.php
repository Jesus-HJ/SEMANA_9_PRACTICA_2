<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);

    if (!session_start()) {
        http_response_code(500);
        exit('No se pudo iniciar la sesión.');
    }
}

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self'; form-action 'self'; base-uri 'self'; frame-ancestors 'none'");

set_exception_handler(static function (Throwable $exception): void {
    error_log(sprintf(
        'Unhandled application exception: %s in %s:%d',
        get_class($exception),
        basename($exception->getFile()),
        $exception->getLine()
    ));

    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'self'; style-src 'self'; form-action 'self'; base-uri 'self'; frame-ancestors 'none'");
    }

    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Aurelia Hotel &amp; Suites</title><link rel="stylesheet" href="assets/styles.css"></head><body><header class="site-header"><a class="brand" href="index.php"><span class="brand-mark">A</span><span class="brand-copy"><strong>AURELIA</strong><small>HOTEL &amp; SUITES</small></span></a></header><main class="error-page"><p class="eyebrow">UN MOMENTO, POR FAVOR</p><h1>Estamos preparando <em>todo para ti.</em></h1><p>No pudimos completar la solicitud. Inténtalo de nuevo en unos momentos.</p><a class="button button-dark" href="index.php">Volver a Aurelia <span aria-hidden="true">↗</span></a></main></body></html>';
});

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function ensureCsrfToken(): string
{
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function isValidTextLength(string $value, int $min, int $max): bool
{
    return preg_match('/\A.{'.$min.','.$max.'}\z/us', $value) === 1;
}

function hotelRooms(): array
{
    return [
        'deluxe' => [
            'name' => 'Deluxe Room',
            'image' => 'assets/images/deluxe-room.png',
            'description' => 'Una pausa luminosa con vistas panorámicas y detalles pensados para descansar.',
            'rate' => 280,
            'capacity' => 2,
            'beds' => 'Cama king',
            'features' => ['Cama king', 'Vista panorámica', 'Wi-Fi'],
        ],
        'premium-twin' => [
            'name' => 'Premium Twin',
            'image' => 'assets/images/premium-twin.png',
            'description' => 'Espacio contemporáneo con dos camas y la comodidad ideal para compartir.',
            'rate' => 350,
            'capacity' => 2,
            'beds' => 'Dos camas',
            'features' => ['Dos camas', 'Smart TV', 'Wi-Fi'],
        ],
        'junior-suite' => [
            'name' => 'Junior Suite',
            'image' => 'assets/images/junior-suite.png',
            'description' => 'Una estancia más amplia que combina una sala privada y una suite acogedora.',
            'rate' => 480,
            'capacity' => 3,
            'beds' => 'King + sala',
            'features' => ['Sala de estar', 'Minibar', 'Desayuno'],
        ],
        'royal-suite' => [
            'name' => 'Royal Suite',
            'image' => 'assets/images/royal-suite.png',
            'description' => 'Una suite exclusiva con jacuzzi privado y vistas panorámicas para compartir.',
            'rate' => 750,
            'capacity' => 4,
            'beds' => 'Suite exclusiva',
            'features' => ['Jacuzzi privado', 'Vista panorámica', '4 personas'],
        ],
    ];
}

function parseHotelDate(string $value): ?DateTimeImmutable
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone('America/Lima'));
    $dateErrors = DateTimeImmutable::getLastErrors();

    if ($date === false
        || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
        || $date->format('Y-m-d') !== $value
    ) {
        return null;
    }

    return $date;
}

function hotelMoney(int $amount): string
{
    return number_format($amount, 0, ',', '.');
}
