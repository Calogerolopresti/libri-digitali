<?php
require_once __DIR__ . '/config/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['ruolo'] !== 'user') {
    header('Location:index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Logica per l'apertura automatica della modale dettagli
$showOrderDetail = false;
if (isset($_GET['id']) && isset($_GET['azione']) && $_GET['azione'] == 'mostra') {
    $showOrderDetail = true;
}

// prima query per popolare la prima tabella generale degli ordini dell utente che visualizza 
try {
    $sql = "SELECT * FROM Ordini WHERE id_utente=(?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $ordini = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // in caso di errore inizzializziamo un erray vuoto 
    $ordini = [];
}

// quando il cliente clicca sui dettagli di un singolo ordine facciamo la query tramite join per recuperare informazioni in piu come copertina formato ecc che non si trovano nella tabella dettagli ordini 
if (isset($_GET['id'])) {
    $dettaglio_ordine = htmlspecialchars($_GET['id']);
    try {
        $sql = "SELECT 
                Prodotti.titolo as titolo, 
                Prodotti.copertina as copertina, 
                Prodotti.formato as formato, 
                Dettagli_Ordine.prezzo_unitario as prezzo_unitario, 
                Dettagli_Ordine.quantita as quantita
                FROM Dettagli_Ordine
                JOIN Prodotti ON Dettagli_Ordine.id_prodotto = Prodotti.id
                WHERE Dettagli_Ordine.id_ordine = (?);";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dettaglio_ordine]);
        $ordini_dettagli = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // anche qui in caso di errore restituiamo un array vuoto in modo tale da non far vedere errori all utente 
        $ordini_dettagli = [];
    }
}

?>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/head.php'; ?>

<body class="d-flex flex-column min-vh-100 fade-in pt-5 mt-4 bg-light">

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/navbar_user.php'; ?>

    <main class="container mb-5 flex-grow-1 fade-in fade-in-delay-1 mt-5">
        <h2 class="fw-bold text-secondary-color mb-4">Ciao, <span class="text-primary"><?php echo htmlspecialchars($_SESSION['user_nome']) ?></span>!</h2>

        <div class="row g-4">
            <!-- Info Utente -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-2">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <i class="fa-regular fa-user"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">I tuoi Dati</h5>
                                <p class="text-muted small mb-0">Gestisci il tuo account</p>
                            </div>
                        </div>
                        <ul class="list-unstyled mb-4 text-muted">
                            <li class="mb-2"><i class="fa-regular fa-envelope text-primary me-2"></i><?php echo htmlspecialchars($_SESSION['user_email']) ?></li>
                        </ul>
                        <button class="btn btn-outline-primary w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#editProfileModal"><i class="fa-solid fa-pen-to-square me-2"></i> Modifica Profilo</button>
                    </div>
                </div>
            </div>

            <!-- Storico Ordini -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-2">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4"><i class="fa-solid fa-box-open text-primary me-2"></i> Storico Ordini</h5>
                        <div class="table-responsive">
                            <!-- verifichiamo se l array esiste o è vuoto per mostrare la tabella o dire nessun ordine disponibile  -->
                            <?php if (!isset($ordini) || empty($ordini)): ?>
                                <p>Nessun ordine da mostrare, continua a fare shopping</p>
                            <?php else: ?>
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID Ordine</th>
                                            <th>Data</th>
                                            <th class="text-end">Totale</th>
                                            <th class="text-center">Azione</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ordini as $ordine): ?>
                                            <tr>
                                                <td><span class="fw-bold">#<?php echo htmlspecialchars($ordine['id']) ?></span></td>
                                                <td><?php echo htmlspecialchars($ordine['data_ordine']) ?></td>
                                                <td class="text-end fw-bold text-primary">€ <?php echo htmlspecialchars($ordine['totale_ordine']) ?></td>
                                                <td class="text-center">
                                                    <a href="?id=<?php echo htmlspecialchars($ordine['id']); ?>&azione=mostra&totale=<?php echo htmlspecialchars($ordine['totale_ordine']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm">
                                                        <i class="fa-solid fa-eye me-1"></i> Dettagli
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- MODALE MODIFICA PROFILO (Ripristinata) -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-secondary-color" id="editProfileModalLabel">Modifica Profilo</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="nome_edit" class="form-label text-muted small fw-medium">Nome Completo</label>
                            <input type="text" class="form-control rounded-3" id="nome_edit" name="nome" value="<?php echo htmlspecialchars($_SESSION['user_nome']) ?>" required>
                        </div>
                        <div class="mb-4">
                            <label for="email_edit" class="form-label text-muted small fw-medium">Email</label>
                            <input type="email" class="form-control rounded-3" id="email_edit" name="email" value="<?php echo htmlspecialchars($_SESSION['user_email']) ?>" required>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Annulla</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="fa-solid fa-floppy-disk me-2"></i> Salva Modifiche</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODALE DETTAGLI ORDINE (Con Logica PHP e Base Statica) -->
    <div class="modal fade <?php echo $showOrderDetail ? 'show' : ''; ?>"
        id="orderDetailModal"
        tabindex="-1"
        style="<?php echo $showOrderDetail ? 'display: block; background: rgba(0,0,0,0.5);' : ''; ?>"
        aria-hidden="<?php echo $showOrderDetail ? 'false' : 'true'; ?>"
        role="dialog">

        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title fw-bold text-secondary-color mb-1">Dettagli Ordine #<?php echo $showOrderDetail ? htmlspecialchars($_GET['id']) : ''; ?></h5>
                        <p class="text-muted small mb-0">Riepilogo prodotti acquistati</p>
                    </div>
                    <a href="<?php echo htmlspecialchars(strtok($_SERVER["REQUEST_URI"], '?')); ?>" class="btn-close shadow-none align-self-start"></a>
                </div>
                <div class="modal-body p-4">
                    <div class="table-responsive">
                        <!-- verifichiamo se l array esiste o è vuoto per mostrare la tabella o dire nessun ordine disponibile  -->
                        <?php if (!isset($ordini_dettagli) || empty($ordini_dettagli)): ?>
                            <p>Impossibile mostrare i dettagli di questo ordine al momento, Riprova piu tardi</p>
                        <?php else: ?>
                            <table class="table align-middle mb-0 border">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="text-muted fw-medium ps-3">Prodotto</th>
                                        <th scope="col" class="text-muted fw-medium text-center">Prezzo</th>
                                        <th scope="col" class="text-muted fw-medium text-center">Qtà</th>
                                        <th scope="col" class="text-muted fw-medium text-end pe-3">Subtotale</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- STRUTTURA STATICA DA POPOLARE -->
                                    <?php foreach ($ordini_dettagli as $dettaglio): ?>
                                        <tr>
                                            <td class="ps-3 py-3">
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo htmlspecialchars($dettaglio['copertina']) ?>" alt="Copertina" class="rounded me-3 shadow-sm" style="object-fit: cover;height:80px">
                                                    <div>
                                                        <h6 class="fw-bold mb-1 text-secondary-color mb-0"><?php echo htmlspecialchars($dettaglio['titolo']) ?></h6>
                                                        <?php if ($dettaglio['formato'] == 'fisico'): ?>
                                                            <span class="badge bg-success small fw-medium">Cartaceo</span>
                                                        <?php else : ?>
                                                            <span class="badge bg-info small fw-medium">eBook</span>
                                                        <?php endif ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center text-muted fw-medium">€ <?php echo htmlspecialchars($dettaglio['prezzo_unitario']) ?></td>
                                            <td class="text-center"><span class="badge bg-light text-dark border px-2 py-1"><?php echo htmlspecialchars($dettaglio['quantita']) ?></span></td>
                                            <td class="text-end fw-bold text-primary pe-3">€ <?php echo htmlspecialchars(number_format($dettaglio['prezzo_unitario'] * $dettaglio['quantita'], 2, ',', '')) ?></td>
                                        </tr>
                                    <?php endforeach ?>
                                </tbody>
                                <tfoot class="border-top-0 bg-light">
                                    <tr>
                                        <td colspan="3" class="text-end fw-medium text-muted pt-3 pb-3 border-bottom-0">Totale Ordine:</td>
                                        <td class="text-end fw-bold text-primary fs-5 pt-3 pb-3 border-bottom-0 pe-3">€ <?php echo htmlspecialchars($_GET['totale']) ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        <?php endif ?>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                    <a href="<?php echo htmlspecialchars(strtok($_SERVER["REQUEST_URI"], '?')); ?>" class="btn btn-outline-secondary rounded-pill px-4">Chiudi</a>
                </div>
            </div>
        </div>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/footer.php'; ?>
</body>