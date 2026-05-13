<?php
// importo collegamento al db e avvio la sessione se non è già avviata 
require_once __DIR__ . '/config/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}



?>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/head.php'; ?>
<body class="d-flex flex-column min-vh-100 fade-in pt-5 mt-4 bg-light">

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/navbar_user.php'; ?>

<!-- Profile Content -->
    <main class="container mb-5 flex-grow-1 fade-in fade-in-delay-1 mt-5">
        <h2 class="fw-bold text-secondary-color mb-4">Ciao, <span class="text-primary"><?php echo htmlspecialchars($_SESSION['user_nome'])?></span>!</h2>
        
        <div class="row g-4">
            <!-- Personal Info Card -->
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
                        <ul class="list-unstyled mb-4 text-muted" style="line-height: 2;">
                            <li class="mb-2"><i class="fa-regular fa-envelope text-primary me-2 text-center" style="width: 20px;"></i><?php echo htmlspecialchars($_SESSION['user_email'])?></li>
                        </ul>
                        <button class="btn btn-outline-primary w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#editProfileModal"><i class="fa-solid fa-pen-to-square me-2"></i> Modifica Profilo</button>
                    </div>
                </div>
            </div>
            
            <!-- Order History Card -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-2">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2">
                            <h5 class="fw-bold mb-0 d-flex align-items-center">
                                <i class="fa-solid fa-box-open text-primary me-2"></i> Storico Ordini
                            </h5>
                            <span class="badge bg-light text-muted border px-3 py-2 rounded-pill">3 Ordini Totali</span>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-custom align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="text-muted fw-medium rounded-start ps-3">ID Ordine</th>
                                        <th scope="col" class="text-muted fw-medium">Data</th>
                                        <th scope="col" class="text-muted fw-medium text-end">Totale</th>
                                        <th scope="col" class="text-muted fw-medium text-center rounded-end pe-3">Azione</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="ps-3"><span class="fw-bold text-secondary-color">#10045</span></td>
                                        <td><span class="text-muted small">01/05/2026 14:30</span></td>
                                        <td class="text-end fw-bold text-primary">€ 14.50</td>
                                        <td class="text-center pe-3">
                                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#orderDetailModal"><i class="fa-solid fa-eye me-1"></i> Dettagli</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-3"><span class="fw-bold text-secondary-color">#10044</span></td>
                                        <td><span class="text-muted small">15/04/2026 09:15</span></td>
                                        <td class="text-end fw-bold text-primary">€ 8.99</td>
                                        <td class="text-center pe-3">
                                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#orderDetailModal"><i class="fa-solid fa-eye me-1"></i> Dettagli</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-3"><span class="fw-bold text-secondary-color">#10030</span></td>
                                        <td><span class="text-muted small">10/03/2026 18:45</span></td>
                                        <td class="text-end fw-bold text-primary">€ 15.00</td>
                                        <td class="text-center pe-3">
                                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#orderDetailModal"><i class="fa-solid fa-eye me-1"></i> Dettagli</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Modifica Profilo -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-secondary-color" id="editProfileModalLabel">Modifica Profilo</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="#">
                        <div class="mb-3">
                            <label for="nome_edit" class="form-label text-muted small fw-medium">Nome Completo</label>
                            <input type="text" class="form-control rounded-3" id="nome_edit" name="nome" value="Mario Rossi" required>
                        </div>
                        <div class="mb-4">
                            <label for="email_edit" class="form-label text-muted small fw-medium">Email</label>
                            <input type="email" class="form-control rounded-3" id="email_edit" name="email" value="mario.rossi@example.com" required>
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

    <!-- Modal Dettagli Ordine -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title fw-bold text-secondary-color mb-1" id="orderDetailModalLabel">Dettagli Ordine #10045</h5>
                        <p class="text-muted small mb-0">Effettuato il 01/05/2026 alle 14:30</p>
                    </div>
                    <button type="button" class="btn-close shadow-none align-self-start" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 border">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-muted fw-medium ps-3">Prodotto</th>
                                    <th scope="col" class="text-muted fw-medium text-center">Prezzo Unitario</th>
                                    <th scope="col" class="text-muted fw-medium text-center">Qtà</th>
                                    <th scope="col" class="text-muted fw-medium text-end pe-3">Subtotale</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-3 py-3">
                                        <div class="d-flex align-items-center">
                                            <img src="https://via.placeholder.com/40x60/f8f9fa/a52a2a?text=Eco" alt="Copertina" class="rounded me-3 shadow-sm" style="object-fit: cover;">
                                            <div>
                                                <h6 class="fw-bold mb-1 text-secondary-color mb-0">Il Nome della Rosa</h6>
                                                <span class="badge bg-success small fw-medium mt-1">Cartaceo</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center text-muted fw-medium">€ 14.50</td>
                                    <td class="text-center"><span class="badge bg-light text-dark border px-2 py-1">1</span></td>
                                    <td class="text-end fw-bold text-primary pe-3">€ 14.50</td>
                                </tr>
                            </tbody>
                            <tfoot class="border-top-0 bg-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-medium text-muted pt-3 pb-3 border-bottom-0">Totale Ordine:</td>
                                    <td class="text-end fw-bold text-primary fs-5 pt-3 pb-3 border-bottom-0 pe-3">€ 14.50</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/footer.php'; ?>
