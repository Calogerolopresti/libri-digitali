    <!-- Navbar fissa in cima alla pagina, visibile agli utenti non loggati -->
    <nav class="navbar navbar-expand-lg fixed-top shadow-sm">
        <div class="container">
            <!-- logo con immagine favicon + nome del sito -->
            <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>/index.php">
                <img src="<?php echo BASE_URL; ?>/assets/img/favicon.png" alt="Logo" class="navbar-logo">
                E-Book &amp; Co.
            </a>
            <!-- toggler per il menu mobile -->
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <!-- eventuali link generici qui -->
                </ul>
                <!-- pulsanti di accesso / registrazione allineati a destra -->
                <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-3 mt-3 mt-lg-0 pb-3 pb-lg-0">
                    <a href="<?php echo BASE_URL; ?>/auth/login.php" class="btn btn-outline-primary nav-btn px-4">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i> Accedi
                    </a>
                    <a href="<?php echo BASE_URL; ?>/auth/register.php" class="btn btn-primary nav-btn px-4">
                        <i class="fa-solid fa-user-plus"></i> Registrati
                    </a>
                </div>
            </div>
        </div>
    </nav>
