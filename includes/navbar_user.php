    <!-- Navbar Sticky -->
    <nav class="navbar navbar-expand-lg fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="/libri-digitali/index.php">
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
                    <a href="/libri-digitali/carrello.php" class="btn btn-outline-primary nav-btn px-4 position-relative">
                        <i class="fa-solid fa-cart-shopping"></i> Carrello
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                            2
                        </span>
                    </a>
                    <a href="/libri-digitali/profilo.php" class="btn btn-primary nav-btn px-4 shadow-sm">
                        <i class="fa-regular fa-user"></i> Storico Ordini
                    </a>
                    <a href="/libri-digitali/auth/logout.php" class="btn btn-outline-secondary nav-btn px-3 border-0">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
