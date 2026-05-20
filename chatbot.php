<?php
// avviamo la sessione per gestire la cronologia dei messaggi scambiati tra l utente e il chatbot
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// accettiamo solo chiamate in post con dentro il messaggio dell utente altrimenti diamo errore bad request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['messaggio'])) {
    http_response_code(400);
    echo json_encode(['errore' => 'Richiesta non valida.']);
    exit;
}

// carichiamo la chiave di groq dal file di config esterno
$config_path = __DIR__ . '/config/groq.php';
if (!file_exists($config_path)) {
    http_response_code(500);
    echo json_encode(['errore' => 'Configurazione API non trovata.']);
    exit;
}
require_once $config_path;

// ci colleghiamo al db dei libri cosi leo puo leggere il catalogo in tempo reale e consigliare i libri che abbiamo davvero in vendita
require_once __DIR__ . '/config/db.php';

$catalogo_testo = "Catalogo non disponibile al momento.";
try {
    // facciamo una query sul db ordinando i libri per titolo
    $stmt = $pdo->query("SELECT titolo, prezzo, formato, descrizione FROM Prodotti ORDER BY titolo ASC");
    $libri = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($libri)) {
        // assembliamo una bella stringa con tutti i dettagli dei libri (formato, prezzo, descrizione) cosi l ia sa cosa proporre
        $righe = [];
        foreach ($libri as $libro) {
            $formato  = $libro['formato'] === 'fisico' ? 'Cartaceo' : 'eBook digitale';
            $prezzo   = '€' . number_format((float)$libro['prezzo'], 2, ',', '');
            $righe[]  = "- \"{$libro['titolo']}\" | {$formato} | {$prezzo} | {$libro['descrizione']}";
        }
        $catalogo_testo = implode("\n", $righe);
    }
} catch (PDOException $e) {
    // se il db ha problemi salviamo l errore nel log, la chat funziona lo stesso ma leo avvisera che non ha il catalogo sottomano
    error_log("Chatbot: errore lettura catalogo - " . $e->getMessage());
}

// puliamo il messaggio dell utente per evitare spazi vuoti
$messaggio_utente = trim($_POST['messaggio']);
if (empty($messaggio_utente)) {
    echo json_encode(['errore' => 'Messaggio vuoto.']);
    exit;
}

// inizzializziamo l array della chat in sessione se è la prima volta che l utente scrive
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

// aggiungiamo subito il messaggio dell utente alla cronologia
$_SESSION['chat_history'][] = [
    'role'    => 'user',
    'content' => $messaggio_utente
];

// teniamo solo gli ultimi 10 messaggi in cronologia con array_slice cosi non sforiamo il limite di token di groq e risparmiamo banda
$history = array_slice($_SESSION['chat_history'], -10);

// definiamo il prompt di sistema spiegando a llama chi è (Leo) e come si deve comportare. gli diamo in pasto il catalogo reale dei libri preso dal db
$system_prompt = "Sei Leo, un assistente virtuale esperto e appassionato di letteratura per la libreria online 'E-Book & Co.'.
Il tuo ruolo è aiutare i clienti a scegliere libri, consigliare letture in base ai loro gusti, spiegare la differenza tra formati fisici e digitali, e rispondere a domande generali sui libri.
Sii sempre cordiale, entusiasta e conciso (massimo 3-4 frasi per risposta). Rispondi sempre in italiano.
Se ti chiedono cose che non riguardano i libri o la libreria, reindirizza gentilmente la conversazione al tema letterario.

Ecco il catalogo ATTUALE dei libri disponibili nel nostro negozio (usa SOLO questi per i consigli di acquisto):
{$catalogo_testo}

Quando consigli un libro dal catalogo, menziona sempre titolo, formato e prezzo. Se un libro richiesto non è nel catalogo, dillo chiaramente e proponi un'alternativa disponibile.";

// prepariamo l array dei dati da inviare a groq unendo il prompt di sistema con la cronologia dei messaggi passati
$data = [
    'model'    => 'llama-3.3-70b-versatile',
    'messages' => array_merge(
        [['role' => 'system', 'content' => $system_prompt]],
        $history
    ),
    'max_tokens'  => 200,
    'temperature' => 0.8
];

// facciamo partire la chiamata cURL verso le api di groq
$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($data),
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . GROQ_API_KEY,
        'Content-Type: application/json'
    ],
    CURLOPT_TIMEOUT        => 15
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// se la risposta fallisce o il codice http non è 200 restituiamo errore all utente
if ($http_code !== 200 || !$response) {
    echo json_encode(['errore' => 'Errore di comunicazione con l\'IA. Riprova.']);
    exit;
}

// decodifichiamo il json che ci torna indietro ed estraiamo il testo della risposta di leo
$result = json_decode($response, true);
$risposta_ai = $result['choices'][0]['message']['content'] ?? 'Non ho capito, puoi riformulare?';

// salviamo anche la risposta dell ia in sessione cosi si ricorda cosa ha detto all utente alla prossima battuta
$_SESSION['chat_history'][] = [
    'role'    => 'assistant',
    'content' => $risposta_ai
];

// impostiamo l header json e stampiamo la risposta per js
header('Content-Type: application/json');
echo json_encode(['risposta' => $risposta_ai]);
