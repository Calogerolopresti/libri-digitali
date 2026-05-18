<?php
// importo collegamento al db e avvio la sessione se non è già avviata 
require_once __DIR__ . '/../config/db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// creo il csft token se non esiste gia in sessione 
if(!isset($_SESSION['csfr_token'])){
    $_SESSION['csfr_token']= bin2hex(random_bytes(32));
}

// controllo se l utente è con ruolo user o se ha mai fatto accesso e se non lo è lo butto fuori 
if (!isset($_SESSION['user_id']) || $_SESSION['ruolo'] !== 'admin') {
    header('Location:../index.php');
    exit();
}
$libri = [];
include '../includes/select_prodotti.php';
?>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/head.php'; ?>

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

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/navbar_admin.php'; ?>

    <!-- Admin Content -->
    <main class="container-fluid px-4 mb-5 flex-grow-1 fade-in fade-in-delay-1 mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <h2 class="fw-bold text-secondary-color mb-0">
                <i class="fa-solid fa-gauge-high text-primary me-2"></i> Gestione Catalogo
            </h2>
            <button class="btn btn-primary shadow-sm rounded-pill px-4" data-bs-toggle="modal"
                data-bs-target="#addProductModal">
                <i class="fa-solid fa-plus me-2"></i> Aggiungi Prodotto
            </button>
        </div>

        <?php if(isset($_GET['errore_update'])):?>
            <p>errore durante inserimento dei dati</p>
        <?php endif?>

        <!-- Tabella Prodotti -->
        <div class="card border-0 shadow-sm rounded-4 p-2">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-4 text-muted fw-medium rounded-start" style="width: 80px;">
                                    Copertina</th>
                                <th scope="col" class="text-muted fw-medium">Titolo e Autore</th>
                                <th scope="col" class="text-muted fw-medium">Prezzo</th>
                                <th scope="col" class="text-muted fw-medium">Formato</th>
                                <th scope="col" class="text-muted fw-medium text-center">Stock</th>
                                <th scope="col" class="text-center pe-4 text-muted fw-medium rounded-end">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <!-- Riga 1 -->
                            <?php foreach ($libri as $libro): ?>
                                <tr>
                                    <td class="ps-4 py-3">
                                        <img src="<?php echo htmlspecialchars($libro['copertina']) ?>" alt="Copertina"
                                            class="rounded shadow-sm" style="object-fit: cover; height:100px">
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
                                        <!-- Pulsante Modifica: rimosso ID fisso, aggiunto echo tramite  -->
                                        <button
                                            type="button"
                                            class="btn-edit btn btn-sm btn-outline-secondary border-0 rounded-circle"
                                            data-id="<?= $libro['id'] ?>"
                                            data-titolo="<?= htmlspecialchars($libro['titolo']) ?>"
                                            data-prezzo="<?= htmlspecialchars($libro['prezzo']) ?>"
                                            data-formato="<?= htmlspecialchars($libro['formato']) ?>"
                                            data-disponibilita="<?= htmlspecialchars($libro['disponibilita']) ?>"
                                            data-copertina="<?= htmlspecialchars($libro['copertina']) ?>"
                                            data-descrizione="<?= htmlspecialchars($libro['descrizione']) ?>"
                                            style="width: 35px; height: 35px;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editProductModal">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>

                                        <!-- Pulsante Elimina: corretto echo e uniformata variabile -->
                                        <a href="elimina.php?id=<?= htmlspecialchars($libro['id']) ?>"
                                            class="btn btn-sm btn-outline-danger border-0 rounded-circle d-inline-flex align-items-center justify-content-center p-0"
                                            style="width: 35px; height: 35px;"
                                            title="Elimina"
                                            onclick="return confirm('Vuoi davvero eliminare questo libro?')">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 px-4 py-3 border-top">
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
                </div>
            </div>
        </div>
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
                            <input type="hidden" name="csfr_token" value="<?=htmlspecialchars($_SESSION['csfr_token'])?>">
                            <!-- Titolo -->
                            <div class="col-md-8">
                                <label for="titolo_add" class="form-label fw-medium text-muted small">Titolo Libro
                                    *</label>
                                <input type="text" class="form-control" id="titolo_add" name="titolo"
                                    placeholder="Inserisci il titolo" required>
                            </div>
                            <!-- Prezzo -->
                            <div class="col-md-4">
                                <label for="prezzo_add" class="form-label fw-medium text-muted small">Prezzo (€)
                                    *</label>
                                <div class="input-group-custom m-0">
                                    <i class="fa-solid fa-euro-sign input-icon" style="font-size: 0.95rem;"></i>
                                    <input type="number" step="0.01" class="form-control" id="prezzo_add" name="prezzo"
                                        placeholder="0.00" required>
                                </div>
                            </div>
                            <!-- Formato -->
                            <div class="col-md-4">
                                <label for="formato_add" class="form-label fw-medium text-muted small">Formato *</label>
                                <select class="form-select form-control" id="formato_add" name="formato" required>
                                    <option value="" selected disabled>Seleziona formato...</option>
                                    <option value="fisico">Libro Cartaceo (Fisico)</option>
                                    <option value="digitale">Edizione Digitale (eBook)</option>
                                </select>
                            </div>
                            <!-- Quantità -->
                            <div class="col-md-3">
                                <label for="quantita_add" class="form-label fw-medium text-muted small">Qtà
                                    (Stock)</label>
                                <input type="number" class="form-control" id="quantita_add" name="quantita"
                                    placeholder="es. 10" min="0" required>
                            </div>
                            <!-- Copertina -->
                            <div class="col-md-5">
                                <label for="copertina_add" class="form-label fw-medium text-muted small">Link Immagine
                                    Copertina</label>
                                <div class="input-group-custom m-0">
                                    <i class="fa-solid fa-link input-icon"></i>
                                    <input type="url" class="form-control" id="copertina_add" name="copertina"
                                        placeholder="https://esempio.com/img.jpg" required>
                                </div>
                            </div>
                            <!-- Descrizione -->
                            <div class="col-12">
                                <label for="descrizione_add" class="form-label fw-medium text-muted small">Descrizione
                                    Estesa</label>
                                <textarea class="form-control" id="descrizione_add" name="descrizione" rows="4"
                                    placeholder="Inserisci la sinossi o i dettagli del libro..." required></textarea>
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

                        <input type="hidden" name="csfr_token" value="<?=htmlspecialchars($_SESSION['csfr_token'])?>">

                        <div class="row g-4">
                            <!-- Titolo -->
                            <div class="col-md-8">
                                <label for="titolo_edit" class="form-label fw-medium text-muted small">Titolo Libro
                                    *</label>
                                <input type="text" class="form-control" id="titolo_edit" name="titolo"
                                    placeholder="Inserisci il titolo" value="Il Nome della Rosa" required>
                            </div>
                            <!-- Prezzo -->
                            <div class="col-md-4">
                                <label for="prezzo_edit" class="form-label fw-medium text-muted small">Prezzo (€)
                                    *</label>
                                <div class="input-group-custom m-0">
                                    <i class="fa-solid fa-euro-sign input-icon" style="font-size: 0.95rem;"></i>
                                    <input type="number" step="0.01" class="form-control" id="prezzo_edit" name="prezzo"
                                        placeholder="0.00" value="14.50" required>
                                </div>
                            </div>
                            <!-- Formato -->
                            <div class="col-md-4">
                                <label for="formato_edit" class="form-label fw-medium text-muted small">Formato
                                    *</label>
                                <select class="form-select form-control" id="formato_edit" name="formato" required>
                                    <option value="" disabled>Seleziona formato...</option>
                                    <option value="fisico" selected>Libro Cartaceo (Fisico)</option>
                                    <option value="digitale">Edizione Digitale (eBook)</option>
                                </select>
                            </div>
                            <!-- Quantità -->
                            <div class="col-md-3">
                                <label for="quantita_edit" class="form-label fw-medium text-muted small">Qtà
                                    (Stock)</label>
                                <input type="number" class="form-control" id="quantita_edit" name="quantita" value="15"
                                    min="0" required>
                            </div>
                            <!-- Copertina -->
                            <div class="col-md-5">
                                <label for="copertina_edit" class="form-label fw-medium text-muted small">Link Immagine
                                    Copertina</label>
                                <div class="input-group-custom m-0">
                                    <i class="fa-solid fa-link input-icon"></i>
                                    <input type="url" class="form-control" id="copertina_edit" name="copertina"
                                        placeholder="https://esempio.com/img.jpg"
                                        value="https://images.unsplash.com/photo-1481627834876-b7833e8f5570" required>
                                </div>
                            </div>
                            <!-- Descrizione -->
                            <div class="col-12">
                                <label for="descrizione_edit" class="form-label fw-medium text-muted small">Descrizione
                                    Estesa</label>
                                <textarea class="form-control" id="descrizione_edit" name="descrizione" rows="4"
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
        });
    </script>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/footer.php'; ?>