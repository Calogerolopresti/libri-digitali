<?php
// Avviamo la sessione per gestire la cronologia della conversazione
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Accettiamo solo richieste POST con il messaggio dell'utente
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['messaggio'])) {
    http_response_code(400);
    echo json_encode(['errore' => 'Richiesta non valida.']);
    exit;
}

// Leggiamo la chiave API dal file di configurazione esterno
$config_path = __DIR__ . '/config/groq.php';
if (!file_exists($config_path)) {
    http_response_code(500);
    echo json_encode(['errore' => 'Configurazione API non trovata.']);
    exit;
}
require_once $config_path;

// Connettiamo al database per recuperare il catalogo libri
require_once __DIR__ . '/config/db.php';

$catalogo_testo = "Catalogo non disponibile al momento.";
try {
    $stmt = $pdo->query("SELECT titolo, prezzo, formato, descrizione FROM Prodotti ORDER BY titolo ASC");
    $libri = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($libri)) {
        // Costruiamo una stringa leggibile con tutti i libri del catalogo
        $righe = [];
        foreach ($libri as $libro) {
            $formato  = $libro['formato'] === 'fisico' ? 'Cartaceo' : 'eBook digitale';
            $prezzo   = '€' . number_format((float)$libro['prezzo'], 2, ',', '');
            $righe[]  = "- \"{$libro['titolo']}\" | {$formato} | {$prezzo} | {$libro['descrizione']}";
        }
        $catalogo_testo = implode("\n", $righe);
    }
} catch (PDOException $e) {
    // In caso di errore DB il chatbot funziona lo stesso ma senza catalogo
    error_log("Chatbot: errore lettura catalogo - " . $e->getMessage());
}

$messaggio_utente = trim($_POST['messaggio']);
if (empty($messaggio_utente)) {
    echo json_encode(['errore' => 'Messaggio vuoto.']);
    exit;
}

// Gestiamo la cronologia della conversazione in sessione
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

// Aggiungiamo il messaggio dell'utente alla cronologia
$_SESSION['chat_history'][] = [
    'role'    => 'user',
    'content' => $messaggio_utente
];

// Limitiamo la cronologia agli ultimi 10 messaggi per non superare i token
$history = array_slice($_SESSION['chat_history'], -10);

// Definiamo il "system prompt" con il catalogo reale iniettato
$system_prompt = "Sei Leo, un assistente virtuale esperto e appassionato di letteratura per la libreria online 'E-Book & Co.'.
Il tuo ruolo è aiutare i clienti a scegliere libri, consigliare letture in base ai loro gusti, spiegare la differenza tra formati fisici e digitali, e rispondere a domande generali sui libri.
Sii sempre cordiale, entusiasta e conciso (massimo 3-4 frasi per risposta). Rispondi sempre in italiano.
Se ti chiedono cose che non riguardano i libri o la libreria, reindirizza gentilmente la conversazione al tema letterario.

Ecco il catalogo ATTUALE dei libri disponibili nel nostro negozio (usa SOLO questi per i consigli di acquisto):
{$catalogo_testo}

Quando consigli un libro dal catalogo, menziona sempre titolo, formato e prezzo. Se un libro richiesto non è nel catalogo, dillo chiaramente e proponi un'alternativa disponibile.";

// Costruiamo il payload per le API di Groq
$data = [
    'model'    => 'llama-3.3-70b-versatile',
    'messages' => array_merge(
        [['role' => 'system', 'content' => $system_prompt]],
        $history
    ),
    'max_tokens'  => 200,
    'temperature' => 0.8
];

// Inviamo la richiesta alle API di Groq
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

if ($http_code !== 200 || !$response) {
    echo json_encode(['errore' => 'Errore di comunicazione con l\'IA. Riprova.']);
    exit;
}

$result = json_decode($response, true);
$risposta_ai = $result['choices'][0]['message']['content'] ?? 'Non ho capito, puoi riformulare?';

// Aggiungiamo la risposta dell'IA alla cronologia della sessione
$_SESSION['chat_history'][] = [
    'role'    => 'assistant',
    'content' => $risposta_ai
];

// Restituiamo la risposta in formato JSON
header('Content-Type: application/json');
echo json_encode(['risposta' => $risposta_ai]);
