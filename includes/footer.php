    <!-- Footer -->
    <footer class="py-4 mt-auto border-top">
        <div class="container text-center">
            <p class="mb-0 text-muted">&copy; 2026 E-Book & Co. | LibriDigitali s.r.l. Tutti i diritti riservati.</p>
        </div>
    </footer>

    <!-- Back to top button -->
    <a href="#" class="back-to-top" id="backToTop" title="Torna su">
        <i class="fa-solid fa-arrow-up"></i>
    </a>

    <!-- Chatbot Widget Leo -->
    <?php
        // Calcoliamo la profondità della directory per trovare il percorso corretto del widget
        $depth = substr_count($_SERVER['PHP_SELF'], '/') - 1;
        $base  = str_repeat('../', max(0, $depth - 1));
        include __DIR__ . '/chatbot_widget.php';
    ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const backToTopButton = document.getElementById("backToTop");
            if(backToTopButton) {
                window.addEventListener("scroll", function() {
                    if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                        backToTopButton.classList.add("show");
                    } else {
                        backToTopButton.classList.remove("show");
                    }
                });
                backToTopButton.addEventListener("click", function(e) {
                    e.preventDefault();
                    window.scrollTo({top: 0, behavior: 'smooth'});
                });
            }
        });
    </script>
</body>
</html>
