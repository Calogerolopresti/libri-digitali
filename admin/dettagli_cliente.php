<?php
// importo collegamento al db e avvio la sessione se non è già avviata 
require_once __DIR__ . '/../config/db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// creo il csrf token se non esiste gia in sessione 
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// controllo se l utente è admin, altrimenti lo sbatto fuori 
if (!isset($_SESSION['user_id']) || $_SESSION['ruolo'] !== 'admin') {
    header('Location:../index.php');
    exit();
}

// controllo se hanno passato un id cliente nell url, se no torno alla pagina clienti
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location:clienti.php');
    exit();
}

$id_cliente = (int)$_GET['id'];

// mi preparo a fare le chiamate al db
try {
    // 1. Prendo le informazioni di base del cliente specificato
    $sql_utente = "SELECT id, nome, email FROM Utenti WHERE id = ? AND ruolo = 'user'";
    $stmt_utente = $pdo->prepare($sql_utente);
    $stmt_utente->execute([$id_cliente]);
    $cliente = $stmt_utente->fetch(PDO::FETCH_ASSOC);

    // se non trovo il cliente nel db, torno alla pagina clienti
    if (!$cliente) {
        header('Location:clienti.php');
        exit();
    }

    // 2. Prendo tutto lo storico degli ordini fatti da questo cliente
    $sql_ordini = "SELECT * FROM Ordini WHERE id_utente = ?";
    $stmt_ordini = $pdo->prepare($sql_ordini);
    $stmt_ordini->execute([$id_cliente]);
    $ordini = $stmt_ordini->fetchAll(PDO::FETCH_ASSOC);

    // 3. Prendo le statistiche per mostrare i dati totali dell ultimo anno
    $sql_stats = "SELECT 
                    COUNT(*) AS totale_ordini,
                    COALESCE(SUM(totale_ordine), 0) AS totale_speso
                  FROM Ordini 
                  WHERE id_utente = ?
                  AND data_ordine >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
    $stmt_stats = $pdo->prepare($sql_stats);
    $stmt_stats->execute([$id_cliente]);
    $stats_annuali = $stmt_stats->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // in caso di errore evito di mostrare codici strani a schermo 
    error_log("errore recupero dettagli cliente: " . $e->getMessage());
    $cliente = null;
    $ordini = [];
    $stats_annuali = ['totale_ordini' => 0, 'totale_speso' => 0];
}

// Logica per l'apertura automatica della modale dettagli (uguale al profilo utente)
$showOrderDetail = false;
if (isset($_GET['azione']) && $_GET['azione'] == 'mostra' && isset($_GET['id_ordine'])) {
    $showOrderDetail = true;
    $dettaglio_ordine = (int)$_GET['id_ordine'];

    try {
        // faccio la join per prendere info aggiuntive sui prodotti di quel singolo ordine
        $sql = "SELECT 
                Prodotti.titolo as titolo, 
                Prodotti.copertina as copertina, 
                COALESCE(Dettagli_Ordine.formato, Prodotti.formato) as formato, 
                Dettagli_Ordine.prezzo_unitario as prezzo_unitario, 
                Dettagli_Ordine.quantita as quantita
                FROM Dettagli_Ordine
                JOIN Prodotti ON Dettagli_Ordine.id_prodotto = Prodotti.id
                JOIN Ordini ON Dettagli_Ordine.id_ordine = Ordini.id
                WHERE Dettagli_Ordine.id_ordine = ?
                AND Ordini.id_utente = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dettaglio_ordine, $id_cliente]);
        $ordini_dettagli = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $ordini_dettagli = [];
    }
}
?>

<?php include __DIR__ . '/../includes/head.php'; ?>

<style>
    /*
     * FIX MODAL: .fade-in sul body usa transform:translateY() che crea
     * un nuovo stacking context e rompe position:fixed delle modal Bootstrap.
     * Sovrascriviamo l'animazione sul body con una solo-opacity.
     */
    body.fade-in {
        animation: fadeInBodyOnly 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }

    @keyframes fadeInBodyOnly {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }
</style>

