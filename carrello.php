<?php
// importo collegamento al db e avvio la sessione se non è già avviata 
require_once __DIR__ . '/config/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
//  includo la funzione che mi salva i libri nel database 
include 'includes/select_prodotti.php';

// controllo se l utente è con ruolo user o se ha mai fatto accesso e se non lo è lo butto fuori 
if (!isset($_SESSION['user_id']) || $_SESSION['ruolo'] !== 'user') {
    header('Location:index.php');
    exit();
}

$somma = 0.00;

// funzione per aumentare la quantità di una unita 
if (isset($_GET['id'], $_GET['azione']) && $_GET['azione'] === 'aggiungi') {
    $id = htmlspecialchars($_GET['id']);

    // verifichiamo che il prodotto esista effettivamente nel carrello
    if (isset($_SESSION['carrello'][$id])) {

        $quantitaAttuale = $_SESSION['carrello'][$id]['quantita'];
        $quantitaMax = $_SESSION['carrello'][$id]['quantitaMax'];

        // controllo disponibilità
        if (($quantitaAttuale + 1) <= $quantitaMax) {
            $_SESSION['carrello'][$id]['quantita']++;
        } else {
            // passo l errore tramite get
            header('Location: carrello.php?errore=' . urlencode('limite_raggiunto'));
            exit();
        }
    }

    header('Location: carrello.php');
    exit();
}

// funzione per diminuire la quantita di un unità 
if (isset($_GET['id'], $_GET['azione']) && $_GET['azione'] === 'rimuovi') {
    $id = htmlspecialchars($_GET['id']);

    // verifichiamo che il prodotto esista effettivamente nel carrello
    if (isset($_SESSION['carrello'][$id])) {

        $quantitaAttuale = $_SESSION['carrello'][$id]['quantita'];

        // controllo disponibilità
        if (($quantitaAttuale - 1) > 0) {
            $_SESSION['carrello'][$id]['quantita']--;
        } else {
            // passo l errore tramite get
            header('Location: carrello.php?errore=' . urlencode('minimo_raggiunto'));
            exit();
        }
    }

    header('Location: carrello.php');
    exit();
}

// funzione per eliminare l articolo da carrello 
if (isset($_GET['id'], $_GET['azione']) && $_GET['azione'] === 'elimina') {
    $id = htmlspecialchars($_GET['id']);

    // verifichiamo che il prodotto esista effettivamente nel carrello
    if (isset($_SESSION['carrello'][$id])) {
        unset($_SESSION['carrello'][$id]);
    }

    header('Location: carrello.php');
    exit();
}

// funzione per l acquisto 
if (isset($_GET['azione']) && $_GET['azione'] == 'acquista') {
    if (!empty($_SESSION['carrello'])) {
        $id_utente = $_SESSION['user_id'];
        $carrello = $_SESSION['carrello'];
        // calcolo il totale ordine 
        $totale_calcolato = 0;
        foreach ($carrello as $item) {
            $totale_calcolato += $item['prezzo'] * $item['quantita'];
        }
        
        // Ottieni la data e l'ora corrente
        $data_ordine = date('Y-m-d H:i:s');

        try {
            // iniziamo la transazione con pdo 
            $pdo->beginTransaction();
            $sql_ordine = "INSERT INTO Ordini (id_utente, totale_ordine, data_ordine) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql_ordine);
            $stmt->execute([$id_utente, $totale_calcolato, $data_ordine]);

            // recuperiamo l ultimo id crearo nell insert 
            $id_ordine_creato = $pdo->lastInsertId();

            $sql_dettagli = "INSERT INTO Dettagli_Ordine(id_ordine, id_prodotto, quantita, prezzo_unitario) VALUES (?,?,?,?)";
            $stmt_dettagli = $pdo->prepare($sql_dettagli);
            $sql_quantita = "UPDATE Prodotti SET disponibilita = disponibilita - :qta WHERE id = :id_prod AND formato = 'fisico'";
            $stmt_quantita = $pdo->prepare($sql_quantita);

            foreach ($carrello as $id_prodotto => $dati) {
                $stmt_dettagli->execute([$id_ordine_creato, $id_prodotto, $dati['quantita'], $dati['prezzo']]);
                if ($dati['formato'] === 'fisico') {
                    $stmt_quantita->execute([
                        ':qta' => $dati['quantita'],
                        ':id_prod' => $id_prodotto
                    ]);
                }
            }

            $pdo->commit();
            unset($_SESSION['carrello']);
            echo "Ordine #$id_ordine_creato salvato con successo!";
            header("Location:carrello.php?successo=1");
            exit();
        } catch (PDOException $e) {
            // Se qualcosa fallisce, PDO annulla tutto
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            echo "Errore critico: " . $e->getMessage();
            header("Location:carrello.php?errore=Errore_riprovare_più_tardi");
            exit();
        }
    }
}
?>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/head.php'; ?>

