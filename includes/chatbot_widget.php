<!-- ========================================================
     CHATBOT WIDGET - Leo, l'assistente letterario di E-Book & Co.
     Questo widget è incluso nel footer ed è visibile su tutte le pagine.
     La comunicazione con l'IA avviene via AJAX verso chatbot.php
     ======================================================== -->

<?php
// Calcoliamo il percorso assoluto verso chatbot.php usando la costante BASE_URL definita in config/db.php
// In questo modo funziona correttamente da qualsiasi pagina (root, admin, auth, ecc.) indipendentemente dalla struttura delle cartelle o dal web server
$chatbot_path = defined('BASE_URL') ? BASE_URL . '/chatbot.php' : 'chatbot.php';
?>

<style>
    /* --- Pulsante flottante per aprire/chiudere la chat --- */
    #chatbot-toggle {
        position: fixed;
        bottom: 90px; /* sopra il back-to-top button */
        right: 28px;
        z-index: 1050;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #a31d1d, #c0392b);
        color: white;
        border: none;
        box-shadow: 0 4px 20px rgba(163, 29, 29, 0.45);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    #chatbot-toggle:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 28px rgba(163, 29, 29, 0.6);
    }

    /* --- Finestra della chat --- */
    #chatbot-window {
        position: fixed;
        bottom: 160px;
        right: 28px;
        z-index: 1049;
        width: 340px;
        max-height: 480px;
        border-radius: 20px;
        background: #fff;
        box-shadow: 0 12px 40px rgba(0,0,0,0.18);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        transform: scale(0.85) translateY(20px);
        opacity: 0;
        pointer-events: none;
        transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), opacity 0.25s ease;
    }
    #chatbot-window.open {
        transform: scale(1) translateY(0);
        opacity: 1;
        pointer-events: all;
    }

    /* --- Header della chat --- */
    #chatbot-header {
        background: linear-gradient(135deg, #a31d1d, #c0392b);
        color: white;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }
    #chatbot-header .avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }
    #chatbot-header h6 {
        margin: 0;
        font-weight: 700;
        font-size: 0.95rem;
    }
    #chatbot-header small {
        opacity: 0.85;
        font-size: 0.75rem;
    }
    #chatbot-close {
        margin-left: auto;
        background: none;
        border: none;
        color: white;
        opacity: 0.8;
        cursor: pointer;
        font-size: 1.1rem;
        padding: 0;
        line-height: 1;
        transition: opacity 0.2s;
    }
    #chatbot-close:hover { opacity: 1; }

    /* --- Area messaggi --- */
    #chatbot-messages {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        background: #f8fafc;
    }
    #chatbot-messages::-webkit-scrollbar { width: 4px; }
    #chatbot-messages::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }

    /* --- Singolo messaggio --- */
    .chat-msg {
        max-width: 82%;
        padding: 9px 13px;
        border-radius: 16px;
        font-size: 0.875rem;
        line-height: 1.5;
        word-break: break-word;
        animation: msgIn 0.3s ease;
    }
    @keyframes msgIn {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .chat-msg.user {
        align-self: flex-end;
        background: #a31d1d;
        color: white;
        border-bottom-right-radius: 4px;
    }
    .chat-msg.ai {
        align-self: flex-start;
        background: white;
        color: #334155;
        border: 1px solid #e2e8f0;
        border-bottom-left-radius: 4px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }

    /* --- Indicatore di digitazione (pallini animati) --- */
    .chat-msg.typing {
        align-self: flex-start;
        background: white;
        border: 1px solid #e2e8f0;
        padding: 12px 16px;
    }
    .typing-dots { display: flex; gap: 4px; }
    .typing-dots span {
        width: 7px; height: 7px;
        background: #a31d1d;
        border-radius: 50%;
        animation: bounce 1.2s infinite;
    }
    .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
    .typing-dots span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes bounce {
        0%, 60%, 100% { transform: translateY(0); }
        30%            { transform: translateY(-6px); }
    }

    /* --- Input area --- */
    #chatbot-input-area {
        display: flex;
        align-items: center;
        padding: 10px 12px;
        border-top: 1px solid #e2e8f0;
        background: white;
        gap: 8px;
        flex-shrink: 0;
    }
    #chatbot-input {
        flex: 1;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 8px 14px;
        font-size: 0.875rem;
        outline: none;
        background: #f8fafc;
        transition: border-color 0.2s;
        resize: none;
    }
    #chatbot-input:focus { border-color: #a31d1d; background: #fff; }
    #chatbot-send {
        width: 36px; height: 36px;
        border-radius: 50%;
        background: #a31d1d;
        color: white;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        flex-shrink: 0;
        transition: background 0.2s, transform 0.15s;
    }
    #chatbot-send:hover { background: #7a0c0c; transform: scale(1.08); }
    #chatbot-send:disabled { background: #cbd5e1; cursor: not-allowed; transform: none; }

    /* --- Badge notifica sul toggle --- */
    #chatbot-badge {
        position: absolute;
        top: -4px; right: -4px;
        width: 16px; height: 16px;
        background: #22c55e;
        border-radius: 50%;
        border: 2px solid white;
        display: none;
    }
</style>

<!-- Pulsante flottante -->
<button id="chatbot-toggle" title="Chatta con Leo, il tuo assistente letterario" aria-label="Apri chat assistente">
    <i class="fa-solid fa-robot" id="chatbot-toggle-icon"></i>
    <span id="chatbot-badge"></span>
</button>

