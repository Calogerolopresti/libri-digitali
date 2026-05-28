    <!-- Navbar fissa in cima alla pagina, visibile agli utenti loggati -->
    <nav class="navbar navbar-expand-lg fixed-top shadow-sm">
        <div class="container">
            <!-- logo con immagine favicon + nome del sito -->
            <a class="navbar-brand d-flex align-items-center" href="<?php echo BASE_URL; ?>/index-logged.php">
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
                <!-- azioni utente: carrello con badge, profilo, logout -->
                <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center gap-3 mt-3 mt-lg-0 pb-3 pb-lg-0">
                    <!-- link al carrello con contatore degli articoli al suo interno -->
                    <a href="<?php echo BASE_URL; ?>/carrello.php" class="btn btn-outline-primary nav-btn px-4 position-relative">
                        <i class="fa-solid fa-cart-shopping"></i> Carrello
                        <?php
                        // contiamo quanti prodotti distinti ci sono nel carrello dell utente
                        $cart_count = 0;
                        if (isset($_SESSION['user_id'])) {
                            try {
                                $stmt_cart = $pdo->prepare("SELECT COUNT(*) FROM Carrello WHERE id_utente = ?");
                                $stmt_cart->execute([$_SESSION['user_id']]);
                                $cart_count = (int)$stmt_cart->fetchColumn();
                            } catch (PDOException $e) {
                                $cart_count = 0;
                            }
                        }
                        // mostriamo il badge rosso solo se il carrello ha almeno un prodotto
                        if($cart_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                            <?php echo htmlspecialchars($cart_count)?>
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
