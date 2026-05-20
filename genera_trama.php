<?php
// 1. Controlliamo se nella richiesta URL è presente il parametro 'titolo'
if (isset($_GET['titolo'])) {
    // 2. Mettiamo in sicurezza il titolo del libro per evitare attacchi hacker
    $titolo = escapeshellarg($_GET['titolo']);
    
    // prendiamo lo stile richiesto e lo filtriamo per sicurezza
    $stile = isset($_GET['stile']) ? $_GET['stile'] : 'normale';
    $allowed_styles = ['normale', 'spoiler', '3punti', 'bambini', 'recensione'];
    if (!in_array($stile, $allowed_styles)) {
        $stile = 'normale';
    }
    $stile_arg = escapeshellarg($stile);
    
    // 3. Troviamo il percorso assoluto dello script Python
    $script_path = escapeshellarg(__DIR__ . '/ai_plot.py');
    
    // Cerchiamo il percorso assoluto di python3 (MAMP spesso ha il PATH limitato e non trova "python3" semplice)
    $python_paths = [
        '/usr/bin/python3',
        '/usr/local/bin/python3',
        '/opt/homebrew/bin/python3',
        'python3',
        'python'
    ];
    
    $output = null;
    foreach ($python_paths as $python) {
        $command = $python . " " . $script_path . " " . $titolo . " " . $stile_arg . " 2>&1";
        $temp_output = shell_exec($command);
        
        // se ha funzionato e non dà errore di comando non trovato o file mancante, teniamo questo output
        if ($temp_output !== null && !preg_match('/(command not found|sh: line)/i', $temp_output)) {
            $output = $temp_output;
            break;
        }
    }
    
    // 7. Controlliamo se la risposta è vuota (c'è stato un problema imprevisto)
    if (trim($output) === "") {
        echo "Errore nell'esecuzione dello script AI. Assicurati che Python sia installato e configurato.";
    } else {
        // 8. Se tutto è andato bene, prepariamo il testo per essere mostrato in HTML
        // htmlspecialchars: converte eventuali caratteri speciali (<, >) per evitare attacchi XSS
        // nl2br: trasforma gli "a capo" testuali nel tag HTML <br>
        // trim: rimuove gli spazi vuoti all'inizio e alla fine
        echo nl2br(htmlspecialchars(trim($output)));
    }
} else {
    // Se qualcuno chiama questa pagina senza specificare il titolo, mostriamo un errore
    echo "Titolo non fornito.";
}
?>
