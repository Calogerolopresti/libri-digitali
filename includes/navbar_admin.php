    <!-- Navbar fissa per il pannello admin -->
    <nav class="navbar navbar-expand-lg fixed-top shadow-sm bg-white border-bottom py-2">
        <div class="container-fluid px-4">
            <!-- logo + badge admin per distinguerla dalla navbar utente -->
            <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>/admin/index.php">
                <img src="<?php echo BASE_URL; ?>/assets/img/favicon.png" alt="Logo" class="navbar-logo">
                E-Book &amp; Co. <span class="badge bg-primary ms-2 fs-6">Admin</span>
            </a>
            <!-- toggler per il menu mobile -->
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- link di navigazione tra le sezioni del pannello admin -->
                <ul class="navbar-nav me-auto ps-lg-4">
                    <li class="nav-item">
                        <a class="nav-link fw-medium text-secondary" href="<?php echo BASE_URL; ?>/admin/index.php">
                            <i class="fa-solid fa-book me-1 text-primary"></i> Catalogo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium text-secondary ms-lg-3" href="<?php echo BASE_URL; ?>/admin/clienti.php">
                            <i class="fa-solid fa-users me-1 text-primary"></i> Clienti
                        </a>
                    </li>
                </ul>
                <!-- pulsante per uscire dal pannello e tornare al sito pubblico -->
                <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-3 mt-3 mt-lg-0 pb-3 pb-lg-0">
                    <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-outline-secondary nav-btn px-4 border-0">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Esci dal Pannello
                    </a>
                </div>
            </div>
        </div>
    </nav>
