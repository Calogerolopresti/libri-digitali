<?php
// importo collegamento al db e avvio la sessione se non è già avviata 
require_once __DIR__ . '/../config/db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    // controllo se l'utente ha selezionato un formato specifico 
    $formato = isset($_GET['formato']) ? trim($_GET['formato']) : '';

    // controllo se l'utente ha inviato una ricerca tramite get
    if (isset($_GET['cerca']) && !empty(trim($_GET['cerca']))) {
        // preparo la stringa per cercare col like
        $cerca = "%" . trim($_GET['cerca']) . "%";
        
        // se l'utente ha filtrato anche per formato specifico 
        if ($formato === 'fisico' || $formato === 'digitale') {
            // scrivo la select per cercare i prodotti per nome e per formato 
            $sql = 'SELECT * FROM Prodotti WHERE titolo LIKE ? AND formato = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$cerca, $formato]);
        } else {
            // scrivo la select per cercare i prodotti per nome in tutti i formati
            $sql = 'SELECT * FROM Prodotti WHERE titolo LIKE ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$cerca]);
        }
    } else {
        // se l'utente ha filtrato per formato specifico ma senza testo 
        if ($formato === 'fisico' || $formato === 'digitale') {
            // scrivo la select per mostrare i prodotti per quel formato 
            $sql = 'SELECT * FROM Prodotti WHERE formato = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$formato]);
        } else {
            // scrivo la select per mostrarmi tutti i prodotti 
            $sql = 'SELECT * FROM Prodotti';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }
    }
    $libri = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // in caso di errore inizzializzo un array vuota in modo tale da non mostrare errori al cliente 
    $libri = [];
}
?>