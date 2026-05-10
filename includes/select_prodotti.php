<?php
// importo collegamento al db e avvio la sessione se non è già avviata 
require_once '../config/db.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

?>