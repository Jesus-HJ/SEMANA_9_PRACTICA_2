<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" import="java.time.LocalDate,java.time.format.DateTimeFormatter,java.util.ArrayList,java.util.HashMap,java.util.List,java.util.Map" %>
<%@ include file="/WEB-INF/hotel-common.jspf" %>
<%
    response.setHeader("X-Content-Type-Options", "nosniff");
    response.setHeader("Cache-Control", "no-store");
    response.setHeader("X-Frame-Options", "DENY");
    response.setHeader("Referrer-Policy", "strict-origin-when-cross-origin");
    response.setHeader("Content-Security-Policy", "default-src 'self'; style-src 'self'; script-src 'self'; img-src 'self'; form-action 'self'; base-uri 'self'; frame-ancestors 'none'");

    String csrfToken = (String) session.getAttribute("csrfToken");
    if (csrfToken == null) {
        csrfToken = createHotelToken();
        session.setAttribute("csrfToken", csrfToken);
    }

    Map<String, HotelRoom> rooms = hotelRooms();
    Map<String, String> formValues = (Map<String, String>) request.getAttribute("formValues");
    if (formValues == null) {
        formValues = new HashMap<String, String>();
    }
    List<String> formErrors = (List<String>) request.getAttribute("formErrors");
    if (formErrors == null) {
        formErrors = new ArrayList<String>();
    }

    List<Map<String, String>> reservationView;
    synchronized (session) {
        List<Map<String, String>> reservations = (List<Map<String, String>>) session.getAttribute("reservas");
        reservationView = reservations == null
            ? new ArrayList<Map<String, String>>()
            : new ArrayList<Map<String, String>>(reservations);
    }

    Map<String, String> reservationNotice = (Map<String, String>) session.getAttribute("reservaRecibida");
    if (reservationNotice != null) {
        session.removeAttribute("reservaRecibida");
    }

    String selectedRoom = rooms.containsKey(formValues.get("room")) ? formValues.get("room") : "deluxe";
    int selectedGuests = 2;
    try {
        selectedGuests = Integer.parseInt(formValues.get("guests"));
    } catch (NumberFormatException exception) {
        selectedGuests = 2;
    }
    LocalDate today = LocalDate.now(HOTEL_ZONE);
    String todayValue = today.format(DateTimeFormatter.ISO_LOCAL_DATE);
    String tomorrowValue = today.plusDays(1).format(DateTimeFormatter.ISO_LOCAL_DATE);
