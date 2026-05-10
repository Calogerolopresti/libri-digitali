<?php
// importo collegamento al db e avvio la sessione se non è già avviata 
require_once __DIR__ . '/../config/db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// controllo se l utente è con ruolo user o se ha mai fatto accesso e se non lo è lo butto fuori 
if(!isset($_SESSION['user_id']) || $_SESSION['ruolo']!=='admin'){
    header('Location:../index.php');
    exit();
}
?>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/head.php'; ?>
<body class="d-flex flex-column min-vh-100 fade-in pt-5 mt-4 bg-light">

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/navbar_admin.php'; ?>

<!-- Admin Content -->
    <main class="container-fluid px-4 mb-5 flex-grow-1 fade-in fade-in-delay-1 mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <h2 class="fw-bold text-secondary-color mb-0">
                <i class="fa-solid fa-gauge-high text-primary me-2"></i> Gestione Catalogo
            </h2>
            <button class="btn btn-primary shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="fa-solid fa-plus me-2"></i> Aggiungi Prodotto
            </button>
        </div>
        
        <!-- Tabella Prodotti -->
        <div class="card border-0 shadow-sm rounded-4 p-2">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-4 text-muted fw-medium rounded-start" style="width: 80px;">Copertina</th>
                                <th scope="col" class="text-muted fw-medium">Titolo e Autore</th>
                                <th scope="col" class="text-muted fw-medium">Prezzo</th>
                                <th scope="col" class="text-muted fw-medium">Formato</th>
                                <th scope="col" class="text-muted fw-medium text-center">Stock</th>
                                <th scope="col" class="text-center pe-4 text-muted fw-medium rounded-end">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <!-- Riga 1 -->
                            <tr>
                                <td class="ps-4 py-3">
                                    <img src="https://via.placeholder.com/40x60/f8f9fa/a52a2a?text=IMG" alt="Copertina" class="rounded shadow-sm" style="object-fit: cover;">
                                </td>
                                <td>
                                    <h6 class="fw-bold mb-1 text-secondary-color">Il Nome della Rosa</h6>
                                    <span class="text-muted small">Umberto Eco</span>
                                </td>
                                <td class="fw-bold text-primary">€ 14.50</td>
                                <td><span class="badge bg-success small fw-medium">Cartaceo</span></td>
                                <td class="text-center"><span class="badge bg-secondary small fw-medium">15 pz</span></td>
                                <td class="text-center pe-4">
                                    <button class="btn btn-sm btn-outline-secondary border-0 rounded-circle me-1 d-inline-flex align-items-center justify-content-center p-0" style="width: 35px; height: 35px;" title="Modifica" data-bs-toggle="modal" data-bs-target="#editProductModal"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn btn-sm btn-outline-danger border-0 rounded-circle d-inline-flex align-items-center justify-content-center p-0" style="width: 35px; height: 35px;" title="Elimina"><i class="fa-solid fa-trash-can"></i></button>
                                </td>
                            </tr>
                            
                            <!-- Riga 2 -->
                            <tr>
                                <td class="ps-4 py-3">
                                    <img src="https://via.placeholder.com/40x60/f8f9fa/a52a2a?text=IMG" alt="Copertina" class="rounded shadow-sm" style="object-fit: cover;">
                                </td>
                                <td>
                                    <h6 class="fw-bold mb-1 text-secondary-color">1984</h6>
                                    <span class="text-muted small">George Orwell</span>
                                </td>
                                <td class="fw-bold text-primary">€ 8.99</td>
                                <td><span class="badge bg-info small fw-medium">eBook</span></td>
                                <td class="text-center"><span class="badge bg-light text-dark border small fw-medium">&infin;</span></td>
                                <td class="text-center pe-4">
                                    <button class="btn btn-sm btn-outline-secondary border-0 rounded-circle me-1 d-inline-flex align-items-center justify-content-center p-0" style="width: 35px; height: 35px;" title="Modifica" data-bs-toggle="modal" data-bs-target="#editProductModal"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn btn-sm btn-outline-danger border-0 rounded-circle d-inline-flex align-items-center justify-content-center p-0" style="width: 35px; height: 35px;" title="Elimina"><i class="fa-solid fa-trash-can"></i></button>
                                </td>
                            </tr>
                            
                            <!-- Riga 3 -->
                            <tr>
                                <td class="ps-4 py-3">
                                    <img src="https://via.placeholder.com/40x60/f8f9fa/a52a2a?text=IMG" alt="Copertina" class="rounded shadow-sm" style="object-fit: cover;">
                                </td>
                                <td>
                                    <h6 class="fw-bold mb-1 text-secondary-color">Il Signore degli Anelli</h6>
                                    <span class="text-muted small">J.R.R. Tolkien</span>
                                </td>
                                <td class="fw-bold text-primary">€ 25.00</td>
                                <td><span class="badge bg-success small fw-medium">Cartaceo</span></td>
                                <td class="text-center"><span class="badge bg-secondary small fw-medium">3 pz</span></td>
                                <td class="text-center pe-4">
                                    <button class="btn btn-sm btn-outline-secondary border-0 rounded-circle me-1 d-inline-flex align-items-center justify-content-center p-0" style="width: 35px; height: 35px;" title="Modifica" data-bs-toggle="modal" data-bs-target="#editProductModal"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn btn-sm btn-outline-danger border-0 rounded-circle d-inline-flex align-items-center justify-content-center p-0" style="width: 35px; height: 35px;" title="Elimina"><i class="fa-solid fa-trash-can"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginazione -->
                <div class="d-flex justify-content-between align-items-center mt-3 px-4 py-3 border-top">
                    <span class="text-muted small">Mostrando 1-3 di 45 prodotti</span>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled"><a class="page-link border-0 text-muted" href="#"><i class="fa-solid fa-chevron-left"></i></a></li>
                            <li class="page-item active"><a class="page-link border-0 rounded bg-primary" href="#">1</a></li>
                            <li class="page-item"><a class="page-link border-0 text-muted" href="#">2</a></li>
                            <li class="page-item"><a class="page-link border-0 text-muted" href="#">3</a></li>
                            <li class="page-item"><a class="page-link border-0 text-muted" href="#"><i class="fa-solid fa-chevron-right"></i></a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Aggiungi Prodotto -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <h4 class="modal-title fw-bold text-secondary-color" id="addProductModalLabel">Nuovo Prodotto</h4>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="aggiungi.php" enctype="multipart/form-data">
                        <div class="row g-4">
                            <!-- Titolo -->
                            <div class="col-md-8">
                                <label for="titolo_add" class="form-label fw-medium text-muted small">Titolo Libro *</label>
                                <input type="text" class="form-control" id="titolo_add" name="titolo" placeholder="Inserisci il titolo" required>
                            </div>
                            <!-- Prezzo -->
                            <div class="col-md-4">
                                <label for="prezzo_add" class="form-label fw-medium text-muted small">Prezzo (€) *</label>
                                <div class="input-group-custom m-0">
                                    <i class="fa-solid fa-euro-sign input-icon" style="font-size: 0.95rem;"></i>
                                    <input type="number" step="0.01" class="form-control" id="prezzo_add" name="prezzo" placeholder="0.00" required>
                                </div>
                            </div>
                            <!-- Formato -->
                            <div class="col-md-4">
                                <label for="formato_add" class="form-label fw-medium text-muted small">Formato *</label>
                                <select class="form-select form-control" id="formato_add" name="formato" required>
                                    <option value="" selected disabled>Seleziona formato...</option>
                                    <option value="fisico">Libro Cartaceo (Fisico)</option>
                                    <option value="ebook">Edizione Digitale (eBook)</option>
                                </select>
                            </div>
                            <!-- Quantità -->
                            <div class="col-md-3">
                                <label for="quantita_add" class="form-label fw-medium text-muted small">Qtà (Stock)</label>
                                <input type="number" class="form-control" id="quantita_add" name="quantita" placeholder="es. 10" min="0">
                            </div>
                            <!-- Copertina -->
                            <div class="col-md-5">
                                <label for="copertina_add" class="form-label fw-medium text-muted small">Link Immagine Copertina</label>
                                <div class="input-group-custom m-0">
                                    <i class="fa-solid fa-link input-icon"></i>
                                    <input type="url" class="form-control" id="copertina_add" name="copertina" placeholder="https://esempio.com/img.jpg">
                                </div>
                            </div>
                            <!-- Descrizione -->
                            <div class="col-12">
                                <label for="descrizione_add" class="form-label fw-medium text-muted small">Descrizione Estesa</label>
                                <textarea class="form-control" id="descrizione_add" name="descrizione" rows="4" placeholder="Inserisci la sinossi o i dettagli del libro..." required></textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-3 mt-5 pt-3 border-top">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Annulla</button>
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
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <h4 class="modal-title fw-bold text-secondary-color" id="editProductModalLabel">Modifica Prodotto</h4>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="modifica.php" enctype="multipart/form-data">
                        <!-- Input hidden per l'ID del prodotto da modificare -->
                        <input type="hidden" name="id_prodotto" id="id_prodotto_edit" value="1">
                        
                        <div class="row g-4">
                            <!-- Titolo -->
                            <div class="col-md-8">
                                <label for="titolo_edit" class="form-label fw-medium text-muted small">Titolo Libro *</label>
                                <input type="text" class="form-control" id="titolo_edit" name="titolo" placeholder="Inserisci il titolo" value="Il Nome della Rosa" required>
                            </div>
                            <!-- Prezzo -->
                            <div class="col-md-4">
                                <label for="prezzo_edit" class="form-label fw-medium text-muted small">Prezzo (€) *</label>
                                <div class="input-group-custom m-0">
                                    <i class="fa-solid fa-euro-sign input-icon" style="font-size: 0.95rem;"></i>
                                    <input type="number" step="0.01" class="form-control" id="prezzo_edit" name="prezzo" placeholder="0.00" value="14.50" required>
                                </div>
                            </div>
                            <!-- Formato -->
                            <div class="col-md-4">
                                <label for="formato_edit" class="form-label fw-medium text-muted small">Formato *</label>
                                <select class="form-select form-control" id="formato_edit" name="formato" required>
                                    <option value="" disabled>Seleziona formato...</option>
                                    <option value="fisico" selected>Libro Cartaceo (Fisico)</option>
                                    <option value="ebook">Edizione Digitale (eBook)</option>
                                </select>
                            </div>
                            <!-- Quantità -->
                            <div class="col-md-3">
                                <label for="quantita_edit" class="form-label fw-medium text-muted small">Qtà (Stock)</label>
                                <input type="number" class="form-control" id="quantita_edit" name="quantita" value="15" min="0">
                            </div>
                            <!-- Copertina -->
                            <div class="col-md-5">
                                <label for="copertina_edit" class="form-label fw-medium text-muted small">Link Immagine Copertina</label>
                                <div class="input-group-custom m-0">
                                    <i class="fa-solid fa-link input-icon"></i>
                                    <input type="url" class="form-control" id="copertina_edit" name="copertina" placeholder="https://esempio.com/img.jpg" value="https://images.unsplash.com/photo-1481627834876-b7833e8f5570">
                                </div>
                            </div>
                            <!-- Descrizione -->
                            <div class="col-12">
                                <label for="descrizione_edit" class="form-label fw-medium text-muted small">Descrizione Estesa</label>
                                <textarea class="form-control" id="descrizione_edit" name="descrizione" rows="4" placeholder="Inserisci la sinossi o i dettagli del libro..." required>Un celebre romanzo di Umberto Eco ambientato nel Medioevo...</textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-3 mt-5 pt-3 border-top">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Annulla</button>
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
        function setupQuantityToggle(formatId, quantityId) {
            const formatSelect = document.getElementById(formatId);
            const quantityInput = document.getElementById(quantityId);
            
            if (formatSelect && quantityInput) {
                const toggle = () => {
                    if (formatSelect.value === 'ebook') {
                        quantityInput.value = '';
                        quantityInput.disabled = true;
                        quantityInput.placeholder = '∞';
                    } else {
                        quantityInput.disabled = false;
                        quantityInput.placeholder = 'es. 10';
                    }
                };
                
                // Imposta stato iniziale
                toggle();
                
                // Aggiorna stato al cambio
                formatSelect.addEventListener('change', toggle);
            }
        }
        
        setupQuantityToggle('formato_add', 'quantita_add');
        setupQuantityToggle('formato_edit', 'quantita_edit');
    });
    </script>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/footer.php'; ?>
