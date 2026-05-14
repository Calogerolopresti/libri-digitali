<?php
require_once __DIR__ . '/config/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Inizializziamo le variabili per evitare errori "Undefined variable"
$libro = null;
$errore = "";
$messaggio = "";

// genero il token csrf se non esiste ancora nella sessione
if(!isset($_SESSION['csrf_token'])){
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// controlliamo de nell link è stato inserito id del libro da cercare 
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // facciamo la select del libro col try catch 
    try {
        $sql = "SELECT * FROM Prodotti WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $libro = $stmt->fetch();

        // se l id del libro non esiste restituiamo errore 
        if (!$libro) {
            $errore = "Il prodotto richiesto non è disponibile o è stato rimosso.";
        } else {
            // Se esiste, calcoliamo la quantità massima in base al formato del libro
            $quantitaMassima = ($libro['formato'] == 'digitale') ? 1 : $libro['disponibilita'];
            
            // funzione per gestire l aggiunta al carrello del libro
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // gestiamo i permessi per l aggiunta al carrello 
                if (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] == 'user') {

                    // verifico il token csrf prima di aggiungere al carrello
                    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']){
                        $errore = "Richiesta non valida, riprova.";
                    } else {
                        $quantita = (int)$_POST['quantita'];
                        // se la quantita esiste gli assegna il valore se non esiste assegna 0 
                        $quantitaGiaInCarrello = $_SESSION['carrello'][$id]['quantita'] ?? 0;
                        $quantitaRichiestaTotal = $quantita + $quantitaGiaInCarrello;

                        // controlliamo se la quatita esiste 
                        if ($quantitaRichiestaTotal <= $quantitaMassima) {
                            // inizzializziamo il carrello se non esiete 
                            if (!isset($_SESSION['carrello'])) $_SESSION['carrello'] = [];
                            
                            // mettiamo tutte le informazioni del prodotto nel carrello con un array associativo 
                            $_SESSION['carrello'][$id] = [
                                'titolo'    => $libro['titolo'],
                                'copertina' => $libro['copertina'],
                                'formato'   => $libro['formato'],
                                'prezzo'    => number_format((float) str_replace(',', '.', $libro['prezzo']), 2, '.', ''),
                                'quantita'  => $quantitaRichiestaTotal,
                                'quantitaMax' => $quantitaMassima
                            ];
                            $messaggio = "Prodotto aggiunto correttamente al carrello";
                        } else {
                            // errore se la quantita richiesta è maggiore di quella disponibile 
                            $errore = "Quantità richiesta non disponibile (Massimo: $quantitaMassima)";
                        }
                    }
                } else {
                    // rimanda alla pagina di log in se l utente non è user 
                    header('Location: auth/login.php');
                    exit();
                }
            }
        }
    } catch (PDOException $e) {
        error_log("errore alla ricerca del singolo libro: " . $e->getMessage());
        $errore = "Si è verificato un errore tecnico. Riprova più tardi.";
    }
} else {
    // se non c'è proprio l'ID nel link
    $errore = "Nessun prodotto selezionato.";
}
?>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/head.php'; ?>

<body class="d-flex flex-column min-vh-100 fade-in pt-5 mt-4 bg-light">

    <!-- mostro una navbar diversa in base all user  -->
    <?php if (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] == 'user') {
        include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/navbar_user.php';
    } else {
        include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/navbar_public.php';
    }
    ?>

    <!-- Product Detail -->
    <main class="container mb-5 flex-grow-1 fade-in fade-in-delay-1 mt-5">
        <?php if (!empty($messaggio)): ?>
            <div class="alert alert-success">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span><?php echo htmlspecialchars($messaggio); ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($errore)): ?>
            <div class="alert alert-error">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span><?php echo htmlspecialchars($errore); ?></span>
            </div>
        <?php endif; ?>
        <!-- controllo che l array libro esista e abbia qualcosa dentro  -->
        <?php if ($libro): ?>
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

                    <form action="" method="POST">
                        <!-- campo nascosto per la protezione csrf -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <div class="d-flex flex-wrap align-items-center gap-3 mb-4 p-3 bg-white rounded-3 shadow-sm border border-light">
                            <div class="d-flex align-items-center me-2">
                                <label for="quantita" class="fw-medium text-muted mb-0 me-3">Quantità:</label>
                                <div class="qty-selector d-flex align-items-center">
                                    <?php if ($libro['formato'] == 'digitale'): ?>
                                        <!-- se il formato è digitale imposto come limite massimo 1  -->
                                        <button class="btn qty-btn shadow-sm" type="button" onclick="document.getElementById('quantita').stepDown()"><i class="fa-solid fa-minus" style="font-size: 0.75rem;"></i></button>
                                        <input type="number" class="form-control text-center qty-input hide-spinners px-1" id="quantita" name="quantita" value="1" min="1" max="1">
                                        <button class="btn qty-btn shadow-sm" type="button" onclick="document.getElementById('quantita').stepUp()"><i class="fa-solid fa-plus" style="font-size: 0.75rem;"></i></button>
                                    <?php else: ?>
                                        <button class="btn qty-btn shadow-sm" type="button" onclick="document.getElementById('quantita').stepDown()"><i class="fa-solid fa-minus" style="font-size: 0.75rem;"></i></button>
                                        <input type="number" class="form-control text-center qty-input hide-spinners px-1" id="quantita" name="quantita" value="1" min="1" max="<?php echo htmlspecialchars($libro['disponibilita']) ?>">
                                        <button class="btn qty-btn shadow-sm" type="button" onclick="document.getElementById('quantita').stepUp()"><i class="fa-solid fa-plus" style="font-size: 0.75rem;"></i></button>
                                    <?php endif ?>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg flex-grow-1 rounded-3 ms-auto shadow-sm fw-bold">
                                <i class="fa-solid fa-cart-plus me-2"></i> Aggiungi al Carrello
                            </button>
                        </div>
                        <?php if ($libro['formato'] != 'digitale'): ?>
                            <p class="text-muted" style="line-height: 1.8;">Disponibilità: <?php echo htmlspecialchars($libro['disponibilita']) ?></p>
                        <?php endif ?>
                    </form>

                    <hr class="my-4 text-muted">
                    <div class="text-muted small">
                        <p class="mb-2"><i class="fa-solid fa-truck text-primary me-2"></i> Spedizione express gratuita per ordini superiori a 29€</p>
                        <p class="mb-0"><i class="fa-solid fa-rotate-left text-primary me-2"></i> Reso gratuito e garantito entro 30 giorni</p>
                    </div>
                </div>
            </div>
            <!-- se non esiste o è vuoto do il messaggio di errore      -->
        <?php else: ?>
            
        <?php endif ?>
    </main>



    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/footer.php'; ?>