<?php
// 1. Controlliamo se nella richiesta URL è presente il parametro 'titolo'
if (isset($_GET['titolo'])) {
    // 2. Mettiamo in sicurezza il titolo del libro per evitare attacchi hacker
    // escapeshellarg aggiunge degli apici intorno al testo, impedendo che qualcuno inserisca comandi di sistema
    $titolo = escapeshellarg($_GET['titolo']);
    
    // 3. Troviamo il percorso assoluto dello script Python
    // __DIR__ indica la cartella attuale, a cui aggiungiamo il nome del file python
    $script_path = escapeshellarg(__DIR__ . '/ai_plot.py');
    
    // 4. Prepariamo il comando da eseguire nel terminale.
    // Struttura: python3 percorso/dello/script.py "Titolo del Libro"
    // Il "2>&1" serve a catturare anche eventuali errori stampati dal terminale, oltre al normale output
    $command = "python3 " . $script_path . " " . $titolo . " 2>&1";
    
    // 5. Eseguiamo effettivamente il comando e salviamo quello che risponde in $output
    $output = shell_exec($command);
    
    // 6. Blocco di sicurezza (Fallback)
    // Se python3 non è installato o non viene trovato, proviamo con il comando "python" standard
    if ($output === null || strpos(strtolower($output), 'command not found') !== false) {
        $command = "python " . $script_path . " " . $titolo . " 2>&1";
        $output = shell_exec($command);
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
