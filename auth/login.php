<?php
// importo collegamento al db e avvio la sessione se non è già avviata 
require_once __DIR__ . '/../config/db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// inizzializzo la variabile di sessione per verificare i tentativi se non esiste già 
if(!isset($_SESSION['tentativi'])){
    $_SESSION['tentativi']=0;
}

// genero il token csrf se non esiste ancora nella sessione
if(!isset($_SESSION['csrf_token'])){
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// se inviano il form in pagina con method post entrano nell if 
if($_SERVER['REQUEST_METHOD']==='POST'){

    // verifico che il token csrf sia valido prima di fare qualsiasi cosa
    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']){
        $errore = "Richiesta non valida, riprova.";
    } else {
        // prima di fare la chiamata al db controllo che i tentativi non siano stati superati 
        if($_SESSION['tentativi']<=4){
            $_SESSION['tentativi']++;
            // salvo le credenziali inviate tramite post 
            $email = $_POST['email'];
            $password= $_POST['password'];

            // faccio la query per cercare l utente con quella data email 
            try{
                $stmt = $pdo->prepare('SELECT * FROM Utenti WHERE email=?');
                $stmt->execute([$email]);
                $user = $stmt->fetch();
            }catch(PDOException $e){
                // se la query va male salviamo l errore nel file di log e mostriamo un errore all utente 
                error_log("errore al login ".$e->getMessage());
                $errore = "Si è verificato un problema tecnico. Riprova più tardi.";
            }

            // verifica se l'utente esiste e se la password fornita corrisponde all'hash nel database 
            if(isset($user) && $user && password_verify($password,$user['password'])){
                // rigenero l id sessione per evitare session fixation
                session_regenerate_id(true);
                // se lutente esiste e la pass coincice mi salvo le informazioni dell utente nella sessione 
                $_SESSION['user_id']=$user['id'];
                $_SESSION['user_nome']=$user['nome'];
                $_SESSION['user_email']=$user['email'];
                $_SESSION['ruolo']=$user['ruolo'];
                // reindirizzo l utente in base al suo ruolo 
                if($user['ruolo']==='admin'){
                    header('Location:../admin/index.php');
                    exit();
                }else{
                    header('Location:../index-logged.php');
                    exit(); 
                }
            }else{
                $errore='email o password non corrette, Tentativi: '.$_SESSION['tentativi'];
            }
        }else{
            // se i tentativi sono stati superati lo mostro a schermo 
            $errore="Troppi tentativi, Riprova più tardi";
        }
    }
}

?>



<?php include __DIR__ . '/../includes/head.php'; ?>
<body class="d-flex flex-column min-vh-100 fade-in ">

    <?php include __DIR__ . '/../includes/navbar_public.php'; ?>

<!-- Login Form -->
    <main class="container flex-grow-1 auth-wrapper fade-in fade-in-delay-1" style="margin-top: 80px;">
        <div class="row w-100 justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card auth-card">
                    <div class="text-center mb-5">
                        <h2 class="fw-bold text-secondary-color">Bentornato</h2>
                        <p class="text-muted">Accedi al tuo account per continuare a leggere</p>
                    </div>
                    
                    <form method="POST" action="">
                        <!-- campo nascosto per la protezione csrf -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <?php if(isset($errore)) echo "<p style='color: red'>" . htmlspecialchars($errore) . "</p>"?>
                        <div class="mb-4">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group-custom">
                                <i class="fa-regular fa-envelope input-icon"></i>
                                <input type="email" class="form-control" id="email" name="email" placeholder="mario.rossi@example.com" required>
                            </div>
                        </div>
                        <div class="mb-5">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group-custom">
                                <i class="fa-solid fa-lock input-icon"></i>
                                <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 text-uppercase">
                            Accedi <i class="fa-solid fa-arrow-right ms-2"></i>
                        </button>
                    </form>
                    
                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="mb-0 text-muted">Non hai un account? <a href="../auth/register.php" class="text-primary text-decoration-none fw-semibold">Registrati qui</a></p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    

    <?php include __DIR__ . '/../includes/footer.php'; ?>
