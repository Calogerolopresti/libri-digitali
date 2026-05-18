<?php
// Avviamo la sessione e verifichiamo che sia un admin
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Solo gli admin possono usare questa funzione
if (!isset($_SESSION['user_id']) || $_SESSION['ruolo'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['errore' => 'Accesso non autorizzato.']);
    exit;
}

// Solo richieste POST con il titolo del libro
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty(trim($_POST['titolo'] ?? ''))) {
    http_response_code(400);
    echo json_encode(['errore' => 'Titolo mancante.']);
    exit;
}

// Leggiamo la chiave API dal file di configurazione esterno
require_once __DIR__ . '/../config/groq.php';

$titolo  = trim($_POST['titolo']);
$formato = trim($_POST['formato'] ?? '');
$hint_formato = ($formato === 'digitale') ? 'eBook digitale' : (($formato === 'fisico') ? 'libro cartaceo' : 'libro');

// Costruiamo il prompt per generare una descrizione commerciale professionale
$prompt = "Sei un copywriter esperto di librerie online. 
Scrivi una descrizione commerciale accattivante per il seguente {$hint_formato}: \"{$titolo}\".
La descrizione deve:
- Essere lunga tra 80 e 120 parole
- Essere scritta in italiano
- Incuriosire il lettore senza rivelare troppo della trama
- Essere adatta a una scheda prodotto di un e-commerce
- Non contenere titoli, intestazioni o elenchi puntati
Rispondi SOLO con il testo della descrizione, senza preamboli né conclusioni.";

// Payload per le API Groq
$data = [
    'model'       => 'llama-3.3-70b-versatile',
    'messages'    => [['role' => 'user', 'content' => $prompt]],
    'max_tokens'  => 250,
    'temperature' => 0.75
];

// Chiamata alle API Groq tramite cURL
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

if ($http_code !== 200 || !$response) {
    http_response_code(502);
    echo json_encode(['errore' => 'Errore di comunicazione con l\'IA. Riprova.']);
    exit;
}

$result      = json_decode($response, true);
$descrizione = trim($result['choices'][0]['message']['content'] ?? '');

if (empty($descrizione)) {
    echo json_encode(['errore' => 'Risposta vuota dall\'IA. Riprova.']);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['descrizione' => $descrizione]);
