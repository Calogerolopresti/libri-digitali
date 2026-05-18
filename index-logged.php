<?php
// importo collegamento al db e avvio la sessione se non è già avviata 
require_once __DIR__ . '/config/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
//  includo la funzione che mi salva i libri nel database 
include 'includes/select_prodotti.php';

// controllo se l utente è con ruolo user o se ha mai fatto accesso e se non lo è lo butto fuori 
if(!isset($_SESSION['user_id']) || $_SESSION['ruolo']!=='user'){
    header('Location:index.php');
    exit();
}
?>


<?php include __DIR__ . '/includes/head.php'; ?>
<body class="d-flex flex-column min-vh-100 fade-in ">

    <?php include __DIR__ . '/includes/navbar_user.php'; ?>

<!-- Hero Section -->
    <section class="hero-section fade-in fade-in-delay-1">
        <div class="container hero-content">
            <h1 class="hero-title">Esplora Mondi, <br><span>Trova Te Stesso.</span></h1>
            <p class="hero-subtitle">Benvenuto in E-Book & Co., la destinazione premium per i lettori di tutto il mondo. Libri fisici di pregio ed edizioni digitali a portata di click.</p>
            <a href="#catalogo" class="btn btn-primary btn-lg mt-2">
                <i class="fa-solid fa-compass"></i> Esplora il Catalogo
            </a>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container mb-5 flex-grow-1 fade-in fade-in-delay-2" id="catalogo">
        <div class="mb-4 pb-2 border-bottom">
            <h2 class="fw-bold mb-0 text-secondary-color">Ultime Novità</h2>
        </div>
        
        <!-- se non ce nessun libro mostro che non ci sono libri nel catalogo  -->
        <?php if(count($libri)<=0):?>
            <p>Nessun Libro disponibile</p>
        <?php else:?>    
            <div class="row g-4 mb-5">
                <?php foreach($libri as $libro):?>
                <!-- Esempio Card 1 (Libro Fisico) -->
                    <div class="col-12 col-sm-6 col-lg-3">
                        <a style="text-decoration: none;" href="prodotto.php?id=<?php echo htmlspecialchars($libro['id'])?>">
                            <div class="card book-card">
                                <div class="card-img-wrapper">
                                    <img src="<?php echo htmlspecialchars($libro['copertina'])?>" class="book-cover" alt="Copertina">
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <?php if($libro['formato']=='fisico'):?>
                                        <span class="badge bg-success mb-3 align-self-start"><i class="fa-solid fa-book-open me-1"></i> Edizione Cartacea</span>
                                    <?php else:?>
                                        <span class="badge bg-info mb-3 align-self-start"><i class="fa-solid fa-download me-1"></i> Edizione Digitale</span>
                                    <?php endif?>    
                                    <h5 class="card-title"><?php echo htmlspecialchars($libro['titolo'])?></h5>
                                    <div class="mt-auto d-flex justify-content-between align-items-center">
                                        <span class="price">€ <?php echo htmlspecialchars($libro['prezzo'])?></span>
                                    </div>
                                </div>
                            </div>
                        </a>        
                    </div>
                <?php endforeach?>
        <?php endif?>        
    </main>

    

    <?php include __DIR__ . '/includes/footer.php'; ?>