<body class="d-flex flex-column min-vh-100 fade-in pt-5 mt-4 bg-light">

    <?php include __DIR__ . '/../includes/navbar_admin.php'; ?>

    <!-- Contenuto principale -->
    <main class="container-fluid px-4 mb-5 flex-grow-1 fade-in fade-in-delay-1 mt-5">
        
        <!-- Intestazione Pagina con tasto per tornare indietro -->
        <div class="d-flex align-items-center mb-4 border-bottom pb-4 gap-3 mt-3">
            <a href="clienti.php" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center p-0 shadow-sm" style="width: 40px; height: 40px; border: none; background-color: #fff;">
                <i class="fa-solid fa-arrow-left text-primary"></i>
            </a>
            <h2 class="fw-bold text-secondary-color mb-0">
                Dettagli Cliente <span class="text-primary fs-4">#<?php echo htmlspecialchars($cliente['id']) ?></span>
            </h2>
        </div>

        <div class="row g-4">
            <!-- colonna sinistra: info cliente e statistiche impilate -->
            <div class="col-lg-4 d-flex flex-column gap-4">

                <!-- Card Info Utente -->
                <div class="card border-0 shadow-sm rounded-4 p-2">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <i class="fa-regular fa-user"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-secondary-color"><?php echo htmlspecialchars($cliente['nome']) ?></h5>
                                <span class="badge bg-light border text-secondary mt-1">Ruolo: user</span>
                            </div>
                        </div>
                        <ul class="list-unstyled mb-0 text-muted">
                            <li class="mb-2"><i class="fa-regular fa-envelope text-primary me-2"></i><?php echo htmlspecialchars($cliente['email']) ?></li>
                        </ul>
                    </div>
                </div>

                <!-- Statistiche Ultimo Anno -->
                <div class="card border-0 shadow-sm rounded-4 p-2">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-secondary-color">Anno in Corso</h5>
                                <p class="text-muted small mb-0">Ultimi 12 mesi</p>
                            </div>
                        </div>
                        <!-- ordini cliente -->
                        <div class="d-flex align-items-center justify-content-between mb-3 p-3 bg-light rounded-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-bag-shopping text-primary"></i>
                                <span class="text-muted small fw-medium">Ordini effettuati</span>
                            </div>
                            <span class="fw-bold fs-5 text-secondary-color">
                                <?php echo (int)$stats_annuali['totale_ordini'] ?>
                            </span>
                        </div>
                        <!-- spesa cliente -->
                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-euro-sign text-primary"></i>
                                <span class="text-muted small fw-medium">Totale speso</span>
                            </div>
                            <span class="fw-bold fs-5 text-primary">
                                € <?php echo number_format((float)$stats_annuali['totale_speso'], 2, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Colonna destra: Storico Ordini -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-2">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4 text-secondary-color"><i class="fa-solid fa-box-open text-primary me-2"></i> Storico Ordini Cliente</h5>
                        
                        <!-- se array vuoto dico che non ha fatto acquisti -->
                        <?php if (empty($ordini)): ?>
                            <div class="alert alert-light border rounded-3 text-center py-5">
                                <i class="fa-solid fa-ghost text-muted mb-3" style="font-size: 2rem;"></i>
                                <h6 class="fw-bold text-muted mb-0">Il cliente non ha ancora effettuato alcun ordine.</h6>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 border-0">
                                    <thead class="table-light border-bottom">
                                        <tr>
                                            <th class="ps-4 text-muted fw-bold border-bottom-0" style="text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; padding-top: 1rem; padding-bottom: 1rem;">ID Ordine</th>
                                            <th class="text-muted fw-bold border-bottom-0" style="text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; padding-top: 1rem; padding-bottom: 1rem;">Data</th>
                                            <th class="text-end text-muted fw-bold border-bottom-0" style="text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; padding-top: 1rem; padding-bottom: 1rem;">Totale</th>
                                            <th class="text-center pe-4 text-muted fw-bold border-bottom-0" style="text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; padding-top: 1rem; padding-bottom: 1rem;">Azione</th>
                                        </tr>
                                    </thead>
                                    <tbody class="border-top-0">
                                        <!-- stampo la riga per ogni singolo ordine -->
                                        <?php foreach ($ordini as $ordine): ?>
                                            <tr style="transition: all 0.2s ease;">
                                                <td class="ps-4 py-3 fw-medium text-secondary">#<?php echo htmlspecialchars($ordine['id']) ?></td>
                                                <td class="py-3 text-muted"><span class="bg-light rounded-pill px-3 py-1"><i class="fa-regular fa-calendar me-2 text-primary opacity-75"></i> <?php echo date('d/m/Y H:i', strtotime($ordine['data_ordine'])) ?></span></td>
                                                <td class="text-end py-3 fw-bold text-primary">€ <?php echo number_format($ordine['totale_ordine'], 2, ',', '.') ?></td>
                                                <td class="text-center py-3 pe-4">
                                                    <!-- tasto che ricarica la pagina passando l id ordine in GET per aprire la modale -->
                                                    <a href="dettagli_cliente.php?id=<?php echo htmlspecialchars($id_cliente) ?>&azione=mostra&id_ordine=<?php echo htmlspecialchars($ordine['id']) ?>" class="btn btn-sm btn-light text-primary shadow-sm rounded-pill px-3 py-1 fw-medium" style="font-size: 0.85rem;">
                                                        <i class="fa-regular fa-eye me-1"></i> Dettagli
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modale Dettagli Ordine (copiata dalla vista utente) -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-secondary-color" id="orderDetailsModalLabel">
                        <i class="fa-solid fa-receipt text-primary me-2"></i> Ricevuta Ordine #<?php echo isset($_GET['id_ordine']) ? htmlspecialchars($_GET['id_ordine']) : '' ?>
                    </h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="table-responsive mt-2">
                        <table class="table align-middle table-hover border-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 border-0 text-muted fw-bold" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding-top: 1rem; padding-bottom: 1rem;">Libro</th>
                                    <th class="border-0 text-muted fw-bold text-center" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding-top: 1rem; padding-bottom: 1rem;">Qtà</th>
                                    <th class="border-0 text-muted fw-bold text-end pe-4" style="text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding-top: 1rem; padding-bottom: 1rem;">Prezzo</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                <!-- stampo tutti i prodotti presenti in quell'ordine -->
                                <?php if (!empty($ordini_dettagli)): ?>
                                    <?php foreach ($ordini_dettagli as $dettaglio): ?>
                                        <tr>
                                            <td class="ps-4 py-3">
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo htmlspecialchars($dettaglio['copertina']) ?>" alt="Copertina" class="rounded shadow-sm me-3" style="width: 50px; height: 70px; object-fit: cover;">
                                                    <div>
                                                        <h6 class="fw-bold mb-1 text-secondary-color"><?php echo htmlspecialchars($dettaglio['titolo']) ?></h6>
                                                        <?php if ($dettaglio['formato'] == 'fisico'): ?>
                                                            <span class="badge bg-light text-secondary border small"><i class="fa-solid fa-book-open me-1"></i> Cartaceo</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-light text-primary border border-primary-subtle small"><i class="fa-solid fa-download me-1"></i> E-book</span>
                                                        <?php endif ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center py-3 fw-medium text-secondary">
                                                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill"><?php echo htmlspecialchars($dettaglio['quantita']) ?>x</span>
                                            </td>
                                            <td class="text-end py-3 fw-bold text-primary pe-4">€ <?php echo number_format($dettaglio['prezzo_unitario'], 2, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">Dettagli ordine non disponibili.</td>
                                    </tr>
                                <?php endif ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Script per aprire in automatico la modale se nell URL c'è azione=mostra -->
    <?php if ($showOrderDetail): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var myModal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
                myModal.show();
                
                // Pulizia URL al click di chiusura per non riaprire in automatico la pagina (tolgo l id_ordine in modo pulito)
                document.getElementById('orderDetailsModal').addEventListener('hidden.bs.modal', function () {
                    const newUrl = window.location.pathname + '?id=<?php echo $id_cliente ?>';
                    window.history.replaceState({}, document.title, newUrl);
                });
            });
        </script>
    <?php endif; ?>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
