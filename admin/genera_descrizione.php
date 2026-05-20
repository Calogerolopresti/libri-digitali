<?php
// avviamo la sessione per controllare chi sta provando a generare la descrizione
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// controllo se l utente ha fatto accesso e se ha il ruolo admin, altrimenti lo buttiamo fuori con un errore 403
if (!isset($_SESSION['user_id']) || $_SESSION['ruolo'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['errore' => 'Accesso non autorizzato.']);
    exit;
}

// accettiamo solo richieste post con dentro il titolo del libro da descrivere
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty(trim($_POST['titolo'] ?? ''))) {
    http_response_code(400);
    echo json_encode(['errore' => 'Titolo mancante.']);
    exit;
}

// carichiamo la chiave api dal file config groq salendo di una cartella
require_once __DIR__ . '/../config/groq.php';

$titolo  = trim($_POST['titolo']);
$formato = trim($_POST['formato'] ?? '');
// in base al formato scelto impostiamo un aiutino testuale per il prompt dell ia
$hint_formato = ($formato === 'digitale') ? 'eBook digitale' : (($formato === 'fisico') ? 'libro cartaceo' : 'libro');

// prepariamo le istruzioni dettagliate per far scrivere all ia una descrizione commerciale accattivante da ecommerce
$prompt = "Sei un copywriter esperto di librerie online. 
Scrivi una descrizione commerciale accattivante per il seguente {$hint_formato}: \"{$titolo}\".
La descrizione deve:
- Essere lunga tra 80 e 120 parole
- Essere scritta in italiano
- Incuriosire il lettore senza rivelare troppo della trama
- Essere adatta a una scheda prodotto di un e-commerce
- Non contenere titoli, intestazioni o elenchi puntati
Rispondi SOLO con il testo della descrizione, senza preamboli né conclusioni.";

// impacchettiamo i dati impostando il modello llama 3.3 e la temperatura per una descrizione commerciale equilibrata
$data = [
    'model'       => 'llama-3.3-70b-versatile',
    'messages'    => [['role' => 'user', 'content' => $prompt]],
    'max_tokens'  => 250,
    'temperature' => 0.75
];

// facciamo partire la chiamata cURL verso groq per interloquire con l ia
$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($data),
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . GROQ_API_KEY,
        'Content-Type: application/json'
    ],
    CURLOPT_TIMEOUT => 15
]);

$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// se groq risponde male o cè un problema di rete restituiamo un errore generico
if ($http_code !== 200 || !$response) {
    http_response_code(502);
    echo json_encode(['errore' => 'Errore di comunicazione con l\'IA. Riprova.']);
    exit;
}

// decodifichiamo il json per prendere la descrizione generata
$result      = json_decode($response, true);
$descrizione = trim($result['choices'][0]['message']['content'] ?? '');

// se l ia ci ha ritornato una stringa vuota diamo un avviso di riprovare
if (empty($descrizione)) {
    echo json_encode(['errore' => 'Risposta vuota dall\'IA. Riprova.']);
    exit;
}

// se tutto è andato bene restituiamo la descrizione in formato json al javascript dell admin
header('Content-Type: application/json');
echo json_encode(['descrizione' => $descrizione]);
