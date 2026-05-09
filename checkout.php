<?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/head.php'; ?>
<body class="d-flex flex-column min-vh-100 fade-in pt-5 mt-4 bg-light">

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/navbar_user.php'; ?>

<!-- Main Content -->
    <main class="container mb-5 flex-grow-1 fade-in fade-in-delay-1 mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h2 class="fw-bold text-secondary-color mb-4">Checkout</h2>
                <div class="alert alert-info">
                    <i class="fa-solid fa-info-circle me-2"></i> Integrazione pagamento da implementare (es. Stripe o PayPal).
                </div>
                <a href="index.php" class="btn btn-primary mt-3">Torna alla Home</a>
            </div>
        </div>
    </main>

    

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/footer.php'; ?>
