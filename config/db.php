<?php
// salvo le credenziali del db 
$host    ='193.203.168.45';
$db      ='u641252676_libridigitali';
$user    ='u641252676_libridigitali';
$pass    ='Libridigitali2026';
$charset = 'utf8mb4';

// creo il link di collegamento al db 
$dns = "mysql:host=$host;dbname=$db;charser=$charset";


$options=[
    PDO::ATTR_ERRMODE            =>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE =>PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// provo la connessione 
try{
    $pdo=new PDO($dns,$user,$pass,$options);
    
// in caso di errore lancio il problema 
}catch(PDOException $e){
    die("Errore: ".$e->getMessage()) ;
}
?>
