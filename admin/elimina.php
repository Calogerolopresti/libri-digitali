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

if(isset($_GET['id'])){
    $id=(int)$_GET['id'];
    try{
        $sql="DELETE FROM Prodotti WHERE id=?";
        $stmt=$pdo->prepare($sql);
        $stmt->execute([$id]);
        header("Location: index.php");
        exit();
    }catch(PDOException $e){
        error_log("errore durante l'eliminazione del prodotto: ").$e->getMessage();
        header("Location: index.php?errore_delete");
        exit();
    }
    
}    
