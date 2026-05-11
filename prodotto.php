<?php
// importo collegamento al db e avvio la sessione se non è già avviata 
require_once __DIR__ . '/config/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// controllo se esiste la variabile id nel link 
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        // cerco il libri con quel id 
        $sql = "SELECT * FROM Prodotti WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $libro = $stmt->fetch();
    } catch (PDOException $e) {
        // in caso di errore mi salvo l errore nel file log e inizzilizo l array vuoto 
        error_log("errore alla ricerca del singolo libro: " . $e->getMessage());
        $libro = [];
    }
}
?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/head.php'; ?>

<body class="d-flex flex-column min-vh-100 fade-in pt-5 mt-4 bg-light">

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/navbar_public.php'; ?>

    <!-- Product Detail -->
    <main class="container mb-5 flex-grow-1 fade-in fade-in-delay-1 mt-5">
        <!-- controllo che l array libro esista e abbia qualcosa dentro  -->
        <?php if (isset($libro) && count($libro) > 0): ?>
            <div class="row g-5 align-items-start">
                <!-- Left Column: Image -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm rounded-4 p-4 text-center d-flex align-items-center justify-content-center" style="background-color: #f1f5f9; min-height: 500px;">
                        <img src="<?php echo htmlspecialchars($libro['copertina']) ?>" class="img-fluid rounded shadow" alt="Copertina Libro" style="object-fit: cover; width: 100%; max-width: 320px; aspect-ratio: 2/3; display: block;">
                    </div>
                </div>

                <!-- Right Column: Details -->
                <div class="col-md-7">
                    <?php if ($libro['formato'] == 'fisico'): ?>
                        <span class="badge bg-success mb-3 px-3 py-2"><i class="fa-solid fa-book-open me-1"></i> Edizione Cartacea</span>
                    <?php else: ?>
                        <span class="badge bg-info mb-3 align-self-start"><i class="fa-solid fa-download me-1"></i> Edizione Digitale</span>
                    <?php endif ?>
                    <h1 class="fw-bold text-secondary-color mb-2"><?php echo htmlspecialchars($libro['titolo']) ?></h1>

                    <h2 class="price fs-1 mb-4">€ <?php echo htmlspecialchars($libro['prezzo']) ?></h2>

                    <div class="mb-4">
                        <p class="text-muted" style="line-height: 1.8;">
                            <?php echo htmlspecialchars($libro['descrizione']) ?>
                        </p>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-3 mb-4 p-3 bg-white rounded-3 shadow-sm border border-light">
                        <form action="" method="POST">
                            <div class="d-flex align-items-center me-2">
                                <label for="quantita" class="fw-medium text-muted mb-0 me-3">Quantità:</label>
                                <div class="qty-selector d-flex align-items-center">
                                    <?php if ($libro['formato'] == 'digitale'): ?>
                                        <!-- se il formato è digitale imposto come limite massimo 1  -->
                                        <button class="btn qty-btn shadow-sm" type="button" onclick="document.getElementById('quantita').stepDown()"><i class="fa-solid fa-minus" style="font-size: 0.75rem;"></i></button>
                                        <input type="number" class="form-control text-center qty-input hide-spinners px-1" id="quantita" value="1" min="1" max="1">
                                        <button class="btn qty-btn shadow-sm" type="button" onclick="document.getElementById('quantita').stepUp()"><i class="fa-solid fa-plus" style="font-size: 0.75rem;"></i></button>
                                    <?php else: ?>
                                        <button class="btn qty-btn shadow-sm" type="button" onclick="document.getElementById('quantita').stepDown()"><i class="fa-solid fa-minus" style="font-size: 0.75rem;"></i></button>
                                        <input type="number" class="form-control text-center qty-input hide-spinners px-1" id="quantita" value="1" min="1" max="10">
                                        <button class="btn qty-btn shadow-sm" type="button" onclick="document.getElementById('quantita').stepUp()"><i class="fa-solid fa-plus" style="font-size: 0.75rem;"></i></button>
                                    <?php endif ?>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg flex-grow-1 rounded-3 ms-auto shadow-sm fw-bold">
                                <i class="fa-solid fa-cart-plus me-2"></i> Aggiungi al Carrello
                            </button>
                        </form>
                    </div>

                    <hr class="my-4 text-muted">
                    <div class="text-muted small">
                        <p class="mb-2"><i class="fa-solid fa-truck text-primary me-2"></i> Spedizione express gratuita per ordini superiori a 29€</p>
                        <p class="mb-0"><i class="fa-solid fa-rotate-left text-primary me-2"></i> Reso gratuito e garantito entro 30 giorni</p>
                    </div>
                </div>
            </div>
            <!-- se non esiste o è vuoto do il messaggio di errore      -->
        <?php else: ?>
            <div style="display: flex; align-items: center; background-color: #fef2f2; border: 1px solid #fee2e2; color: #b91c1c; padding: 12px 16px; border-radius: 8px; font-family: sans-serif; gap: 10px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                <p style="margin: 0; font-size: 14px; font-weight: 500;">
                    Si è verificato un errore. Impossibile mostrare il libro, riprova più tardi.
                </p>
            </div>
        <?php endif ?>
    </main>



    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/footer.php'; ?>