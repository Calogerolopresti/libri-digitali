<?php
// importo collegamento al db e avvio la sessione se non è già avviata 
require_once __DIR__ . '/../config/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// accetta solo richieste di tipo post con il bottone submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset(($_POST['submit']))) {
    
    // salviamo e sanifichiamo gli input
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // dichiariamo un array associativo in cui verranno accumulati gli errori
    $errors = [];

    // validazione nome
    if (empty($name)) {
        $errors['name'] = 'Il nome è obbligatorio.'; 
    }

    // validazione email
    if (empty($email)) {
        $errors['email'] = 'L\' email è obbligatoria.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Formato email non valido.';
    }

    // validazione password
    if (empty($password)) {
        $errors['password'] = 'La password è obbligatoria.';
    } else {

        // gestione dei criteri di complessità
        $password_issues = [];

        if (strlen($password) < 8) {
            $password_issues[] = 'almeno 8 caratteri';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $password_issues[] = 'almeno una lettera maiuscola';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $password_issues[] = 'almeno una lettera minuscola';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $password_issues[] = 'almeno un numero';
        }

        if (!preg_match('/[\W_]/', $password)) {
            $password_issues[] = 'almeno un carattere speciale';
        }

        if (!empty($password_issues)) {
            $errors['password'] = 'La password deve contenere ' . implode(', ', $password_issues) . '.';
        }

    }

    // se l'array che contiene gli errori non è vuoto salviamo gli 
    // errori e gli input precedenti in sessione, poi redirect
    if (!empty($errors)) {
    
        // salviamo gli errori in sessione
        $_SESSION['errors'] = $errors;

        // salviamo gli input precedenti in sessione
        $_SESSION['previous_input'] = [
            'name' => $name,
            'email' => $email
        ];

        header('Location: register.php');
        exit;

    } else {

        // hashing della password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Pulizia sessione da eventuali dati precedenti
        unset($_SESSION['errors'], $_SESSION['old_input']);

        try {
            $sql = 'INSERT INTO Utenti (nome, email, password) VALUES(?, ?, ?)';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $email, $hashed_password]);
            header('Location: login.php');
            exit;

        } catch (PDOException $e) {

            // se la query va male salviamo l'errore nel file di log
            error_log("errore al login ".$e->getMessage());
            header('Location: register.php?errore_registrazione');
            exit;
            
        }

    }
}

// array degli errori
$errors = $_SESSION['errors'] ?? []; 

// array degli input precedenti, tranne la password
$previous_input = $_SESSION['previous_input'] ?? [];

// eliminiamo i dati dopo averli letti, così al prossimo refresh della
// pagina il form sarà pulito e i messaggi non compariranno di nuovo
unset($_SESSION['errors'], $_SESSION['previous_input']);

function escape(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// vecchi input da ripopolare nei campi del form
$previous_name = escape($previous_input['name'] ?? '');
$previous_email = escape($previous_input['email'] ?? '');

// messaggi di errore per ogni campo
$name_error_message = escape($errors['name'] ?? '');
$email_error_message = escape($errors['email'] ?? '');
$password_error_message = escape($errors['password'] ?? '');

include __DIR__ . '/../includes/head.php';
?>
<body class="d-flex flex-column min-vh-100 fade-in ">

    <?php include __DIR__ . '/../includes/navbar_public.php'; ?>

<!-- Register Form -->
    <main class="container flex-grow-1 auth-wrapper fade-in fade-in-delay-1" style="margin-top: 80px;">
        <div class="row w-100 justify-content-center">
            <div class="col-md-8 col-lg-6 col-xl-5">
                <div class="card auth-card my-5">
                    <div class="text-center mb-5">
                        <h2 class="fw-bold text-secondary-color">Crea Account</h2>
                        <p class="text-muted">Inizia il tuo viaggio letterario con noi</p>
                    </div>
                    
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label for="nome" class="form-label">Nome Completo</label>
                            <div class="input-group-custom">
                                <i class="fa-regular fa-user input-icon"></i>
                                <input type="text" class="<?= $name_error_message ? 'has-error' : 'form-control'?>" id="nome" name="name" placeholder="Mario Rossi" value="<?= $previous_name ?>" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group-custom">
                                <i class="fa-regular fa-envelope input-icon"></i>
                                <input type="email" class="<?= $email_error_message ? 'has-error' : 'form-control' ?>" id="email" name="email" placeholder="mario.rossi@example.com" value="<?= $previous_email ?>" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group-custom">
                                <i class="fa-solid fa-lock input-icon"></i>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Crea una password sicura" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" name="submit" class="btn btn-primary w-100 py-3 text-uppercase">
                            Registrati <i class="fa-solid fa-check ms-2"></i>
                        </button>
                    </form>
                    
                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="mb-0 text-muted">Hai già un account? <a href="../auth/login.php" class="text-primary text-decoration-none fw-semibold">Accedi qui</a></p>
                    </div>
                </div>
            </div>
        </div>
    </main> 
    <script>
        function togglePassword(inputId, button) {
            const passwordInput = document.getElementById(inputId);
            const icon = button.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
