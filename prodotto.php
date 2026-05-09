<?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/head.php'; ?>
<body class="d-flex flex-column min-vh-100 fade-in pt-5 mt-4 bg-light">

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/navbar_public.php'; ?>

<!-- Product Detail -->
    <main class="container mb-5 flex-grow-1 fade-in fade-in-delay-1 mt-5">
        <div class="row g-5 align-items-start">
            <!-- Left Column: Image -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center d-flex align-items-center justify-content-center" style="background-color: #f1f5f9; min-height: 500px;">
                    <img src="https://via.placeholder.com/600x900/ffffff/a52a2a?text=Il+Nome+della+Rosa" class="img-fluid rounded shadow" alt="Copertina Libro" style="object-fit: cover; width: 100%; max-width: 320px; aspect-ratio: 2/3; display: block;">
                </div>
            </div>
            
            <!-- Right Column: Details -->
            <div class="col-md-7">
                <span class="badge bg-success mb-3 px-3 py-2"><i class="fa-solid fa-book-open me-1"></i> Edizione Cartacea</span>
                <h1 class="fw-bold text-secondary-color mb-2">Il Nome della Rosa</h1>
                <h4 class="text-muted fw-normal mb-4">di Umberto Eco</h4>
                
                <h2 class="price fs-1 mb-4">€ 14.50</h2>
                
                <div class="mb-4">
                    <p class="text-muted" style="line-height: 1.8;">
                        "Il nome della rosa" è un romanzo scritto da Umberto Eco ed edito per la prima volta da Bompiani nel 1980.
                        Ambientato sul finire dell'anno 1327, si presenta con un classico espediente letterario, quello del manoscritto ritrovato, 
                        attraverso il quale l'autore narra le vicende vissute all'interno di un'abbazia benedettina. Un'opera magistrale che unisce il thriller storico alla profonda riflessione filosofica.
                    </p>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-3 mb-4 p-3 bg-white rounded-3 shadow-sm border border-light">
                    <div class="d-flex align-items-center me-2">
                        <label for="quantita" class="fw-medium text-muted mb-0 me-3">Quantità:</label>
                        <div class="qty-selector d-flex align-items-center">
                            <button class="btn qty-btn shadow-sm" type="button" onclick="document.getElementById('quantita').stepDown()"><i class="fa-solid fa-minus" style="font-size: 0.75rem;"></i></button>
                            <input type="number" class="form-control text-center qty-input hide-spinners px-1" id="quantita" value="1" min="1" max="10">
                            <button class="btn qty-btn shadow-sm" type="button" onclick="document.getElementById('quantita').stepUp()"><i class="fa-solid fa-plus" style="font-size: 0.75rem;"></i></button>
                        </div>
                    </div>
                    <button class="btn btn-primary btn-lg flex-grow-1 rounded-3 ms-auto shadow-sm fw-bold">
                        <i class="fa-solid fa-cart-plus me-2"></i> Aggiungi al Carrello
                    </button>
                </div>
                
                <hr class="my-4 text-muted">
                <div class="text-muted small">
                    <p class="mb-2"><i class="fa-solid fa-truck text-primary me-2"></i> Spedizione express gratuita per ordini superiori a 29€</p>
                    <p class="mb-0"><i class="fa-solid fa-rotate-left text-primary me-2"></i> Reso gratuito e garantito entro 30 giorni</p>
                </div>
            </div>
        </div>
    </main>

    

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/footer.php'; ?>