%>
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
        <a class="brand" href="index.jsp" aria-label="Aurelia Hotel & Suites, inicio">
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
                <div><p class="hero-kicker">UNA ESTANCIA EXTRAORDINARIA</p><h1 id="page-title">AURELIA</h1><p class="hero-subtitle">HOTEL &amp; SUITES</p></div>
                <div class="hero-panel-aside"><p>Tu próxima experiencia<br>comienza aquí.</p><a class="button button-light" href="#habitaciones">Descubrir habitaciones <span aria-hidden="true">↓</span></a></div>
            </div>
        </section>

        <section class="intro-section page-width" aria-labelledby="intro-title">
            <div><p class="eyebrow">EL ARTE DE RECIBIR</p><h2 id="intro-title">Donde cada detalle<br>te hace sentir <em>en casa.</em></h2></div>
            <p class="intro-copy">Descubre una forma distinta de hospedarte. Habitaciones serenas, una atención que se anticipa a ti y la belleza de Huancayo como telón de fondo.</p>
        </section>

        <section class="rooms-section page-width" id="habitaciones" aria-labelledby="rooms-title">
            <div class="section-heading">
                <div><p class="eyebrow">DESCANSO A TU MEDIDA</p><h2 id="rooms-title">Habitaciones <em>&amp; suites</em></h2></div>
                <p class="section-caption">Espacios diseñados para que cada noche sea parte del viaje. Tarifas referenciales en soles peruanos; imágenes y reservas ilustrativas.</p>
            </div>
            <div class="room-grid">
                <% for (HotelRoom room : rooms.values()) { %>
                    <article class="room-card">
                        <a class="room-image-link" href="#reservar" data-room-choice="<%= escapeHtml(room.id) %>" aria-label="Elegir <%= escapeHtml(room.name) %>">
                            <img class="room-image" src="<%= escapeHtml(room.image) %>" alt="<%= escapeHtml(room.name + ": " + room.description) %>" loading="lazy">
                            <span class="room-image-arrow" aria-hidden="true">↗</span>
                        </a>
                        <div class="room-card-body">
                            <div class="room-title-row"><h3><%= escapeHtml(room.name) %></h3><span><%= room.capacity %> huéspedes</span></div>
                            <p><%= escapeHtml(room.description) %></p>
                            <ul class="room-features" aria-label="Características">
                                <% for (String feature : room.features) { %><li><%= escapeHtml(feature) %></li><% } %>
                            </ul>
                            <div class="room-card-footer">
                                <p class="room-rate"><strong>S/ <%= formatHotelPrice(room.rate) %></strong><span> / noche</span></p>
                                <a class="room-link" href="#reservar" data-room-choice="<%= escapeHtml(room.id) %>">Elegir habitación <span aria-hidden="true">→</span></a>
                            </div>
                        </div>
                    </article>
                <% } %>
            </div>
        </section>

        <section class="experience-section" id="experiencia" aria-labelledby="experience-title">
            <img src="assets/images/resort-sunset.png" alt="Una suite de Aurelia y una vista de atardecer sobre las montañas" loading="lazy">
            <div class="experience-overlay"></div>
            <div class="experience-copy page-width"><p class="eyebrow">EL PRIVILEGIO DE DETENERSE</p><h2 id="experience-title">El tiempo<br>también <em>descansa.</em></h2><p>Despierta con vistas que invitan a quedarse un poco más.</p><a class="button button-light" href="#reservar">Planificar mi estancia <span aria-hidden="true">↗</span></a></div>
            <span class="experience-caption">UN ATARDECER AURELIA</span>
        </section>

        <section class="amenities page-width" aria-label="Servicios incluidos">
            <article><span aria-hidden="true">✳</span><div><h3>Descanso auténtico</h3><p>Textiles suaves y espacios pensados para desconectar.</p></div></article>
            <article><span aria-hidden="true">⌁</span><div><h3>Atención personal</h3><p>Un equipo atento a cada detalle de tu estancia.</p></div></article>
            <article><span aria-hidden="true">◈</span><div><h3>Huancayo a tu ritmo</h3><p>Una ubicación ideal para explorar y volver a relajarte.</p></div></article>
        </section>

        <section class="booking-section" id="reservar" aria-labelledby="booking-title">
            <div class="booking-layout page-width">
                <div class="booking-copy"><p class="eyebrow">TU PRÓXIMA ESCAPADA</p><h2 id="booking-title">Haz espacio<br>para <em>algo especial.</em></h2><p>Cuéntanos cómo imaginas tu estancia. Prepararemos tu solicitud para que nuestro equipo pueda confirmar los detalles contigo.</p><div class="booking-assurance"><span aria-hidden="true">✧</span> Sin compromiso. Los datos se utilizan únicamente para esta solicitud.</div></div>
                <div class="booking-card">
                    <div class="booking-card-heading"><div><p class="eyebrow">PLANIFICA TU ESTANCIA</p><h3>Solicita tu reserva</h3></div><span class="booking-monogram" aria-hidden="true">A</span></div>
                    <% if (!formErrors.isEmpty()) { %>
                        <div class="form-alert" role="alert"><strong>No pudimos preparar la solicitud.</strong><ul><% for (String error : formErrors) { %><li><%= escapeHtml(error) %></li><% } %></ul></div>
                    <% } %>
                    <form action="reservar.jsp" method="post">
                        <input type="hidden" name="csrfToken" value="<%= escapeHtml(csrfToken) %>">
                        <div class="form-grid">
                            <div class="form-field field-full"><label for="guest_name">Nombre completo</label><input class="form-control" id="guest_name" name="guest_name" type="text" autocomplete="name" minlength="2" maxlength="100" value="<%= escapeHtml(formValues.get("guest_name")) %>" placeholder="¿A nombre de quién reservamos?" required></div>
                            <div class="form-field field-full"><label for="guest_email">Correo electrónico</label><input class="form-control" id="guest_email" name="guest_email" type="email" autocomplete="email" maxlength="254" value="<%= escapeHtml(formValues.get("guest_email")) %>" placeholder="nombre@ejemplo.com" required></div>
                            <div class="form-field field-full">
                                <label for="room">Habitación</label>
                                <select class="form-control" id="room" name="room" required>
                                    <% for (HotelRoom room : rooms.values()) { %><option value="<%= escapeHtml(room.id) %>" data-rate="<%= room.rate %>" data-capacity="<%= room.capacity %>" <%= selectedRoom.equals(room.id) ? "selected" : "" %>><%= escapeHtml(room.name) %> · S/ <%= formatHotelPrice(room.rate) %> / noche</option><% } %>
                                </select>
                            </div>
                            <div class="form-field"><label for="check_in">Llegada</label><input class="form-control" id="check_in" name="check_in" type="date" min="<%= todayValue %>" value="<%= escapeHtml(formValues.containsKey("check_in") ? formValues.get("check_in") : todayValue) %>" required></div>
                            <div class="form-field"><label for="check_out">Salida</label><input class="form-control" id="check_out" name="check_out" type="date" min="<%= tomorrowValue %>" value="<%= escapeHtml(formValues.containsKey("check_out") ? formValues.get("check_out") : tomorrowValue) %>" required></div>
                            <div class="form-field field-full"><label for="guests">Número de huéspedes</label><select class="form-control" id="guests" name="guests" required><% for (int count = 1; count <= 4; count++) { %><option value="<%= count %>" <%= selectedGuests == count ? "selected" : "" %>><%= count %> <%= count == 1 ? "huésped" : "huéspedes" %></option><% } %></select></div>
                            <div class="form-field field-full"><label for="notes">¿Alguna preferencia especial? <span>(opcional)</span></label><textarea class="form-control form-message" id="notes" name="notes" rows="3" maxlength="600" placeholder="Cuéntanos si tienes alguna solicitud para tu estancia."><%= escapeHtml(formValues.get("notes")) %></textarea></div>
                        </div>
                        <div class="stay-summary" aria-live="polite"><div><span id="night-count">1 noche</span><small> Tarifa referencial · sujeta a confirmación</small></div><strong id="stay-total">S/ 280</strong></div>
                        <button class="button button-dark booking-submit" type="submit">Solicitar reserva <span aria-hidden="true">↗</span></button>
                        <p class="booking-disclaimer">La solicitud se guarda de forma temporal en este navegador. La reserva no queda confirmada hasta que Aurelia valide disponibilidad y precio.</p>
                    </form>
                </div>
            </div>
        </section>

        <section class="reservations-section page-width" id="mis-reservas" aria-labelledby="reservations-title">
            <div class="section-heading"><div><p class="eyebrow">TU VIAJE, A TU ALCANCE</p><h2 id="reservations-title">Mis <em>solicitudes</em></h2></div><span class="reservation-count"><%= reservationView.size() %> <%= reservationView.size() == 1 ? "solicitud" : "solicitudes" %></span></div>
            <% if (reservationNotice != null) { %><div class="reservation-success" role="status"><span aria-hidden="true">✓</span><p><strong>Solicitud recibida</strong><br>Referencia <%= escapeHtml(reservationNotice.get("reference")) %> · Aurelia confirmará disponibilidad y tarifa.</p></div><% } %>
            <% if (reservationView.isEmpty()) { %>
                <div class="empty-reservations"><span aria-hidden="true">✧</span><p>Tus próximas escapadas aparecerán aquí.</p><a href="#habitaciones">Descubrir habitaciones <span aria-hidden="true">→</span></a></div>
            <% } else { %>
                <div class="reservation-list">
                    <% for (int i = reservationView.size() - 1; i >= 0; i--) {
                        Map<String, String> reservation = reservationView.get(i);
                        HotelRoom room = rooms.get(reservation.get("room"));
                        int reservationGuests = Integer.parseInt(reservation.get("guests")); %>
                        <article class="reservation-card">
                            <img src="<%= escapeHtml(room.image) %>" alt="" loading="lazy">
                            <div class="reservation-details"><span class="reservation-status"><span></span> Pendiente de confirmación</span><h3><%= escapeHtml(reservation.get("room_name")) %></h3><p><%= escapeHtml(reservation.get("guest_name")) %> · <%= escapeHtml(reservation.get("check_in")) %> <span aria-hidden="true">→</span> <%= escapeHtml(reservation.get("check_out")) %> · <%= escapeHtml(reservation.get("nights")) %> <%= "1".equals(reservation.get("nights")) ? "noche" : "noches" %> · <%= reservationGuests %> <%= reservationGuests == 1 ? "huésped" : "huéspedes" %></p><small>Referencia <%= escapeHtml(reservation.get("reference")) %></small></div>
                            <div class="reservation-total"><span>Total estimado</span><strong>S/ <%= formatHotelPrice(Integer.parseInt(reservation.get("total"))) %></strong></div>
                        </article>
                    <% } %>
                </div>
            <% } %>
        </section>
    </main>

    <footer class="site-footer">
        <div class="footer-main"><a class="brand footer-brand" href="#inicio"><span class="brand-mark" aria-hidden="true">A</span><span class="brand-copy"><strong>AURELIA</strong><small>HOTEL &amp; SUITES</small></span></a><p>Una estancia extraordinaria en el corazón de Huancayo.</p><a href="#inicio">Volver arriba ↑</a></div>
        <div class="footer-bottom"><span>© <%= LocalDate.now(HOTEL_ZONE).getYear() %> Aurelia Hotel &amp; Suites</span><span>Hospitalidad, con intención.</span></div>
    </footer>
</body>
</html>
