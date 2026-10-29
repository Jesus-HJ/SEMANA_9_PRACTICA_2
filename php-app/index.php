<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
$csrfToken = ensureCsrfToken();
$formValues = isset($formValues) && is_array($formValues) ? $formValues : [];
$formErrors = isset($formErrors) && is_array($formErrors) ? $formErrors : [];
$reservationNotice = $_SESSION['reserva_recibida'] ?? null;
unset($_SESSION['reserva_recibida']);
$reservations = isset($_SESSION['reservas']) && is_array($_SESSION['reservas'])
    ? $_SESSION['reservas']
    : [];
$rooms = hotelRooms();
$today = (new DateTimeImmutable('today', new DateTimeZone('America/Lima')))->format('Y-m-d');
$tomorrow = (new DateTimeImmutable('tomorrow', new DateTimeZone('America/Lima')))->format('Y-m-d');
$selectedRoom = isset($formValues['room']) && isset($rooms[$formValues['room']])
    ? $formValues['room']
    : 'deluxe';
$selectedGuests = isset($formValues['guests']) && ctype_digit((string) $formValues['guests'])
    ? (int) $formValues['guests']
    : 2;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f6f4ee">
    <meta name="description" content="Descubre Aurelia Hotel & Suites y planifica una estancia inolvidable con habitaciones exclusivas y vistas panorámicas.">
    <title>Aurelia Hotel &amp; Suites | Una estancia extraordinaria</title>
    <link rel="stylesheet" href="assets/styles.css">
    <script src="assets/reservas.js" defer></script>