<!-- Finestra chat -->
<div id="chatbot-window" role="dialog" aria-label="Chat con Leo">
    <!-- Header -->
    <div id="chatbot-header">
        <div class="avatar"><i class="fa-solid fa-robot"></i></div>
        <div>
            <h6>Leo</h6>
            <small>Assistente Letterario · Online</small>
        </div>
        <button id="chatbot-close" aria-label="Chiudi chat"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <!-- Messaggi -->
    <div id="chatbot-messages">
        <!-- Messaggio di benvenuto iniziale -->
        <div class="chat-msg ai">
            Ciao! 👋 Sono <strong>Leo</strong>, il tuo assistente letterario.<br>
            Posso consigliarti libri, aiutarti a scegliere tra eBook e cartaceo o rispondere a qualsiasi domanda sui nostri titoli. Come posso aiutarti?
        </div>
    </div>

    <!-- Input -->
    <div id="chatbot-input-area">
        <input type="text" id="chatbot-input" placeholder="Scrivi un messaggio..." autocomplete="off" maxlength="300">
        <button id="chatbot-send" title="Invia"><i class="fa-solid fa-paper-plane"></i></button>
    </div>
</div>

<script>
(function() {
    // recuperiamo tutti gli elementi html utili per gestire la chat con l utente
    const toggle      = document.getElementById('chatbot-toggle');
    const toggleIcon  = document.getElementById('chatbot-toggle-icon');
    const chatWindow  = document.getElementById('chatbot-window');
    const closeBtn    = document.getElementById('chatbot-close');
    const messages    = document.getElementById('chatbot-messages');
    const input       = document.getElementById('chatbot-input');
    const sendBtn     = document.getElementById('chatbot-send');
    const badge       = document.getElementById('chatbot-badge');
    let isOpen        = false;
    let hasNewMessage = false;

    // recuperiamo il percorso di chatbot.php generato in php in cima al file
    const base = '<?php echo $chatbot_path; ?>';

    // funzione per aprire e chiudere la finestrella della chat in modo fluido
    function toggleChat() {
        isOpen = !isOpen;
        chatWindow.classList.toggle('open', isOpen);
        // cambiamo l iconcina da robot a crocetta in base allo stato
        toggleIcon.className = isOpen ? 'fa-solid fa-xmark' : 'fa-solid fa-robot';
        if (isOpen) {
            // se apriamo la chat nascondiamo il pallino verde di notifica
            badge.style.display = 'none';
            hasNewMessage = false;
            // mettiamo il focus sul campo di testo dopo che la finestra si è aperta
            setTimeout(() => input.focus(), 300);
        }
    }

    toggle.addEventListener('click', toggleChat);
    closeBtn.addEventListener('click', toggleChat);

    // scrolliamo sempre la finestrella dei messaggi fino in fondo cosi si legge l ultimo messaggio inserito
    function scrollBottom() {
        messages.scrollTop = messages.scrollHeight;
    }

    // funzione per buttare dentro la chat un nuovo fumetto (utente o ia)
    function addMessage(text, role) {
        const div = document.createElement('div');
        div.className = 'chat-msg ' + role;
        // sostituiamo gli a capo testuali con i tag br html
        div.innerHTML = text.replace(/\n/g, '<br>');
        messages.appendChild(div);
        scrollBottom();
        return div;
    }

    // creiamo i pallini che saltellano (typing indicator) quando l ia sta elaborando la risposta
    function showTyping() {
        const div = document.createElement('div');
        div.className = 'chat-msg typing';
        div.id = 'typing-indicator';
        div.innerHTML = '<div class="typing-dots"><span></span><span></span><span></span></div>';
        messages.appendChild(div);
        scrollBottom();
    }

    // rimuoviamo il typing indicator prima di mostrare la risposta effettiva di leo
    function hideTyping() {
        const indicator = document.getElementById('typing-indicator');
        if (indicator) indicator.remove();
    }

    // funzione principale per inviare il messaggio dell utente a php via ajax
    function sendMessage() {
        const text = input.value.trim();
        // se il testo è vuoto o il bottone è disabilitato non facciamo niente
        if (!text || sendBtn.disabled) return;

        // aggiungiamo subito il messaggio dell utente alla chat
        addMessage(text, 'user');
        input.value = '';
        // blocchiamo il pulsante di invio per evitare che l utente clicchi piu volte di fila
        sendBtn.disabled = true;

        // mostriamo i tre pallini di attesa
        showTyping();

        // prepariamo i dati del form da inviare via post a php
        const formData = new FormData();
        formData.append('messaggio', text);

        // facciamo partire la fetch asincrona verso il file php
        fetch(base, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                hideTyping();
                if (data.risposta) {
                    addMessage(data.risposta, 'ai');
                    // se la chat è chiusa e arriva una risposta accendiamo il pallino verde di notifica sul toggle
                    if (!isOpen) {
                        badge.style.display = 'block';
                    }
                } else {
                    // se php ci restituisce un errore lo mostriamo nella chat
                    addMessage('⚠️ ' + (data.errore || 'Errore sconosciuto.'), 'ai');
                }
            })
            .catch(() => {
                hideTyping();
                // errore se non cè connessione a internet o se il server è offline
                addMessage('⚠️ Errore di connessione. Riprova tra qualche istante.', 'ai');
            })
            .finally(() => {
                // riabilitiamo il bottone e ridiamo il focus alla tastiera
                sendBtn.disabled = false;
                input.focus();
            });
    }

    // gestiamo l invio premendo il tasto invio da tastiera (senza shift)
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
    sendBtn.addEventListener('click', sendMessage);
})();
</script>
