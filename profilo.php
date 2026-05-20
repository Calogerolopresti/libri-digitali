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

// genero il token csrf se non esiste ancora nella sessione
if(!isset($_SESSION['csrf_token'])){
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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

// query per prendere quanti ordini ha fatto l utente e quanto ha speso negli ultimi 12 mesi
try {
    $sql_stats = "SELECT 
                    COUNT(*) AS totale_ordini,
                    COALESCE(SUM(totale_ordine), 0) AS totale_speso
                  FROM Ordini 
                  WHERE id_utente = ?
                  AND data_ordine >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
    $stmt_stats = $pdo->prepare($sql_stats);
    $stmt_stats->execute([$user_id]);
    $stats_annuali = $stmt_stats->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // in caso di errore mettiamo i valori a zero cosi non mostriamo niente di strano all utente
    $stats_annuali = ['totale_ordini' => 0, 'totale_speso' => 0];
}

// quando il cliente clicca sui dettagli di un singolo ordine facciamo la query tramite join per recuperare informazioni in piu come copertina formato ecc che non si trovano nella tabella dettagli ordini 
if (isset($_GET['id'])) {
    $dettaglio_ordine = (int)$_GET['id'];
    try {
        // aggiungo il controllo sull id utente cosi un utente non puo vedere gli ordini di un altro
        $sql = "SELECT 
                Prodotti.titolo as titolo, 
                Prodotti.copertina as copertina, 
                Prodotti.formato as formato, 
                Dettagli_Ordine.prezzo_unitario as prezzo_unitario, 
                Dettagli_Ordine.quantita as quantita
                FROM Dettagli_Ordine
                JOIN Prodotti ON Dettagli_Ordine.id_prodotto = Prodotti.id
                JOIN Ordini ON Dettagli_Ordine.id_ordine = Ordini.id
                WHERE Dettagli_Ordine.id_ordine = (?)
                AND Ordini.id_utente = (?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$dettaglio_ordine, $user_id]);
        $ordini_dettagli = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // anche qui in caso di errore restituiamo un array vuoto in modo tale da non far vedere errori all utente 
        $ordini_dettagli = [];
    }
}
// funzionalita per modificare nome e email 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // verifico il token csrf prima di fare qualsiasi altra cosa
    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']){
        header('Location:profilo.php?errore');
        exit();
    }

    $nuovo_nome = trim($_POST['nome']);
    // uso filter_var per validare l email in modo corretto
    $nuova_email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);

    if ($nuova_email === false || empty($nuovo_nome)) {
        header('Location:profilo.php?errore');
        exit();
    }

    try {
        // apro una transazione per evitare che due richieste simultanee usino la stessa email
        $pdo->beginTransaction();
        // controllo che l email non sia associata ad altri utenti
        $sql = "SELECT COUNT(*) AS controllo FROM Utenti WHERE email=? AND id!=? FOR UPDATE";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nuova_email, $_SESSION['user_id']]);
        $email_trovate = $stmt->fetchColumn();

        if ($email_trovate == 0) {
            $sql = "UPDATE Utenti SET email=?, nome=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nuova_email, $nuovo_nome, $_SESSION['user_id']]);
            $pdo->commit();
            $_SESSION['user_nome'] = $nuovo_nome;
            $_SESSION['user_email'] = $nuova_email;
            header('Location:profilo.php?successo');
            exit();
        } else {
            $pdo->rollBack();
            header('Location:profilo.php?email_esistente');
            exit();
        }
    } catch (PDOException $e) {
        // in caso di errore annullo tutto e mostro un messaggio generico
        if($pdo->inTransaction()) $pdo->rollBack();
        error_log("errore modifica profilo: " . $e->getMessage());
        header('Location:profilo.php?errore');
        exit();
    }
}
?>

<?php include __DIR__ . '/includes/head.php'; ?>

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
        from { opacity: 0; }
        to   { opacity: 1; }
    }
</style>

<body class="d-flex flex-column min-vh-100 fade-in pt-5 mt-4 bg-light">

    <?php include __DIR__ . '/includes/navbar_user.php'; ?>

    <main class="container mb-5 flex-grow-1 fade-in fade-in-delay-1 mt-5">
        <h2 class="fw-bold text-secondary-color mb-4">Ciao, <span class="text-primary"><?php echo htmlspecialchars($_SESSION['user_nome']) ?></span>!</h2>
        <?php if (isset($_GET['successo'])): ?>
            <div class="alert alert-success">
                <strong>Operazione completata!</strong> Le tue credenziali sono state aggiornate con successo.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['email_esistente'])): ?>
            <div class="alert alert-warning">
                <strong>Email non disponibile:</strong> L'indirizzo inserito è già associato a un altro account. Prova con uno diverso.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['errore'])): ?>
            <div class="alert alert-danger">
                <strong>Si è verificato un errore:</strong> Non è stato possibile aggiornare i dati. Riprova tra qualche minuto.
            </div>
        <?php endif; ?>
        <div class="row g-4">
            <!-- colonna sinistra: info utente e statistiche impilate -->
            <div class="col-lg-4 d-flex flex-column gap-4">

                <!-- Info Utente -->
                <div class="card border-0 shadow-sm rounded-4 p-2">
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

                <!-- Statistiche Ultimo Anno -->
                <div class="card border-0 shadow-sm rounded-4 p-2">
                    <div class="card-body p-4">
                        <!-- intestazione della card statistiche -->
                        <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <i class="fa-solid fa-chart-line"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0">Anno in Corso</h5>
                                <p class="text-muted small mb-0">Ultimi 12 mesi</p>
                            </div>
                        </div>
                        <!-- totale ordini nell ultimo anno -->
                        <div class="d-flex align-items-center justify-content-between mb-3 p-3 bg-light rounded-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-bag-shopping text-primary"></i>
                                <span class="text-muted small fw-medium">Ordini effettuati</span>
                            </div>
                            <span class="fw-bold fs-5 text-secondary-color">
                                <?php echo (int)$stats_annuali['totale_ordini'] ?>
                            </span>
                        </div>
                        <!-- totale speso nell ultimo anno -->
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
                        <!-- campo nascosto per la protezione csrf -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']) ?>">
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

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>