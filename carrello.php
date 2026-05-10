<?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/head.php'; ?>
<body class="d-flex flex-column min-vh-100 fade-in pt-5 mt-4 bg-light">

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/libri-digitali/includes/navbar_user.php'; ?>

<!-- Cart Content -->
    <main class="container mb-5 flex-grow-1 fade-in fade-in-delay-1 mt-5">
        <h2 class="fw-bold text-secondary-color mb-4"><i class="fa-solid fa-cart-shopping me-2 text-primary"></i> Il tuo Carrello</h2>
        
        <div class="row g-4 align-items-start">
            <!-- Cart Items -->
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
                                    <tr>
                                        <td class="ps-4 py-4">
                                            <div class="d-flex align-items-center">
                                                <img src="https://via.placeholder.com/60x90/f8f9fa/a52a2a?text=Eco" alt="Copertina" class="rounded me-3 shadow-sm" style="object-fit: cover;">
                                                <div>
                                                    <h6 class="fw-bold mb-1 text-secondary-color">Il Nome della Rosa</h6>
                                                    <span class="badge bg-success small fw-medium">Cartaceo</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-muted fw-medium">€ 14.50</td>
                                        <td>
                                            <div class="qty-selector d-inline-flex align-items-center" style="border:none!important;background-color:transparent!important;">
                                                <button class="btn qty-btn shadow-sm" type="button" onclick="this.parentNode.querySelector('input[type=number]').stepDown()"><i class="fa-solid fa-minus" style="font-size: 0.75rem;"></i></button>
                                                <input type="number" class="form-control text-center qty-input hide-spinners px-1" value="1" min="1" max="10">
                                                <button class="btn qty-btn shadow-sm" type="button" onclick="this.parentNode.querySelector('input[type=number]').stepUp()"><i class="fa-solid fa-plus" style="font-size: 0.75rem;"></i></button>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold text-primary">€ 14.50</td>
                                        <td class="text-center pe-4">
                                            <button class="btn btn-outline-danger btn-round-perfect shadow-sm border-0" title="Rimuovi"><i class="fa-solid fa-trash-can"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4 py-4">
                                            <div class="d-flex align-items-center">
                                                <img src="https://via.placeholder.com/60x90/f8f9fa/a52a2a?text=1984" alt="Copertina" class="rounded me-3 shadow-sm" style="object-fit: cover;">
                                                <div>
                                                    <h6 class="fw-bold mb-1 text-secondary-color">1984</h6>
                                                    <span class="badge bg-info small fw-medium">eBook</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-muted fw-medium">€ 8.99</td>
                                        <td>
                                            <div class="qty-selector d-inline-flex align-items-center">
                                                <button class="btn qty-btn shadow-sm" type="button" onclick="this.parentNode.querySelector('input[type=number]').stepDown()"><i class="fa-solid fa-minus" style="font-size: 0.75rem;"></i></button>
                                                <input type="number" class="form-control text-center qty-input hide-spinners px-1" value="1" min="1" max="10">
                                                <button class="btn qty-btn shadow-sm" type="button" onclick="this.parentNode.querySelector('input[type=number]').stepUp()"><i class="fa-solid fa-plus" style="font-size: 0.75rem;"></i></button>
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold text-primary">€ 8.99</td>
                                        <td class="text-center pe-4">
                                            <button class="btn btn-outline-danger btn-round-perfect shadow-sm border-0" title="Rimuovi"><i class="fa-solid fa-trash-can"></i></button>
                                        </td>
                                    </tr>
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
                            <span>Subtotale (2 articoli)</span>
                            <span class="fw-medium">€ 23.49</span>
                        </div>
                        <div class="d-flex justify-content-between mb-4 text-muted">
                            <span>Costi di Spedizione</span>
                            <span class="fw-medium">€ 4.90</span>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center border-top pt-4 mb-4">
                            <span class="fw-bold fs-5 text-secondary-color">Totale</span>
                            <span class="fw-bold fs-3 text-primary">€ 28.39</span>
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
                    <h6 class="fw-bold mb-3">Questo è un sito dimostrativo</h6>
                    <p class="text-muted mb-4">Non verranno richiesti o elaborati dati di pagamento reali. Clicca su "Simula Pagamento" per completare l'ordine in modo fittizio.</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary py-2 rounded-pill fw-bold" onclick="simulatePayment(this)">
                            Simula Pagamento € 28.39
                        </button>
                        <button type="button" class="btn btn-light py-2 rounded-pill fw-medium" data-bs-dismiss="modal">Annulla</button>
                    </div>
                    <div id="paymentSuccess" class="alert alert-success mt-3 d-none rounded-3 text-start" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i>Pagamento simulato con successo! Ritorno alla home...
                    </div>
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
