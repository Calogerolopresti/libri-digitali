<?php
// importo collegamento al db e avvio la sessione se non è già avviata 
require_once __DIR__ . '/../config/db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// creo il csft token se non esiste gia in sessione 
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// controllo se l utente è con ruolo user o se ha mai fatto accesso e se non lo è lo butto fuori 
if (!isset($_SESSION['user_id']) || $_SESSION['ruolo'] !== 'admin') {
    header('Location:../index.php');
    exit();
}

// recupero tutti i clienti dal database
try {
    // scrivo la select per mostrarmi tutti gli utenti con ruolo user
    $sql = "SELECT id, nome, email FROM Utenti WHERE ruolo = 'user'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $clienti = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // in caso di errore inizzializzo un array vuota in modo tale da non mostrare errori a schermo
    $clienti = [];
    error_log("errore recupero clienti: " . $e->getMessage());
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

    <!-- Admin Content -->
    <main class="container-fluid px-4 mb-5 flex-grow-1 fade-in fade-in-delay-1 mt-5">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 border-bottom pb-4 gap-3 mt-3">
            <h2 class="fw-bold text-secondary-color mb-0">
                <i class="fa-solid fa-users text-primary me-2"></i> Gestione Clienti
            </h2>
        </div>

        <?php if (isset($_GET['successo_delete'])): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 d-flex align-items-center mb-4 px-4 py-3" role="alert">
                <i class="fa-solid fa-circle-check fs-4 me-3 text-success"></i>
                <div>
                    <strong>Eliminazione completata:</strong> Il cliente è stato eliminato con successo.
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['errore_delete'])): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 d-flex align-items-center mb-4 px-4 py-3" role="alert">
                <i class="fa-solid fa-triangle-exclamation fs-4 me-3 text-danger"></i>
                <div>
                    <strong>Errore di eliminazione:</strong> Si è verificato un problema durante la rimozione del cliente.
                </div>
            </div>
        <?php endif; ?>

        <!-- Tabella Clienti -->
        <?php if (empty($clienti) || !isset($clienti)): ?>
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4 bg-white">
                <div class="card-body">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                        <i class="fa-solid fa-user-slash text-muted" style="font-size: 2rem;"></i>
                    </div>
                    <h4 class="fw-bold text-secondary-color mb-2">Nessun cliente registrato</h4>
                    <p class="text-muted mb-0">Al momento non ci sono utenti con ruolo "user" nel database.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm rounded-4 p-2">
                <div class="card-body p-0">
                    <div class="table-responsive table-wrapper">
                        <table class="table table-hover align-middle mb-0 border-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-4 text-muted fw-bold rounded-start border-bottom-0 text-xs" style="text-transform: uppercase; letter-spacing: 0.5px; padding-top: 1rem; padding-bottom: 1rem;">ID</th>
                                    <th scope="col" class="text-muted fw-bold border-bottom-0 text-xs" style="text-transform: uppercase; letter-spacing: 0.5px; padding-top: 1rem; padding-bottom: 1rem;">Nome Completo</th>
                                    <th scope="col" class="text-muted fw-bold border-bottom-0 text-xs" style="text-transform: uppercase; letter-spacing: 0.5px; padding-top: 1rem; padding-bottom: 1rem;">Email</th>
                                    <th scope="col" class="text-center text-muted fw-bold border-bottom-0 text-xs" style="text-transform: uppercase; letter-spacing: 0.5px; padding-top: 1rem; padding-bottom: 1rem;">Ruolo</th>
                                    <th scope="col" class="text-center pe-4 text-muted fw-bold rounded-end border-bottom-0 text-xs" style="text-transform: uppercase; letter-spacing: 0.5px; padding-top: 1rem; padding-bottom: 1rem;">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                <!-- Riga Cliente -->
                                <?php foreach ($clienti as $cliente): ?>
                                    <tr style="transition: all 0.2s ease;">
                                        <td class="ps-4 py-4 fw-medium text-secondary">#<?php echo htmlspecialchars($cliente['id']) ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                                                    <i class="fa-regular fa-user"></i>
                                                </div>
                                                <h6 class="fw-bold mb-0 text-secondary-color">
                                                    <?php echo htmlspecialchars($cliente['nome']) ?>
                                                </h6>
                                            </div>
                                        </td>
                                        <td class="text-muted fw-medium"><i class="fa-regular fa-envelope me-2 text-primary opacity-75"></i> <?php echo htmlspecialchars($cliente['email']) ?></td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill bg-light text-secondary border px-3 py-2 fw-medium">
                                                Cliente
                                            </span>
                                        </td>
                                        <td class="text-center pe-4">
                                            <a href="dettagli_cliente.php?id=<?php echo htmlspecialchars($cliente['id']) ?>" class="btn btn-sm btn-light text-primary shadow-sm rounded-circle d-inline-flex align-items-center justify-content-center p-0" style="width: 38px; height: 38px; transition: all 0.2s;" title="Vedi Dettagli e Ordini" data-bs-toggle="tooltip">
                                                <i class="fa-regular fa-eye"></i>
                                            </a>
                                            <!-- pulsante elimina passato come form per evitare csrf -->
                                            <form method="POST" action="elimina_cliente.php" class="d-inline m-0 p-0" onsubmit="return confirm('Vuoi davvero eliminare questo cliente?')">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($cliente['id']) ?>">
                                                <button type="submit" class="btn btn-sm btn-light text-danger shadow-sm rounded-circle d-inline-flex align-items-center justify-content-center p-0 ms-1" style="width: 38px; height: 38px; transition: all 0.2s;" title="Elimina Cliente">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif ?>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
