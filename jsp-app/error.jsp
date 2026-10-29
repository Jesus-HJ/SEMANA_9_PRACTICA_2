<%@ page contentType="text/html; charset=UTF-8" pageEncoding="UTF-8" isErrorPage="true" %>
<%
    response.setHeader("Cache-Control", "no-store");
    response.setHeader("X-Content-Type-Options", "nosniff");
    response.setHeader("X-Frame-Options", "DENY");
    response.setHeader("Referrer-Policy", "strict-origin-when-cross-origin");
    response.setHeader("Content-Security-Policy", "default-src 'self'; style-src 'self'; img-src 'self'; base-uri 'self'; frame-ancestors 'none'");
    Object statusAttribute = request.getAttribute("javax.servlet.error.status_code");
    int status = statusAttribute instanceof Integer ? (Integer) statusAttribute : 500;
    response.setStatus(status);
    if (exception != null) {
        application.log("Error interno al procesar una solicitud de Aurelia.", exception);
    }
%>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#f6f4ee"><title>Estamos preparando todo | Aurelia Hotel &amp; Suites</title><link rel="stylesheet" href="assets/styles.css"></head>
<body>
    <header class="site-header"><a class="brand" href="index.jsp" aria-label="Aurelia Hotel & Suites, inicio"><span class="brand-mark" aria-hidden="true">A</span><span class="brand-copy"><strong>AURELIA</strong><small>HOTEL &amp; SUITES</small></span></a></header>
    <main class="error-page"><p class="eyebrow">UN MOMENTO, POR FAVOR</p><h1>Estamos preparando <em>todo para ti.</em></h1><p>No pudimos completar la solicitud. Inténtalo de nuevo en unos momentos.</p><a class="button button-dark" href="index.jsp">Volver a Aurelia <span aria-hidden="true">↗</span></a></main>
    <footer class="site-footer"><div class="footer-main"><a class="brand footer-brand" href="index.jsp"><span class="brand-mark" aria-hidden="true">A</span><span class="brand-copy"><strong>AURELIA</strong><small>HOTEL &amp; SUITES</small></span></a><p>Una estancia extraordinaria en el corazón de Huancayo.</p></div></footer>
</body>
</html>
