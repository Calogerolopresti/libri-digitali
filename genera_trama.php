<?php
// controlliamo se nel link get è stato passato il parametro del titolo del libro da cercare
if (isset($_GET['titolo'])) {
    // mettiamo in sicurezza il titolo usando escapeshellarg cosi evitiamo exploit o che qualche hacker provi a fare command injection
    $titolo = escapeshellarg($_GET['titolo']);
    
    // prendiamo lo stile che ci chiede l utente e facciamo un controllo per sicurezza su un array di stili permessi
    $stile = isset($_GET['stile']) ? $_GET['stile'] : 'normale';
    $allowed_styles = ['normale', 'spoiler', '3punti', 'bambini', 'recensione'];
    if (!in_array($stile, $allowed_styles)) {
        // se lo stile cercato non è tra quelli validi rimettiamo quello di default cosi siamo sicuri
        $stile = 'normale';
    }
    $stile_arg = escapeshellarg($stile);
    
    // prendiamo il percorso assoluto dello script python dell ia cosi lo trova di sicuro
    $script_path = escapeshellarg(__DIR__ . '/ai_plot.py');
    
    // facciamo un array con tutti i possibili percorsi di python3 sul sistema (perchè su MAMP le variabili d ambiente sono limitate e a volte non trova il comando semplice)
    $python_paths = [
        '/usr/bin/python3',
        '/usr/local/bin/python3',
        '/opt/homebrew/bin/python3',
        'python3',
        'python'
    ];
    
    $output = null;
    // proviamo a ciclare tutti i percorsi di python per trovare quello installato sul computer
    foreach ($python_paths as $python) {
        // creiamo la stringa del comando passando lo script, il titolo e lo stile e reindirizziamo l errore standard cosi se fallisce lo vediamo
        $command = $python . " " . $script_path . " " . $titolo . " " . $stile_arg . " 2>&1";
        $temp_output = shell_exec($command);
        
        // se il comando ha funzionato senza dare l errore di comando non trovato o sh allora teniamo questo risultato e usciamo dal ciclo
        if ($temp_output !== null && !preg_match('/(command not found|sh: line)/i', $temp_output)) {
            $output = $temp_output;
            break;
        }
    }
    
    // controlliamo se l output che ci torna python è vuoto perche in quel caso cè stato un errore
    if (trim($output) === "") {
        echo "Errore nell'esecuzione dello script AI. Assicurati che Python sia installato e configurato.";
    } else {
        // se è andato tutto liscio puliamo il testo con htmlspecialchars cosi blocchiamo XSS e convertiamo gli a capo in tag br
        echo nl2br(htmlspecialchars(trim($output)));
    }
} else {
    // mostro l errore se qualcuno apre la pagina direttamente senza passargli niente
    echo "Titolo non fornito.";
}
?>
