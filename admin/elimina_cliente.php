<?php
// importo collegamento al db e avvio la sessione se non è già avviata 
require_once __DIR__ . '/../config/db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// controllo se l utente è con ruolo user o se ha mai fatto accesso e se non lo è lo butto fuori 
if (!isset($_SESSION['user_id']) || $_SESSION['ruolo'] !== 'admin') {
    header('Location:../index.php');
    exit();
}

// elimino solo se la chiamata è post e se il token csrf è valido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header('Location:clienti.php?errore_delete');
        exit();
    }

    if (isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        
        try {
            // inizio transazione per cancellare l'utente e tutti i suoi dati collegati 
            $pdo->beginTransaction();

            // cancello i prodotti presenti nel carrello dell'utente 
            $sql_carrello = "DELETE FROM Carrello WHERE id_utente = ?";
            $stmt_carrello = $pdo->prepare($sql_carrello);
            $stmt_carrello->execute([$id]);

            // cancello i dettagli degli ordini associati agli ordini dell'utente 
            $sql_dettagli = "DELETE FROM Dettagli_Ordine WHERE id_ordine IN (SELECT id FROM Ordini WHERE id_utente = ?)";
            $stmt_dettagli = $pdo->prepare($sql_dettagli);
            $stmt_dettagli->execute([$id]);

            // cancello gli ordini dell'utente 
            $sql_ordini = "DELETE FROM Ordini WHERE id_utente = ?";
            $stmt_ordini = $pdo->prepare($sql_ordini);
            $stmt_ordini->execute([$id]);

            // cancello l'utente dalla tabella Utenti 
            $sql_utente = "DELETE FROM Utenti WHERE id = ?";
            $stmt_utente = $pdo->prepare($sql_utente);
            $stmt_utente->execute([$id]);

            // confermo tutte le eliminazioni 
            $pdo->commit();

            header("Location: clienti.php?successo_delete");
            exit();
        } catch (PDOException $e) {
            // in caso di errore annullo le modifiche 
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("errore durante l'eliminazione del cliente: " . $e->getMessage());
            header("Location: clienti.php?errore_delete");
            exit();
        }
    }
}
?>
