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


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // controllo che ci sia e sia corretto il csfr toker 
    if (!isset($_POST['csfr_token']) || $_POST['csfr_token'] !== $_SESSION['csfr_token']) {
        header('Location:index.php?errore_insert');
        exit();
    }
    
    // controllo url della copertina e se cè problema metto come coperina una mia default 
    $copertina = filter_var($_POST['copertina'], FILTER_VALIDATE_URL) ? $_POST['copertina'] : 'https://img.magnific.com/foto-gratuito/copertina-anteriore-di-un-libro-a-copertina-rossa_1101-833.jpg';
    try{
        if($_POST['quantita']<0 || !isset($_POST['quantita']) || $_POST['quantita']==null ){
            $quantita=0;
        }else{
            $quantita=$_POST['quantita'];
        }
        $sql="INSERT INTO Prodotti (titolo,descrizione,prezzo, formato, disponibilita,copertina) VALUES (?,?,?,?,?,?)";
        $stmt=$pdo->prepare($sql);
        $stmt->execute([$_POST['titolo'],$_POST['descrizione'],$_POST['prezzo'],$_POST['formato'],$quantita,$copertina]);
        header('Location:index.php');
        exit();
    }catch(PDOException $e){
        error_log("errore inserimento prodotto: " . $e->getMessage());
         header('Location:index.php?errore_insert');
         exit();
     }
    
}
?>