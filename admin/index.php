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
$libri = [];
include __DIR__ . '/../includes/select_prodotti.php';
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
                <i class="fa-solid fa-gauge-high text-primary me-2"></i> Gestione Catalogo
            </h2>
            
            <div class="d-flex align-items-center gap-3 flex-wrap flex-md-nowrap w-100 w-lg-auto justify-content-lg-end">
                <!-- form per la barra di ricerca in php senza usare js -->
                <form method="GET" action="index.php" class="d-flex flex-grow-1 flex-md-grow-0" style="max-width: 400px; min-width: 250px;">
                    <div class="input-group shadow-sm rounded-pill overflow-hidden border border-primary border-opacity-25 bg-white">
                        <span class="input-group-text border-0 bg-transparent ps-4 pe-2 text-primary">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" name="cerca" class="form-control border-0 px-2 bg-transparent shadow-none" placeholder="Cerca libro..." value="<?php echo isset($_GET['cerca']) ? htmlspecialchars($_GET['cerca']) : ''; ?>">
                        <button class="btn btn-primary border-0 px-4 fw-medium" type="submit">Cerca</button>
                    </div>
                </form>

                <button class="btn btn-primary shadow-sm rounded-pill px-4 flex-shrink-0" data-bs-toggle="modal" data-bs-target="#addProductModal" style="height: 42px;">
                    <i class="fa-solid fa-plus me-2"></i> Aggiungi Prodotto
                </button>
            </div>
        </div>

        <?php if (isset($_GET['errore_update'])): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 d-flex align-items-center mb-4 px-4 py-3" role="alert">
                <i class="fa-solid fa-triangle-exclamation fs-4 me-3 text-danger"></i>
                <div>
                    <strong>Errore di modifica:</strong> I dati non sono stati aggiornati correttamente. Riprova.
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['errore_insert'])): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 d-flex align-items-center mb-4 px-4 py-3" role="alert">
                <i class="fa-solid fa-circle-xmark fs-4 me-3 text-danger"></i>
                <div>
                    <strong>Errore di inserimento:</strong> Impossibile salvare i nuovi dati. Controlla i campi.
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['errore_delete'])): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 d-flex align-items-center mb-4 px-4 py-3" role="alert">
                <i class="fa-solid fa-trash fs-4 me-3 text-danger"></i>
                <div>
                    <strong>Errore di eliminazione:</strong> Si è verificato un problema durante la rimozione dei dati.
                </div>
            </div>
        <?php endif; ?>

        <!-- Tabella Prodotti -->
        <?php if (empty($libri) || !isset($libri)): ?>
            <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4 bg-white">
                <div class="card-body">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                        <i class="fa-solid fa-magnifying-glass text-muted" style="font-size: 2rem;"></i>
                    </div>
                    <h4 class="fw-bold text-secondary-color mb-2">Nessun libro trovato</h4>
                    <p class="text-muted mb-0">Non ci sono libri disponibili nel catalogo o corrispondenti alla tua ricerca.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm rounded-4 p-2">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 border-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-4 text-muted fw-bold rounded-start border-bottom-0" style="width: 90px; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px;">Copertina</th>
                                    <th scope="col" class="text-muted fw-bold border-bottom-0" style="text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px;">Titolo</th>
                                    <th scope="col" class="text-muted fw-bold border-bottom-0" style="text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px;">Prezzo</th>
                                    <th scope="col" class="text-muted fw-bold border-bottom-0" style="text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px;">Formato</th>
                                    <th scope="col" class="text-muted fw-bold text-center border-bottom-0" style="text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px;">Stock</th>
                                    <th scope="col" class="text-center pe-4 text-muted fw-bold rounded-end border-bottom-0" style="text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px;">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                <!-- Riga Prodotto -->
                                <?php foreach ($libri as $libro): ?>
                                    <tr style="transition: all 0.2s ease;">
                                        <td class="ps-4 py-3">
                                            <div class="position-relative overflow-hidden rounded-3 shadow-sm" style="width: 55px; height: 75px;">
                                                <img src="<?php echo htmlspecialchars($libro['copertina']) ?>" alt="Copertina" class="position-absolute w-100 h-100" style="object-fit: cover; top: 0; left: 0;">
                                            </div>
                                        </td>
                                        <td>
                                            <h6 class="fw-bold mb-1 text-secondary-color">
                                                <?php echo htmlspecialchars($libro['titolo']) ?>
                                            </h6>
                                        </td>
                                        <td class="fw-bold text-primary">€ <?php echo htmlspecialchars($libro['prezzo']) ?></td>
                                        <!-- mostro la tipoligia in base al formato  -->
                                        <td>
                                            <?php if ($libro['formato'] == 'fisico'): ?>
                                                <span class="badge rounded-pill d-inline-flex align-items-center gap-2"
                                                    style="background-color: #a31d1d; color: white; padding: 6px 14px; font-size: 0.75rem; border: 1px solid #7a0c0c;">
                                                    <i class="bi bi-book-fill"></i> CARTACEO
                                                </span>
                                            <?php else: ?>
                                                <span class="badge rounded-pill d-inline-flex align-items-center gap-2"
                                                    style="background-color: #fdf2f2; color: #a31d1d; padding: 6px 14px; font-size: 0.75rem; border: 1px solid #f2d7d7;">
                                                    <i class="bi bi-phone-vibrate"></i> E-BOOK
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <!-- mostro la disponibilità in base al formato  -->
                                        <?php if ($libro['formato'] == 'fisico'): ?>
                                            <td class="text-center"><span
                                                    class="badge bg-secondary small fw-medium"><?php echo htmlspecialchars($libro['disponibilita']) ?>
                                                    pz</span></td>
                                        <?php else: ?>
                                            <td class="text-center"><span
                                                    class="badge bg-secondary small fw-medium">∞</span></td>
                                        <?php endif ?>

                                        <td class="text-center pe-4">
                                            <!-- Pulsante Modifica -->
                                            <button
                                                type="button"
                                                class="btn-edit btn btn-sm btn-light text-primary shadow-sm rounded-circle d-inline-flex align-items-center justify-content-center p-0 me-1"
                                                data-id="<?= $libro['id'] ?>"
                                                data-titolo="<?= htmlspecialchars($libro['titolo']) ?>"
                                                data-prezzo="<?= htmlspecialchars($libro['prezzo']) ?>"
                                                data-formato="<?= htmlspecialchars($libro['formato']) ?>"
                                                data-disponibilita="<?= htmlspecialchars($libro['disponibilita']) ?>"
                                                data-copertina="<?= htmlspecialchars($libro['copertina']) ?>"
                                                data-descrizione="<?= htmlspecialchars($libro['descrizione']) ?>"
                                                style="width: 38px; height: 38px; transition: all 0.2s;"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editProductModal" title="Modifica">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>

                                            <!-- pulsante elimina passato come form per evitare csrf -->
                                            <form method="POST" action="elimina.php" class="d-inline m-0 p-0" onsubmit="return confirm('Vuoi davvero eliminare questo libro?')">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($libro['id']) ?>">
                                                <button type="submit" class="btn btn-sm btn-light text-danger shadow-sm rounded-circle d-inline-flex align-items-center justify-content-center p-0" style="width: 38px; height: 38px; transition: all 0.2s;" title="Elimina">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- <div class="d-flex justify-content-between align-items-center mt-3 px-4 py-3 border-top">
                    <span class="text-muted small">Mostrando 1-3 di 45 prodotti</span>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled"><a class="page-link border-0 text-muted" href="#"><i
                                        class="fa-solid fa-chevron-left"></i></a></li>
                            <li class="page-item active"><a class="page-link border-0 rounded bg-primary" href="#">1</a>
                            </li>
                            <li class="page-item"><a class="page-link border-0 text-muted" href="#">2</a></li>
                            <li class="page-item"><a class="page-link border-0 text-muted" href="#">3</a></li>
                            <li class="page-item"><a class="page-link border-0 text-muted" href="#"><i
                                        class="fa-solid fa-chevron-right"></i></a></li>
                        </ul>
                    </nav>
                </div> -->
                </div>
            </div>
        <?php endif ?>
    </main>

    <!-- Modal Aggiungi Prodotto -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <h4 class="modal-title fw-bold text-secondary-color" id="addProductModalLabel">Nuovo Prodotto</h4>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="aggiungi.php" enctype="multipart/form-data">
                        <div class="row g-4">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <!-- Titolo -->
                            <div class="col-md-8">
                                <label for="titolo_add" class="form-label fw-bold text-secondary-color small mb-1">Titolo Libro *</label>
                                <input type="text" class="form-control bg-light border-0 px-3 py-2 rounded-3 shadow-none" id="titolo_add" name="titolo"
                                    placeholder="Inserisci il titolo" required>
                            </div>
                            <!-- Prezzo -->
                            <div class="col-md-4">
                                <label for="prezzo_add" class="form-label fw-bold text-secondary-color small mb-1">Prezzo (€) *</label>
                                <div class="input-group-custom m-0 bg-light border-0 rounded-3 overflow-hidden d-flex align-items-center px-3">
                                    <i class="fa-solid fa-euro-sign text-muted me-2" style="font-size: 0.95rem;"></i>
                                    <input type="number" step="0.01" class="form-control bg-transparent border-0 shadow-none px-1 py-2" id="prezzo_add" name="prezzo"
                                        placeholder="0.00" required>
                                </div>
                            </div>
                            <!-- Formato -->
                            <div class="col-md-4">
                                <label for="formato_add" class="form-label fw-bold text-secondary-color small mb-1">Formato *</label>
                                <select class="form-select bg-light border-0 px-3 py-2 rounded-3 shadow-none text-secondary" id="formato_add" name="formato" required>
                                    <option value="" selected disabled>Seleziona formato...</option>
                                    <option value="fisico">Libro Cartaceo (Fisico)</option>
                                    <option value="digitale">Edizione Digitale (eBook)</option>
                                </select>
                            </div>
                            <!-- Quantità -->
                            <div class="col-md-3">
                                <label for="quantita_add" class="form-label fw-bold text-secondary-color small mb-1">Stock</label>
                                <input type="number" class="form-control bg-light border-0 px-3 py-2 rounded-3 shadow-none" id="quantita_add" name="quantita"
                                    placeholder="es. 10" min="0" required>
                            </div>
                            <!-- Copertina -->
                            <div class="col-md-5">
                                <label for="copertina_add" class="form-label fw-bold text-secondary-color small mb-1">Link Copertina</label>
                                <div class="input-group-custom m-0 bg-light border-0 rounded-3 overflow-hidden d-flex align-items-center px-3">
                                    <i class="fa-solid fa-link text-muted me-2"></i>
                                    <input type="url" class="form-control bg-transparent border-0 shadow-none px-1 py-2" id="copertina_add" name="copertina"
                                        placeholder="https://...img.jpg" required>
                                </div>
                            </div>
                            <!-- Descrizione -->
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label for="descrizione_add" class="form-label fw-bold text-secondary-color small mb-0">Descrizione Estesa</label>
                                    <button type="button" id="btnGeneraDescrizione"
                                        class="btn btn-sm rounded-pill px-3 fw-medium"
                                        style="background: linear-gradient(135deg, #a31d1d, #c0392b); color: white; font-size: 0.75rem; border: none;"
                                        title="Genera automaticamente la descrizione tramite IA">
                                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Genera con IA
                                    </button>
                                </div>
                                <div style="position: relative;">
                                    <textarea class="form-control bg-light border-0 px-3 py-2 rounded-3 shadow-none" id="descrizione_add" name="descrizione" rows="4"
                                        placeholder="Inserisci la sinossi o i dettagli del libro..." required></textarea>
                                    <div id="descrizione-loader" class="d-none" style="position: absolute; inset: 0; background: rgba(255,255,255,0.85); border-radius: 8px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                        <div class="spinner-border spinner-border-sm text-danger" role="status"></div>
                                        <span class="text-muted small">Leo sta scrivendo...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-3 mt-5 pt-3 border-top">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                                data-bs-dismiss="modal">Annulla</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                <i class="fa-solid fa-plus me-2"></i> Crea Prodotto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Modifica Prodotto -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <h4 class="modal-title fw-bold text-secondary-color" id="editProductModalLabel">Modifica Prodotto
                    </h4>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="modifica.php" enctype="multipart/form-data">
                        <!-- input hidden per l'ID del prodotto da modificare -->
                        <input type="hidden" name="id_prodotto" id="id_prodotto_edit" value="1">

                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                        <div class="row g-4">
                            <!-- Titolo -->
                            <div class="col-md-8">
                                <label for="titolo_edit" class="form-label fw-bold text-secondary-color small mb-1">Titolo Libro *</label>
                                <input type="text" class="form-control bg-light border-0 px-3 py-2 rounded-3 shadow-none" id="titolo_edit" name="titolo"
                                    placeholder="Inserisci il titolo" value="Il Nome della Rosa" required>
                            </div>
                            <!-- Prezzo -->
                            <div class="col-md-4">
                                <label for="prezzo_edit" class="form-label fw-bold text-secondary-color small mb-1">Prezzo (€) *</label>
                                <div class="input-group-custom m-0 bg-light border-0 rounded-3 overflow-hidden d-flex align-items-center px-3">
                                    <i class="fa-solid fa-euro-sign text-muted me-2" style="font-size: 0.95rem;"></i>
                                    <input type="number" step="0.01" class="form-control bg-transparent border-0 shadow-none px-1 py-2" id="prezzo_edit" name="prezzo"
                                        placeholder="0.00" value="14.50" required>
                                </div>
                            </div>
                            <!-- Formato -->
                            <div class="col-md-4">
                                <label for="formato_edit" class="form-label fw-bold text-secondary-color small mb-1">Formato *</label>
                                <select class="form-select bg-light border-0 px-3 py-2 rounded-3 shadow-none text-secondary" id="formato_edit" name="formato" required>
                                    <option value="" disabled>Seleziona formato...</option>
                                    <option value="fisico" selected>Libro Cartaceo (Fisico)</option>
                                    <option value="digitale">Edizione Digitale (eBook)</option>
                                </select>
                            </div>
                            <!-- Quantità -->
                            <div class="col-md-3">
                                <label for="quantita_edit" class="form-label fw-bold text-secondary-color small mb-1">Stock</label>
                                <input type="number" class="form-control bg-light border-0 px-3 py-2 rounded-3 shadow-none" id="quantita_edit" name="quantita" value="15"
                                    min="0" required>
                            </div>
                            <!-- Copertina -->
                            <div class="col-md-5">
                                <label for="copertina_edit" class="form-label fw-bold text-secondary-color small mb-1">Link Copertina</label>
                                <div class="input-group-custom m-0 bg-light border-0 rounded-3 overflow-hidden d-flex align-items-center px-3">
                                    <i class="fa-solid fa-link text-muted me-2"></i>
                                    <input type="url" class="form-control bg-transparent border-0 shadow-none px-1 py-2" id="copertina_edit" name="copertina"
                                        placeholder="https://...img.jpg" value="https://images.unsplash.com/photo-1481627834876-b7833e8f5570" required>
                                </div>
                            </div>
                            <!-- Descrizione -->
                            <div class="col-12">
                                <label for="descrizione_edit" class="form-label fw-bold text-secondary-color small mb-1">Descrizione Estesa</label>
                                <textarea class="form-control bg-light border-0 px-3 py-2 rounded-3 shadow-none" id="descrizione_edit" name="descrizione" rows="4"
                                    placeholder="Inserisci la sinossi o i dettagli del libro..."
                                    required>Un celebre romanzo di Umberto Eco ambientato nel Medioevo...</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3 mt-5 pt-3 border-top">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4"
                                data-bs-dismiss="modal">Annulla</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                <i class="fa-solid fa-floppy-disk me-2"></i> Aggiorna DB
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /**
             * LOGICA MODAL AGGIUNGI
             * Gestisce l'interazione tra formato e quantità nel form di inserimento
             */
            const formatoAdd = document.getElementById('formato_add');
            const quantitaAdd = document.getElementById('quantita_add');

            if (formatoAdd && quantitaAdd) {
                formatoAdd.addEventListener('change', function() {
                    if (this.value === 'digitale' || this.value === 'ebook') {
                        quantitaAdd.value = 0;
                        quantitaAdd.placeholder = '∞';
                        quantitaAdd.readOnly = true; // Impedisce l'inserimento
                        quantitaAdd.style.backgroundColor = '#e9ecef'; // Feedback visivo (colore Bootstrap disabled)
                    } else {
                        quantitaAdd.value = '';
                        quantitaAdd.placeholder = 'es. 10';
                        quantitaAdd.readOnly = false;
                        quantitaAdd.style.backgroundColor = '';
                    }
                });
            }

            /**
             * LOGICA MODAL MODIFICA
             * Popola la modale al click e gestisce i cambi di formato in tempo reale
             */
            const editButtons = document.querySelectorAll('.btn-edit');
            const formatoEdit = document.getElementById('formato_edit');
            const quantitaEdit = document.getElementById('quantita_edit');

            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Estrazione dati dai data-attributes del bottone cliccato
                    const id = this.dataset.id;
                    const titolo = this.dataset.titolo;
                    const prezzo = this.dataset.prezzo;
                    const copertina = this.dataset.copertina;
                    const formatoVal = this.dataset.formato; // valore dal DB
                    const descrizione = this.dataset.descrizione;
                    const disponibilita = this.dataset.disponibilita;

                    // Riempimento campi testuali e hidden
                    document.getElementById('id_prodotto_edit').value = id;
                    document.getElementById('titolo_edit').value = titolo;
                    document.getElementById('prezzo_edit').value = prezzo;
                    document.getElementById('copertina_edit').value = copertina;
                    document.getElementById('descrizione_edit').value = descrizione;

                    // Logica specifica per Formato e Quantità all'apertura
                    // Gestiamo il "ponte" tra i possibili valori 'digitale'/'ebook'
                    if (formatoVal === 'digitale' || formatoVal === 'ebook') {
                        formatoEdit.value = 'digitale'; // Seleziona l'option corretta nella tua select
                        quantitaEdit.value = 0;
                        quantitaEdit.placeholder = '∞';
                        quantitaEdit.readOnly = true;
                        quantitaEdit.style.backgroundColor = '#e9ecef';
                    } else {
                        formatoEdit.value = 'fisico';
                        quantitaEdit.value = disponibilita;
                        quantitaEdit.placeholder = 'es. 10';
                        quantitaEdit.readOnly = false;
                        quantitaEdit.style.backgroundColor = '';
                    }

                    // Memorizziamo la disponibilità originale nel caso l'utente cambi formato e poi torni indietro
                    quantitaEdit.dataset.originalValue = disponibilita;
                });
            });

            /**
             * GESTIONE CAMBIO FORMATO DENTRO LA MODALE MODIFICA
             * Se l'utente cambia il select mentre modifica, la quantità deve reagire
             */
            if (formatoEdit && quantitaEdit) {
                formatoEdit.addEventListener('change', function() {
                    if (this.value === 'ebook' || this.value === 'digitale') {
                        quantitaEdit.value = 0;
                        quantitaEdit.placeholder = '∞';
                        quantitaEdit.readOnly = true;
                        quantitaEdit.style.backgroundColor = '#e9ecef';
                    } else {
                        // Ripristina il valore originale del libro se disponibile, altrimenti lascia vuoto
                        quantitaEdit.value = quantitaEdit.dataset.originalValue || '';
                        quantitaEdit.placeholder = 'es. 10';
                        quantitaEdit.readOnly = false;
                        quantitaEdit.style.backgroundColor = '';
                    }
                });
            }

            // gestiamo il click sul pulsante per far autogenerare la descrizione commerciale all ia
            const btnGenera = document.getElementById('btnGeneraDescrizione');
            if (btnGenera) {
                btnGenera.addEventListener('click', function() {
                    const titolo  = document.getElementById('titolo_add').value.trim();
                    const formato = document.getElementById('formato_add').value;

                    // se l utente non ha inserito il titolo lo avvisiamo altrimenti l ia non sa di che libro parlare
                    if (!titolo) {
                        alert('Inserisci prima il titolo del libro per generare la descrizione.');
                        document.getElementById('titolo_add').focus();
                        return;
                    }

                    // facciamo apparire la schermata di caricamento sopra l area di testo e disabilitiamo il pulsante
                    const loader  = document.getElementById('descrizione-loader');
                    const textarea = document.getElementById('descrizione_add');
                    loader.classList.remove('d-none');
                    loader.style.display = 'flex';
                    btnGenera.disabled = true;

                    // creiamo i dati del form assemblando titolo e formato e chiamiamo genera_descrizione.php
                    const formData = new FormData();
                    formData.append('titolo', titolo);
                    formData.append('formato', formato);

                    fetch('genera_descrizione.php', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.descrizione) {
                                // se la descrizione esiste la buttiamo dentro la textarea e facciamo un effetto flash colorato per far capire che è cambiata
                                textarea.value = data.descrizione;
                                textarea.style.transition = 'background 0.4s';
                                textarea.style.background = '#fdf2f2';
                                setTimeout(() => { textarea.style.background = ''; }, 1000);
                            } else {
                                // se l ia ha fallito avvisiamo con un alert
                                alert('Errore IA: ' + (data.errore || 'Risposta non valida.'));
                            }
                        })
                        .catch(() => alert('Errore di connessione. Riprova.'))
                        .finally(() => {
                            // in ogni caso togliamo la schermata di caricamento e riabilitiamo il pulsante
                            loader.classList.add('d-none');
                            loader.style.display = 'none';
                            btnGenera.disabled = false;
                        });
                });
            }

        }); // fine DOMContentLoaded
    </script>

    <?php include __DIR__ . '/../includes/footer.php'; ?>