</head>
<body>
    <header class="site-header" id="inicio">
        <a class="brand" href="index.php" aria-label="Aurelia Hotel & Suites, inicio">
            <span class="brand-mark" aria-hidden="true">A</span>
            <span class="brand-copy"><strong>AURELIA</strong><small>HOTEL &amp; SUITES</small></span>
        </a>
        <button class="menu-toggle" type="button" aria-label="Abrir menú" aria-expanded="false" aria-controls="primary-navigation">☰</button>
        <nav class="site-nav" id="primary-navigation" aria-label="Navegación principal">
            <a href="#habitaciones">Habitaciones</a>
            <a href="#experiencia">La experiencia</a>
            <a href="#mis-reservas">Mis solicitudes</a>
            <a class="nav-cta" href="#reservar">Reservar estancia <span aria-hidden="true">↗</span></a>
        </nav>
    </header>

    <main>
        <section class="hero" aria-labelledby="page-title">
            <div class="hero-image">
                <img src="assets/images/hotel-lobby.png" alt="Vestíbulo de Aurelia Hotel & Suites, con iluminación cálida y recepción de mármol" fetchpriority="high">
                <div class="hero-image-shade"></div>
                <p class="hero-image-caption"><span></span> HOSPITALIDAD CON HISTORIA · HUANCAYO, PERÚ</p>
            </div>
            <div class="hero-brand-panel">
                <div>
                    <p class="hero-kicker">UNA ESTANCIA EXTRAORDINARIA</p>
                    <h1 id="page-title">AURELIA</h1>
                    <p class="hero-subtitle">HOTEL &amp; SUITES</p>
                </div>
                <div class="hero-panel-aside">
                    <p>Tu próxima experiencia<br>comienza aquí.</p>
                    <a class="button button-light" href="#habitaciones">Descubrir habitaciones <span aria-hidden="true">↓</span></a>
                </div>
            </div>
        </section>

        <section class="intro-section page-width" aria-labelledby="intro-title">
            <div>
                <p class="eyebrow">EL ARTE DE RECIBIR</p>
                <h2 id="intro-title">Donde cada detalle<br>te hace sentir <em>en casa.</em></h2>
            </div>
            <p class="intro-copy">Descubre una forma distinta de hospedarte. Habitaciones serenas, una atención que se anticipa a ti y la belleza de Huancayo como telón de fondo.</p>
        </section>

        <section class="rooms-section page-width" id="habitaciones" aria-labelledby="rooms-title">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">DESCANSO A TU MEDIDA</p>
                    <h2 id="rooms-title">Habitaciones <em>&amp; suites</em></h2>
                </div>
                <p class="section-caption">Espacios diseñados para que cada noche sea parte del viaje. Tarifas referenciales en soles peruanos; imágenes y reservas ilustrativas.</p>
            </div>
            <div class="room-grid">
                <?php foreach ($rooms as $roomId => $room): ?>
                    <article class="room-card">
                        <a class="room-image-link" href="#reservar" data-room-choice="<?= escapeHtml($roomId) ?>" aria-label="Elegir <?= escapeHtml($room['name']) ?>">
                            <img class="room-image" src="<?= escapeHtml($room['image']) ?>" alt="<?= escapeHtml($room['name'].': '.$room['description']) ?>" loading="lazy">
                            <span class="room-image-arrow" aria-hidden="true">↗</span>
                        </a>
                        <div class="room-card-body">
                            <div class="room-title-row"><h3><?= escapeHtml($room['name']) ?></h3><span><?= escapeHtml((string) $room['capacity']) ?> huéspedes</span></div>
                            <p><?= escapeHtml($room['description']) ?></p>
                            <ul class="room-features" aria-label="Características"><?php foreach ($room['features'] as $feature): ?><li><?= escapeHtml($feature) ?></li><?php endforeach; ?></ul>
                            <div class="room-card-footer">
                                <p class="room-rate"><strong>S/ <?= hotelMoney($room['rate']) ?></strong><span> / noche</span></p>
                                <a class="room-link" href="#reservar" data-room-choice="<?= escapeHtml($roomId) ?>">Elegir habitación <span aria-hidden="true">→</span></a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="experience-section" id="experiencia" aria-labelledby="experience-title">
            <img src="assets/images/resort-sunset.png" alt="Una suite de Aurelia y una vista de atardecer sobre las montañas" loading="lazy">
            <div class="experience-overlay"></div>
            <div class="experience-copy page-width">
                <p class="eyebrow">EL PRIVILEGIO DE DETENERSE</p>
                <h2 id="experience-title">El tiempo<br>también <em>descansa.</em></h2>
                <p>Despierta con vistas que invitan a quedarse un poco más.</p>
                <a class="button button-light" href="#reservar">Planificar mi estancia <span aria-hidden="true">↗</span></a>
            </div>
            <span class="experience-caption">UN ATARDECER AURELIA</span>
        </section>

        <section class="amenities page-width" aria-label="Servicios incluidos">
            <article><span aria-hidden="true">✳</span><div><h3>Descanso auténtico</h3><p>Textiles suaves y espacios pensados para desconectar.</p></div></article>
            <article><span aria-hidden="true">⌁</span><div><h3>Atención personal</h3><p>Un equipo atento a cada detalle de tu estancia.</p></div></article>
            <article><span aria-hidden="true">◈</span><div><h3>Huancayo a tu ritmo</h3><p>Una ubicación ideal para explorar y volver a relajarte.</p></div></article>
        </section>

        <section class="booking-section" id="reservar" aria-labelledby="booking-title">
            <div class="booking-layout page-width">
                <div class="booking-copy">
                    <p class="eyebrow">TU PRÓXIMA ESCAPADA</p>
                    <h2 id="booking-title">Haz espacio<br>para <em>algo especial.</em></h2>
                    <p>Cuéntanos cómo imaginas tu estancia. Prepararemos tu solicitud para que nuestro equipo pueda confirmar los detalles contigo.</p>
                    <div class="booking-assurance"><span aria-hidden="true">✧</span> Sin compromiso. Los datos se utilizan únicamente para esta solicitud.</div>
                </div>

                <div class="booking-card">
                    <div class="booking-card-heading">
                        <div><p class="eyebrow">PLANIFICA TU ESTANCIA</p><h3>Solicita tu reserva</h3></div>
                        <span class="booking-monogram" aria-hidden="true">A</span>
                    </div>

                    <?php if ($formErrors !== []): ?>
                        <div class="form-alert" role="alert">
                            <strong>No pudimos preparar la solicitud.</strong>
                            <ul><?php foreach ($formErrors as $error): ?><li><?= escapeHtml($error) ?></li><?php endforeach; ?></ul>
                        </div>
                    <?php endif; ?>
                    <form action="reservar.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?= escapeHtml($csrfToken) ?>">
                        <div class="form-grid">
                            <div class="form-field field-full">
                                <label for="guest_name">Nombre completo</label>
                                <input class="form-control" id="guest_name" name="guest_name" type="text" autocomplete="name" minlength="2" maxlength="100" value="<?= escapeHtml($formValues['guest_name'] ?? '') ?>" placeholder="¿A nombre de quién reservamos?" required>
                            </div>
                            <div class="form-field field-full">
                                <label for="guest_email">Correo electrónico</label>
                                <input class="form-control" id="guest_email" name="guest_email" type="email" autocomplete="email" maxlength="254" value="<?= escapeHtml($formValues['guest_email'] ?? '') ?>" placeholder="nombre@ejemplo.com" required>
                            </div>
                            <div class="form-field field-full">
                                <label for="room">Habitación</label>
                                <select class="form-control" id="room" name="room" required>
                                    <?php foreach ($rooms as $roomId => $room): ?>
                                        <option value="<?= escapeHtml($roomId) ?>" data-rate="<?= escapeHtml((string) $room['rate']) ?>" data-capacity="<?= escapeHtml((string) $room['capacity']) ?>" <?= $selectedRoom === $roomId ? 'selected' : '' ?>><?= escapeHtml($room['name']) ?> · S/ <?= hotelMoney($room['rate']) ?> / noche</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-field">
                                <label for="check_in">Llegada</label>
                                <input class="form-control" id="check_in" name="check_in" type="date" min="<?= escapeHtml($today) ?>" value="<?= escapeHtml($formValues['check_in'] ?? $today) ?>" required>
                            </div>
                            <div class="form-field">
                                <label for="check_out">Salida</label>
                                <input class="form-control" id="check_out" name="check_out" type="date" min="<?= escapeHtml($tomorrow) ?>" value="<?= escapeHtml($formValues['check_out'] ?? $tomorrow) ?>" required>
                            </div>
                            <div class="form-field field-full">
                                <label for="guests">Número de huéspedes</label>
                                <select class="form-control" id="guests" name="guests" required>
                                    <?php for ($guestCount = 1; $guestCount <= 4; $guestCount++): ?>
                                        <option value="<?= $guestCount ?>" <?= $selectedGuests === $guestCount ? 'selected' : '' ?>><?= $guestCount ?> <?= $guestCount === 1 ? 'huésped' : 'huéspedes' ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="form-field field-full">
                                <label for="notes">¿Alguna preferencia especial? <span>(opcional)</span></label>
                                <textarea class="form-control form-message" id="notes" name="notes" rows="3" maxlength="600" placeholder="Cuéntanos si tienes alguna solicitud para tu estancia."><?= escapeHtml($formValues['notes'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="stay-summary" aria-live="polite">
                            <div><span id="night-count">1 noche</span><small> Tarifa referencial · sujeta a confirmación</small></div>
                            <strong id="stay-total">S/ 280</strong>
                        </div>
                        <button class="button button-dark booking-submit" type="submit">Solicitar reserva <span aria-hidden="true">↗</span></button>
                        <p class="booking-disclaimer">La solicitud se guarda de forma temporal en este navegador. La reserva no queda confirmada hasta que Aurelia valide disponibilidad y precio.</p>
                    </form>
                </div>
            </div>
        </section>

        <section class="reservations-section page-width" id="mis-reservas" aria-labelledby="reservations-title">
            <div class="section-heading">
                <div><p class="eyebrow">TU VIAJE, A TU ALCANCE</p><h2 id="reservations-title">Mis <em>solicitudes</em></h2></div>
                <span class="reservation-count"><?= count($reservations) ?> <?= count($reservations) === 1 ? 'solicitud' : 'solicitudes' ?></span>
            </div>
            <?php if (is_array($reservationNotice)): ?>
                <div class="reservation-success" role="status"><span aria-hidden="true">✓</span><p><strong>Solicitud recibida</strong><br>Referencia <?= escapeHtml($reservationNotice['reference']) ?> · Aurelia confirmará disponibilidad y tarifa.</p></div>
            <?php endif; ?>
            <?php if ($reservations === []): ?>
                <div class="empty-reservations"><span aria-hidden="true">✧</span><p>Tus próximas escapadas aparecerán aquí.</p><a href="#habitaciones">Descubrir habitaciones <span aria-hidden="true">→</span></a></div>
            <?php else: ?>
                <div class="reservation-list">
                    <?php foreach (array_reverse($reservations) as $reservation): ?>
                        <article class="reservation-card">
                            <img src="<?= escapeHtml($rooms[$reservation['room']]['image']) ?>" alt="" loading="lazy">
                            <div class="reservation-details">
                                <span class="reservation-status"><span></span> Pendiente de confirmación</span>
                                <h3><?= escapeHtml($reservation['room_name']) ?></h3>
                                <p><?= escapeHtml($reservation['guest_name']) ?> · <?= escapeHtml($reservation['check_in']) ?> <span aria-hidden="true">→</span> <?= escapeHtml($reservation['check_out']) ?> · <?= escapeHtml((string) $reservation['nights']) ?> <?= (int) $reservation['nights'] === 1 ? 'noche' : 'noches' ?> · <?= escapeHtml($reservation['guests']) ?> <?= (int) $reservation['guests'] === 1 ? 'huésped' : 'huéspedes' ?></p>
                                <small>Referencia <?= escapeHtml($reservation['reference']) ?></small>
                            </div>
                            <div class="reservation-total"><span>Total estimado</span><strong>S/ <?= hotelMoney((int) $reservation['total']) ?></strong></div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="site-footer">
        <div class="footer-main">
            <a class="brand footer-brand" href="#inicio"><span class="brand-mark" aria-hidden="true">A</span><span class="brand-copy"><strong>AURELIA</strong><small>HOTEL &amp; SUITES</small></span></a>
            <p>Una estancia extraordinaria en el corazón de Huancayo.</p>
            <a href="#inicio">Volver arriba ↑</a>
        </div>
        <div class="footer-bottom"><span>© <?= date('Y') ?> Aurelia Hotel &amp; Suites</span><span>Hospitalidad, con intención.</span></div>
    </footer>
</body>
</html>
