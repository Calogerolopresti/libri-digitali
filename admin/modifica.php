<?php
// importo collegamento al db e avvio la sessione se non è già avviata 
require_once __DIR__ . '/../config/db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// controllo se l utente è con ruolo user o se ha mai fatto accesso e se non lo è lo butto fuori 
if (!isset($_SESSION['user_id']) || $_SESSION['ruolo'] !== 'admin') {
    header('Location:../index.php?');
    exit();
}
// aggiungere controllo token csrf 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // controllo che ci sia e sia corretto il csfr toker 
    if (!isset($_POST['csfr_token']) || $_POST['csfr_token'] !== $_SESSION['csfr_token']) {
        header('Location:index.php?errore_update');
        exit();
    }
    try {
        // Se deve essere un URL valido
        $copertina = filter_var($_POST['copertina'], FILTER_VALIDATE_URL) ? $_POST['copertina'] : 'default.jpg';

        $sql = "UPDATE Prodotti SET titolo=?,descrizione=?,prezzo=?,formato=?,disponibilita=?,copertina=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_POST['titolo'], $_POST['descrizione'], (float)$_POST['prezzo'], $_POST['formato'], (int)$_POST['quantita'], $copertina, (int)$_POST['id_prodotto']]);
        header('Location:index.php');
        exit();
    } catch (PDOException $e) {
        error_log("errore modifica prodotto: " . $e->getMessage());
        header('Location:index.php?errore_update');
        exit();
    }
}
