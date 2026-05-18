<?php
// carico le credenziali da un file separato che non va su git
require_once __DIR__ . '/secrets.php';

// calcolo automaticamente la base url del sito (funziona sia in locale che su hosting)
// IMPORTANTE: uso variabili con prefisso _ per non sovrascrivere $host usato dal db
if (!defined('BASE_URL')) {
    $_httpProtocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $_httpHost     = $_SERVER['HTTP_HOST'];
    // risalgo alla root del progetto (config/ -> root) usando __DIR__
    $_docRoot     = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $_projectRoot = str_replace('\\', '/', dirname(__DIR__));
    $_basePath    = rtrim(str_replace($_docRoot, '', $_projectRoot), '/');
    define('BASE_URL', $_httpProtocol . '://' . $_httpHost . $_basePath);
    unset($_httpProtocol, $_httpHost, $_docRoot, $_projectRoot, $_basePath);
}

$charset = 'utf8mb4';

// imposto il fuso orario anche per php
date_default_timezone_set('Europe/Rome');

// creo il link di collegamento al db
$dns = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// provo la connessione
try {
    $pdo = new PDO($dns, $user, $pass, $options);
    // imposto il fuso orario sul db
    $pdo->exec("SET time_zone = '+02:00'");

// in caso di errore lo salvo nel log e mostro un messaggio generico all utente
} catch(PDOException $e) {
    error_log("errore connessione db: " . $e->getMessage());
    die("Errore di connessione, riprova più tardi.");
}
?>