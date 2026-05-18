    <!-- Navbar Sticky -->
    <nav class="navbar navbar-expand-lg fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>/index-logged.php">
                <i class="fa-solid fa-book-open me-2 text-primary"></i>
                E-Book & Co. 
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <!-- Eventuali link generici qui -->
                </ul>
                <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0">
                    <a href="<?php echo BASE_URL; ?>/carrello.php" class="btn btn-outline-primary nav-btn px-4 position-relative">
                        <i class="fa-solid fa-cart-shopping"></i> Carrello
                        <?php if(isset($_SESSION['carrello']) && count($_SESSION['carrello'])>0):?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                            <?php echo htmlspecialchars(count($_SESSION['carrello']))?>
                        </span>
                        <?php endif?>
                    </a>
                    <a href="<?php echo BASE_URL; ?>/profilo.php" class="btn btn-primary nav-btn px-4 shadow-sm">
                        <i class="fa-regular fa-user"></i> Storico Ordini
                    </a>
                    <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn btn-outline-secondary nav-btn px-3 border-0">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
