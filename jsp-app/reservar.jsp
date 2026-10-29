<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" import="java.nio.charset.StandardCharsets,java.security.MessageDigest,java.time.LocalDate,java.time.format.DateTimeFormatter,java.time.temporal.ChronoUnit,java.util.ArrayList,java.util.HashMap,java.util.List,java.util.Map" %>
<%@ include file="/WEB-INF/hotel-common.jspf" %>
<%
    request.setCharacterEncoding("UTF-8");
    response.setHeader("X-Content-Type-Options", "nosniff");
    response.setHeader("Cache-Control", "no-store");
    response.setHeader("X-Frame-Options", "DENY");
    response.setHeader("Referrer-Policy", "strict-origin-when-cross-origin");
    response.setHeader("Content-Security-Policy", "default-src 'self'; style-src 'self'; script-src 'self'; img-src 'self'; form-action 'self'; base-uri 'self'; frame-ancestors 'none'");

    List<String> formErrors = new ArrayList<String>();
    Map<String, String> formValues = new HashMap<String, String>();
    for (String field : new String[] {"guest_name", "guest_email", "room", "check_in", "check_out", "guests", "notes"}) {
        String value = request.getParameter(field);
        formValues.put(field, value == null ? "" : value.trim());
    }

    if (!"POST".equals(request.getMethod())) {
        response.setHeader("Allow", "POST");
        response.setStatus(405);
        application.log("Método HTTP no permitido en reservar.jsp: " + request.getMethod());
        formErrors.add("Este recurso solo acepta solicitudes de reserva enviadas mediante POST.");
    } else {
        String submittedToken = request.getParameter("csrfToken");
        String sessionToken = (String) session.getAttribute("csrfToken");
        if (submittedToken == null || sessionToken == null
                || !MessageDigest.isEqual(sessionToken.getBytes(StandardCharsets.UTF_8), submittedToken.getBytes(StandardCharsets.UTF_8))) {
            response.setStatus(403);
            formErrors.add("La solicitud venció o el código de seguridad no es válido. Vuelve a cargar la página e inténtalo otra vez.");
        } else {
            Map<String, HotelRoom> rooms = hotelRooms();
            String guestName = formValues.get("guest_name");
            String guestEmail = formValues.get("guest_email");
            String roomId = formValues.get("room");
            String notes = formValues.get("notes");
            HotelRoom room = rooms.get(roomId);
            LocalDate today = LocalDate.now(HOTEL_ZONE);
            LocalDate checkIn = parseHotelDate(formValues.get("check_in"));
            LocalDate checkOut = parseHotelDate(formValues.get("check_out"));
            int guests = 0;
            boolean guestsValid = false;
            try {
                guests = Integer.parseInt(formValues.get("guests"));
                guestsValid = guests >= 1 && guests <= 4;
            } catch (NumberFormatException exception) {
                guestsValid = false;
            }

            if (!hasHotelLength(guestName, 2, 100) || HOTEL_NAME_CONTROL.matcher(guestName).find()) {
                formErrors.add("Escribe un nombre de entre 2 y 100 caracteres.");
            }
            if (guestEmail.length() > 254 || !HOTEL_EMAIL.matcher(guestEmail).matches()) {
                formErrors.add("Ingresa un correo electrónico válido.");
            }
            if (room == null) {
                formErrors.add("Selecciona una habitación disponible.");
            }
            if (checkIn == null || checkIn.isBefore(today)) {
                formErrors.add("Selecciona una fecha de llegada válida, a partir de hoy.");
            }
            if (checkOut == null || (checkIn != null && !checkOut.isAfter(checkIn))) {
                formErrors.add("La salida debe ser posterior a la fecha de llegada.");
            } else if (checkIn != null && ChronoUnit.DAYS.between(checkIn, checkOut) > 30) {
                formErrors.add("La estancia no puede superar 30 noches.");
            }
            if (!guestsValid) {
                formErrors.add("Selecciona entre 1 y 4 huéspedes.");
            } else if (room != null && guests > room.capacity) {
                formErrors.add("La habitación seleccionada admite hasta " + room.capacity + " huéspedes.");
            }
            if (!hasHotelLength(notes, 0, 600) || HOTEL_NOTES_CONTROL.matcher(notes).find()) {
                formErrors.add("La preferencia debe tener como máximo 600 caracteres.");
            }

            if (formErrors.isEmpty()) {
                long nights = ChronoUnit.DAYS.between(checkIn, checkOut);
                Map<String, String> reservation = new HashMap<String, String>();
                reservation.put("reference", hotelReference());
                reservation.put("guest_name", guestName);
                reservation.put("guest_email", guestEmail);
                reservation.put("room", roomId);
                reservation.put("room_name", room.name);
                reservation.put("rate", String.valueOf(room.rate));
                reservation.put("check_in", checkIn.format(DateTimeFormatter.ofPattern("dd/MM/yyyy")));
                reservation.put("check_out", checkOut.format(DateTimeFormatter.ofPattern("dd/MM/yyyy")));
                reservation.put("guests", String.valueOf(guests));
                reservation.put("nights", String.valueOf(nights));
                reservation.put("total", String.valueOf(room.rate * nights));
                reservation.put("notes", notes);
                reservation.put("created_at", java.time.LocalDateTime.now(HOTEL_ZONE).format(DateTimeFormatter.ofPattern("dd/MM/yyyy HH:mm")));

                synchronized (session) {
                    List<Map<String, String>> reservations = (List<Map<String, String>>) session.getAttribute("reservas");
                    if (reservations == null) {
                        reservations = new ArrayList<Map<String, String>>();
                    }
                    reservations.add(reservation);
                    while (reservations.size() > 50) {
                        reservations.remove(0);
                    }
                    session.setAttribute("reservas", reservations);
                    Map<String, String> notice = new HashMap<String, String>();
                    notice.put("reference", reservation.get("reference"));
                    session.setAttribute("reservaRecibida", notice);
                    session.setAttribute("csrfToken", createHotelToken());
                }

                response.setStatus(303);
                response.setHeader("Location", response.encodeRedirectURL("index.jsp#mis-reservas"));
                return;
            }
            response.setStatus(422);
        }
    }

    request.setAttribute("formErrors", formErrors);
    request.setAttribute("formValues", formValues);
    request.getRequestDispatcher("/index.jsp").include(request, response);
%>
