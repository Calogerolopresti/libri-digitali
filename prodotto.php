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
                        // controllo che la quantita richiesta sia maggiore di zero per evitare exploit
                        if ($quantita > 0) {
                            // se la quantita esiste gli assegna il valore se non esiste assegna 0 
                            $quantitaGiaInCarrello = $_SESSION['carrello'][$id]['quantita'] ?? 0;
                            $quantitaRichiestaTotal = $quantita + $quantitaGiaInCarrello;

                            // controlliamo se la quatita esiste 
                            if ($quantitaRichiestaTotal <= $quantitaMassima) {
                                // inizzializziamo il carrello se non esiete 
                                if (!isset($_SESSION['carrello'])) $_SESSION['carrello'] = [];
                                
                                // mettiamo tutte le informazioni del prodotto nel carrello con un array associativo 
                                $isBundle = (isset($_POST['aggiungi_ebook']) && $_POST['aggiungi_ebook'] == '1' && $libro['formato'] == 'fisico');
                                $prezzoBase = (float) str_replace(',', '.', $libro['prezzo']);
                                $prezzoFinale = $isBundle ? ($prezzoBase + 2.00) : $prezzoBase;
                                $formatoFinale = $isBundle ? 'ibrido' : $libro['formato'];

                                $_SESSION['carrello'][$id] = [
                                    'titolo'    => $libro['titolo'],
                                    'copertina' => $libro['copertina'],
                                    'formato'   => $formatoFinale,
                                    'prezzo'    => number_format($prezzoFinale, 2, '.', ''),
                                    'quantita'  => $quantitaRichiestaTotal,
                                    'quantitaMax' => $quantitaMassima
                                ];
                                $messaggio = $isBundle ? "Bundle Ibrido (Cartaceo + eBook) aggiunto al carrello!" : "Prodotto aggiunto correttamente al carrello";
                            } else {
                                // errore se la quantita richiesta è maggiore di quella disponibile 
                                $errore = "Quantità richiesta non disponibile (Massimo: $quantitaMassima)";
                            }
                        } else {
                            // errore se la quantita inserita è minore o uguale a zero
                            $errore = "Inserisci una quantità valida maggiore di zero.";
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

<?php include __DIR__ . '/includes/head.php'; ?>

<body class="d-flex flex-column min-vh-100 fade-in pt-5 mt-4 bg-light">

    <!-- mostro una navbar diversa in base all user  -->
    <?php if (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] == 'user') {
        include __DIR__ . '/includes/navbar_user.php';
    } else {
        include __DIR__ . '/includes/navbar_public.php';
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
                    <!-- Smart Box IA -->
                    <div class="mb-4 p-4 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #e2e8f0; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: -20px; right: -20px; opacity: 0.05; transform: rotate(-15deg); pointer-events: none;">
                            <i class="fa-solid fa-robot" style="font-size: 8rem;"></i>
                        </div>
                        <h5 class="fw-bold mb-2" style="color: #334155;">
                            <i class="fa-solid fa-wand-magic-sparkles text-primary me-2"></i> Chiedi all'IA
                        </h5>
                        <p class="text-muted small mb-3" style="position: relative; z-index: 1;">
                            Scegli come farti raccontare questo libro dal nostro assistente virtuale!
                        </p>
                        
                        <div class="d-flex flex-wrap gap-2 align-items-center" style="position: relative; z-index: 1;">
                            <select id="stileTrama" class="form-select form-select-sm rounded-pill px-3 text-muted fw-medium border-light shadow-sm" style="width: auto; max-width: 220px;">
                                <option value="normale">Trama standard</option>
                                <option value="spoiler">Teaser (Senza spoiler)</option>
                                <option value="3punti">In 3 punti chiave</option>
                                <option value="recensione">Recensione Social</option>
                                <option value="bambini">Spiegato a un bambino</option>
                            </select>
                            <button id="btnGeneraTrama" type="button" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-medium shadow-sm">
                                <i class="fa-solid fa-bolt me-1"></i> Genera
                            </button>
                        </div>
                        
                        <div id="tramaContainer" class="mt-3 p-3 bg-white rounded-3 shadow-sm d-none border border-light" style="position: relative; z-index: 1;">
                            <div class="d-flex align-items-center mb-2 d-none" id="tramaLoader">
                                <div class="spinner-grow spinner-grow-sm text-primary me-2" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span class="text-primary small fw-medium">Elaborazione magica in corso...</span>
                            </div>
                            <div id="tramaText" class="text-secondary-color" style="font-size: 0.95rem; line-height: 1.6;"></div>
                        </div>
                    </div>
                    <!-- Fine Smart Box IA -->

                    <form action="" method="POST">
                        <!-- campo nascosto per la protezione csrf -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']) ?>">
                        
                        <?php if ($libro['formato'] == 'fisico'): ?>
                            <!-- Smart Box Combo eBook -->
                            <div class="p-3 mb-3 rounded-4 shadow-sm d-flex align-items-center justify-content-between border border-light" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="text-success" style="font-size: 1.8rem;">
                                        <i class="fa-solid fa-cloud-arrow-down"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1" style="color: #14532d;">Bundle Ibrido Promo</h6>
                                        <p class="text-muted mb-0 small">Ottieni anche l'eBook digitale immediato con soli +2,00€!</p>
                                    </div>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input text-success fs-4 cursor-pointer" type="checkbox" id="aggiungiEbook" name="aggiungi_ebook" value="1">
                                </div>
                            </div>
                        <?php endif; ?>

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
                        <p class="mb-0"><i class="fa-solid fa-rotate-left text-primary me-2"></i> Reso gratuito e garantito entro 30 giorni</p>
                    </div>
                </div>
            </div>

            <!-- script js per gestire il click sul box dell ia e fare la chiamata fetch -->
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const btn = document.getElementById('btnGeneraTrama');
                const stileSel = document.getElementById('stileTrama');
                if (btn) {
                    btn.addEventListener('click', function() {
                        const container = document.getElementById('tramaContainer');
                        const loader = document.getElementById('tramaLoader');
                        const text = document.getElementById('tramaText');
                        const stile = stileSel ? stileSel.value : 'normale';
                        
                        // blocchiamo il pulsante subito per evitare click ripetuti se il server ci mette un attimo
                        btn.disabled = true;
                        // mostriamo l area di testo e facciamo partire il caricamento
                        container.classList.remove('d-none');
                        loader.classList.remove('d-none');
                        text.innerHTML = '';
                        
                        // facciamo una chiamata fetch passando titolo del libro e lo stile scelto
                        fetch(`genera_trama.php?titolo=<?php echo urlencode($libro['titolo']); ?>&stile=${stile}`)
                            .then(response => response.text())
                            .then(data => {
                                // nascondiamo il loader magico e scriviamo il testo che ci ha sputato l ia con una bella virgoletta di citazione
                                loader.classList.add('d-none');
                                text.innerHTML = '<i class="fa-solid fa-quote-left text-muted me-2" style="opacity: 0.5;"></i>' + data;
                                btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Trama Generata';
                                btn.disabled = false;
                            })
                            .catch(error => {
                                // se cè un errore di rete avvisiamo in rosso e sblocchiamo il pulsante
                                loader.classList.add('d-none');
                                text.innerHTML = '<span class="text-danger"><i class="fa-solid fa-circle-exclamation me-1"></i> Si è verificato un errore di connessione.</span>';
                                btn.disabled = false;
                            });
                    });
                }
            });
            </script>
            <!-- se non esiste o è vuoto do il messaggio di errore      -->
        <?php else: ?>
            
        <?php endif ?>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>