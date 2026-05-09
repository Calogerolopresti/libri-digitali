<?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/head.php'; ?>
<body class="d-flex flex-column min-vh-100 fade-in ">

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/navbar_public.php'; ?>

<!-- Hero Section -->
    <section class="hero-section fade-in fade-in-delay-1">
        <div class="container hero-content">
            <h1 class="hero-title">Esplora Mondi, <br><span>Trova Te Stesso.</span></h1>
            <p class="hero-subtitle">Benvenuto in E-Book & Co., la destinazione premium per i lettori di tutto il mondo. Libri fisici di pregio ed edizioni digitali a portata di click.</p>
            <a href="#catalogo" class="btn btn-primary btn-lg mt-2">
                <i class="fa-solid fa-compass"></i> Esplora il Catalogo
            </a>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container mb-5 flex-grow-1 fade-in fade-in-delay-2" id="catalogo">
        <div class="mb-4 pb-2 border-bottom">
            <h2 class="fw-bold mb-0 text-secondary-color">Catalogo</h2>
        </div>
        
        <div class="row g-4 mb-5">
            <!-- Esempio Card 1 (Libro Fisico) -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card book-card">
                    <div class="card-img-wrapper">
                        <img src="https://via.placeholder.com/300x450/f8f9fa/d4af37?text=Il+Nome+della+Rosa" class="book-cover" alt="Copertina">
                    </div>
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-success mb-3 align-self-start"><i class="fa-solid fa-book-open me-1"></i> Edizione Cartacea</span>
                        <h5 class="card-title">Il Nome della Rosa</h5>
                        <p class="text-muted small mb-3">Umberto Eco</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="price">€ 14.50</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Esempio Card 2 (eBook) -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card book-card">
                    <div class="card-img-wrapper">
                        <img src="https://via.placeholder.com/300x450/f8f9fa/d4af37?text=1984" class="book-cover" alt="Copertina">
                    </div>
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-info mb-3 align-self-start"><i class="fa-solid fa-download me-1"></i> Edizione Digitale</span>
                        <h5 class="card-title">1984</h5>
                        <p class="text-muted small mb-3">George Orwell</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="price">€ 8.99</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Esempio Card 3 (Libro Fisico) -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card book-card">
                    <div class="card-img-wrapper">
                        <img src="https://via.placeholder.com/300x450/f8f9fa/d4af37?text=Il+Signore+Degli+Anelli" class="book-cover" alt="Copertina">
                    </div>
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-success mb-3 align-self-start"><i class="fa-solid fa-book-open me-1"></i> Edizione Cartacea</span>
                        <h5 class="card-title">Il Signore degli Anelli</h5>
                        <p class="text-muted small mb-3">J.R.R. Tolkien</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="price">€ 25.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Esempio Card 4 (eBook) -->
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card book-card">
                    <div class="card-img-wrapper">
                        <img src="https://via.placeholder.com/300x450/f8f9fa/d4af37?text=Fondazione" class="book-cover" alt="Copertina">
                    </div>
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-info mb-3 align-self-start"><i class="fa-solid fa-download me-1"></i> Edizione Digitale</span>
                        <h5 class="card-title">Fondazione</h5>
                        <p class="text-muted small mb-3">Isaac Asimov</p>
                        <div class="mt-auto d-flex justify-content-between align-items-center">
                            <span class="price">€ 7.50</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/footer.php'; ?>
