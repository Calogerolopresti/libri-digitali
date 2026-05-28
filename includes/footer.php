<!-- footer arricchito con tre colonne: brand, navigazione, copyright -->
    <footer class="py-5 mt-auto">
        <div class="container">
            <div class="row gy-4 align-items-start">

                <!-- colonna brand: logo + payoff del sito -->
                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-center mb-2">
                        <img src="<?php echo BASE_URL; ?>/assets/img/favicon.png" alt="Logo" class="navbar-logo">
                        <span class="fw-bold" style="font-size: 1.1rem;">E-Book &amp; Co.</span>
                    </div>
                    <p class="text-muted small mb-0" style="max-width: 240px; line-height: 1.7;">
                        La libreria digitale per i lettori moderni. Cartaceo e digitale, sempre con te.
                    </p>
                </div>

                <!-- colonna link rapidi: cambia dinamicamente in base allo stato di login -->
                <div class="col-6 col-md-4">
                    <p class="fw-semibold mb-3 small text-uppercase" style="letter-spacing: 0.5px;">Navigazione</p>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                        <li><a href="<?php echo BASE_URL; ?>/index.php" class="footer-link"><i class="fa-solid fa-house me-2 fa-xs"></i>Home</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/index.php#catalogo" class="footer-link"><i class="fa-solid fa-book me-2 fa-xs"></i>Catalogo</a></li>
                        <?php if(isset($_SESSION['user_id'])): ?>
                        <!-- se l utente è loggato mostriamo carrello e profilo -->
                        <li><a href="<?php echo BASE_URL; ?>/carrello.php" class="footer-link"><i class="fa-solid fa-cart-shopping me-2 fa-xs"></i>Carrello</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/profilo.php" class="footer-link"><i class="fa-regular fa-user me-2 fa-xs"></i>Profilo</a></li>
                        <?php else: ?>
                        <!-- altrimenti mostriamo i link di accesso e registrazione -->
                        <li><a href="<?php echo BASE_URL; ?>/auth/login.php" class="footer-link"><i class="fa-solid fa-arrow-right-to-bracket me-2 fa-xs"></i>Accedi</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/auth/register.php" class="footer-link"><i class="fa-solid fa-user-plus me-2 fa-xs"></i>Registrati</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- colonna copyright e disclaimer -->
                <div class="col-6 col-md-4 text-md-end">
                    <p class="text-muted small mb-0">&copy; 2026 E-Book &amp; Co.</p>
                    <p class="text-muted small">LibriDigitali s.r.l. — Tutti i diritti riservati.</p>
                    <p class="text-muted" style="font-size: 0.7rem;">Sito dimostrativo. Nessun acquisto reale.</p>
                </div>

            </div>
        </div>
    </footer>

    <!-- contenitore dei toast, popolato dinamicamente da showToast() in JS -->
    <div id="toast-container"></div>

    <!-- pulsante flottante torna su, appare dopo 300px di scroll -->
    <a href="#" class="back-to-top" id="backToTop" title="Torna su">
        <i class="fa-solid fa-arrow-up"></i>
    </a>

    <!-- widget chatbot Leo, incluso dal file dedicato -->
    <?php
        // calcoliamo la profondità della directory per trovare il percorso corretto del widget
        $depth = substr_count($_SERVER['PHP_SELF'], '/') - 1;
        $base  = str_repeat('../', max(0, $depth - 1));
        include __DIR__ . '/chatbot_widget.php';
    ?>

    <!-- Bootstrap 5 JS: necessario per modal, tooltip e componenti interattivi -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {

        // gestiamo la visibilità del pulsante "torna su" in base allo scroll
        const backToTopButton = document.getElementById("backToTop");
        if (backToTopButton) {
            window.addEventListener("scroll", function() {
                if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                    backToTopButton.classList.add("show");
                } else {
                    backToTopButton.classList.remove("show");
                }
            });
            // click sul pulsante: torna in cima con animazione smooth
            backToTopButton.addEventListener("click", function(e) {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        // inizializziamo tutti i tooltip Bootstrap presenti nella pagina
        const tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipEls.forEach(el => new bootstrap.Tooltip(el, { trigger: 'hover' }));

        // funzione globale per mostrare un toast temporaneo nella parte alta destra dello schermo
        // type può essere 'error', 'success' o 'info'
        window.showToast = function(msg, type) {
            type = type || 'info';
            const icons = {
                error:   'fa-circle-exclamation text-danger',
                success: 'fa-circle-check text-success',
                info:    'fa-circle-info text-primary'
            };
            const container = document.getElementById('toast-container');
            if (!container) return;

            // creiamo il div del toast con icona, messaggio e pulsante chiudi
            const t = document.createElement('div');
            t.className = 'toast-msg toast-' + type;
            t.innerHTML =
                '<i class="fa-solid ' + icons[type] + '"></i>' +
                '<span>' + msg + '</span>' +
                '<button class="toast-close" aria-label="Chiudi"><i class="fa-solid fa-xmark"></i></button>';

            // click sul pulsante chiudi: rimuove subito il toast
            t.querySelector('.toast-close').addEventListener('click', () => t.remove());
            container.appendChild(t);

            // dopo 5 secondi facciamo sparire il toast con un fade out
            setTimeout(() => {
                t.style.opacity = '0';
                t.style.transition = 'opacity 0.4s';
                setTimeout(() => t.remove(), 400);
            }, 5000);
        };

        // leggiamo i parametri GET nell URL per mostrare errori e successi come toast
        // in questo modo l URL rimane pulito e il feedback è più user-friendly
        const params = new URLSearchParams(window.location.search);
        if (params.has('errore')) {
            const msg = params.get('errore');
            // mappa dei codici di errore verso messaggi leggibili dall utente
            const friendlyErrors = {
                'limite_raggiunto':          'Hai raggiunto la quantità massima disponibile.',
                'minimo_raggiunto':          'La quantità minima è 1. Usa il tasto elimina per rimuovere.',
                'errore_riprovare_piu_tardi': 'Si è verificato un errore. Riprova tra poco.'
            };
            showToast(friendlyErrors[msg] || msg, 'error');
        }
        if (params.has('successo')) {
            showToast('Operazione completata con successo!', 'success');
        }

    });
    </script>
</body>
</html>