<body class="d-flex flex-column min-vh-100 fade-in pt-5 mt-4 bg-light">

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/navbar_user.php'; ?>

    <!-- Cart Content -->
    <main class="container mb-5 flex-grow-1 fade-in fade-in-delay-1 mt-5">
        <h2 class="fw-bold text-secondary-color mb-4"><i class="fa-solid fa-cart-shopping me-2 text-primary"></i> Il tuo Carrello</h2>
        <?php if (isset($_GET['errore'])): ?>
            <div class="alert alert-error">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span><?php echo htmlspecialchars($_GET['errore']); ?></span>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['successo'])): ?>
            <div class="alert alert-error">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span><?php echo "Ordine avvenuto con successo"; ?></span>
            </div>
        <?php endif; ?>
        <div class="row g-4 align-items-start">
            <!-- Cart Items -->
            <?php if (!empty($_SESSION['carrello'])): ?>
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 mb-4 p-2">
                        <div class="card-body p-0">
                            <div class="table-responsive">

                                <table class="table table-custom align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" class="ps-4 text-muted fw-medium rounded-start">Prodotto</th>
                                            <th scope="col" class="text-muted fw-medium">Prezzo</th>
                                            <th scope="col" style="width: 130px;" class="text-muted fw-medium text-center">Qtà</th>
                                            <th scope="col" class="text-end text-muted fw-medium">Subtotale</th>
                                            <th scope="col" class="text-center pe-4 rounded-end"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        <?php foreach ($_SESSION['carrello'] as $id => $dati):
                                            $somma = $somma + ($dati['quantita'] * (float)$dati['prezzo']);
                                        ?>
                                            <tr>
                                                <td class="ps-4 py-4">
                                                    <div class="d-flex align-items-center">
                                                        <a href="prodotto.php?id=<?php echo htmlspecialchars($id) ?>">
                                                            <img src="<?php echo htmlspecialchars($dati['copertina']) ?>" alt="Copertina" class="rounded me-3 shadow-sm" style="object-fit: cover;height:120px;">
                                                        </a>
                                                        <div>
                                                            <h6 class="fw-bold mb-1 text-secondary-color">
                                                                <?php echo htmlspecialchars($dati['titolo']) ?>
                                                            </h6>
                                                            <?php if ($dati['formato'] == 'fisico'): ?>
                                                                <span class="badge bg-success small fw-medium">Cartaceo</span>
                                                            <?php else : ?>
                                                                <span class="badge bg-info small fw-medium">eBook</span>
                                                            <?php endif ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-muted fw-medium">€ <?php echo htmlspecialchars($dati['prezzo']); ?></td>
                                                <td>
                                                    <div class="qty-selector d-inline-flex align-items-center" style="border:none!important;background-color:transparent!important;">
                                                        <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?id=<?php echo htmlspecialchars($id) ?>&azione=rimuovi" style="text-decoration: none;"><button class="btn qty-btn shadow-sm" type="button"><i class="fa-solid fa-minus" style="font-size: 0.75rem;"></i></button></a>
                                                        <input type="number"
                                                            class="form-control text-center qty-input hide-spinners px-1"
                                                            name="quantita"
                                                            value="<?php echo htmlspecialchars($dati['quantita']) ?>"
                                                            min="1"
                                                            max="<?php echo htmlspecialchars(($dati['copertina'] == 'digitale') ? 1 : $dati['quantitaMax']); ?>"
                                                            readonly>
                                                        <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?id=<?php echo htmlspecialchars($id) ?>&azione=aggiungi" style="text-decoration: none;"><button class="btn qty-btn shadow-sm" type="button"><i class="fa-solid fa-plus" style="font-size: 0.75rem;"></i></button></a>
                                                    </div>
                                                </td>
                                                <?php (float)$totale = $dati['quantita'] * (float)$dati['prezzo']; ?>
                                                <td class="text-end fw-bold text-primary">€ <?php echo htmlspecialchars((float)$totale) ?></td>
                                                <td class="text-center pe-4">
                                                    <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?id=<?php echo htmlspecialchars($id) ?>&azione=elimina"><button class="btn btn-outline-danger btn-round-perfect shadow-sm border-0" title="Rimuovi"><i class="fa-solid fa-trash-can"></i></button></a>
                                                </td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>



                <!-- Summary Sidebar -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-2 sticky-top" style="top: 100px;">
                        <div class="card-body p-4">
                            <h5 class="fw-bold border-bottom pb-3 mb-4 text-secondary-color">Riepilogo Ordine</h5>

                            <div class="d-flex justify-content-between mb-3 text-muted">
                                <span>Subtotale (<?php echo htmlspecialchars(count($_SESSION['carrello'])) ?> articoli)</span>
                                <span class="fw-medium">€ <?php echo htmlspecialchars((float)$somma) ?></span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center border-top pt-4 mb-4">
                                <span class="fw-bold fs-5 text-secondary-color">Totale</span>
                                <span class="fw-bold fs-3 text-primary">€ <?php echo htmlspecialchars($somma) ?></span>
                            </div>

                            <button type="button" class="btn btn-primary w-100 py-3 text-uppercase fw-bold shadow-sm rounded-pill mb-3" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                                Procedi al Checkout <i class="fa-solid fa-arrow-right ms-2"></i>
                            </button>

                            <div class="text-center">
                                <a href="index.php" class="text-muted small fw-medium text-decoration-none hover-primary">
                                    <i class="fa-solid fa-arrow-left-long me-1"></i> Continua lo shopping
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    <?php else: ?>
        <p>Il carrello è vuoto, Inserisci qualche prodotto nel carrello!</p>
    <?php endif ?>
    </main>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-secondary-color" id="checkoutModalLabel"><i class="fa-solid fa-credit-card me-2 text-primary"></i>Checkout Simulato</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="mb-4">
                        <i class="fa-solid fa-shield-halved text-success" style="font-size: 3rem;"></i>
                    </div>
                    <?php if (!empty($_SESSION['carrello'])): ?>
                        <h6 class="fw-bold mb-3">Questo è un sito dimostrativo</h6>
                        <p class="text-muted mb-4">Non verranno richiesti o elaborati dati di pagamento reali. Clicca su "Simula Pagamento" per completare l'ordine in modo fittizio.</p>
                        <div class="d-grid gap-2">
                            <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?azione=acquista">
                                <button type="button" class="btn btn-primary py-2 rounded-pill fw-bold">
                                    Simula Pagamento € <?php echo htmlspecialchars((float)$somma) ?>
                                </button>
                            </a>

                            <button type="button" class="btn btn-light py-2 rounded-pill fw-medium" data-bs-dismiss="modal">Annulla</button>
                        </div>
                        <div id="paymentSuccess" class="alert alert-success mt-3 d-none rounded-3 text-start" role="alert">
                            <i class="fa-solid fa-circle-check me-2"></i>Pagamento simulato con successo! Ritorno alla home...
                        </div>
                    <?php else: ?>
                        <h6 class="fw-bold mb-3">Carrello Vuoto</h6>
                        <p class="text-muted mb-4">Impossibile fare pagameto perchè il carrello è vuoto, inserisci qualche prodotto prima.</p>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-light py-2 rounded-pill fw-medium" data-bs-dismiss="modal">Annulla</button>
                        </div>
                    <?php endif ?>

                </div>
            </div>
        </div>
    </div>

    <script>
        function simulatePayment(btn) {
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Elaborazione...';
            btn.disabled = true;

            setTimeout(() => {
                document.getElementById('paymentSuccess').classList.remove('d-none');
                btn.classList.add('d-none');
                btn.nextElementSibling.classList.add('d-none'); // Nasconde il tasto Annulla

                // Simula redirect dopo 2 secondi
                setTimeout(() => {
                    window.location.href = 'index.php?payment=success';
                }, 2000);
            }, 1500);
        }
    </script>


    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/footer.php'; ?>