<?php
// importo collegamento al db e avvio la sessione se non è già avviata 
require_once '../config/db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


try{
    // scrivo la select per mostrarmi tutti i prodotti 
    $sql = 'SELECT * FROM Prodotti';

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $libri = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
   $libri = [];
}
?>