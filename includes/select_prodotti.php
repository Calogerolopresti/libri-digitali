<?php
// importo collegamento al db e avvio la sessione se non è già avviata 
require_once __DIR__ . '/../config/db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    // controllo se l'utente ha inviato una ricerca tramite get
    if (isset($_GET['cerca']) && !empty(trim($_GET['cerca']))) {
        // preparo la stringa per cercare col like
        $cerca = "%" . trim($_GET['cerca']) . "%";
        // scrivo la select per cercare i prodotti per nome
        $sql = 'SELECT * FROM Prodotti WHERE titolo LIKE ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cerca]);
    } else {
        // scrivo la select per mostrarmi tutti i prodotti 
        $sql = 'SELECT * FROM Prodotti';
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }
    $libri = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // in caso di errore inizzializzo un array vuota in modo tale da non mostrare errori al cliente 
    $libri = [];
}
?>