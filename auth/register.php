<?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/head.php'; ?>
<body class="d-flex flex-column min-vh-100 fade-in ">

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/navbar_public.php'; ?>

<!-- Register Form -->
    <main class="container flex-grow-1 auth-wrapper fade-in fade-in-delay-1" style="margin-top: 80px;">
        <div class="row w-100 justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card auth-card my-5">
                    <div class="text-center mb-5">
                        <h2 class="fw-bold text-secondary-color">Crea Account</h2>
                        <p class="text-muted">Inizia il tuo viaggio letterario con noi</p>
                    </div>
                    
                    <form method="POST" action="#">
                        <div class="mb-4">
                            <label for="nome" class="form-label">Nome Completo</label>
                            <div class="input-group-custom">
                                <i class="fa-regular fa-user input-icon"></i>
                                <input type="text" class="form-control" id="nome" name="nome" placeholder="Mario Rossi" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group-custom">
                                <i class="fa-regular fa-envelope input-icon"></i>
                                <input type="email" class="form-control" id="email" name="email" placeholder="mario.rossi@example.com" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group-custom">
                                <i class="fa-solid fa-lock input-icon"></i>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Crea una password sicura" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 text-uppercase">
                            Registrati <i class="fa-solid fa-check ms-2"></i>
                        </button>
                    </form>
                    
                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="mb-0 text-muted">Hai già un account? <a href="../auth/login.php" class="text-primary text-decoration-none fw-semibold">Accedi qui</a></p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/footer.php'; ?>
