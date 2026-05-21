    <!-- Navbar Sticky -->
    <nav class="navbar navbar-expand-lg fixed-top shadow-sm bg-white border-bottom py-2">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>/admin/index.php">
                <i class="fa-solid fa-book-open me-2 text-primary"></i>
                E-Book & Co. <span class="badge bg-primary ms-2 fs-6">Admin</span>
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
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
                <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0">
                    <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-outline-secondary nav-btn px-4 border-0">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Esci dal Pannello
                    </a>
                </div>
            </div>
        </div>
    </nav>
