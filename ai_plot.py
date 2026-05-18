import sys
import json
import re
import os
import urllib.request
import urllib.error

def leggi_api_key():
    # Leggiamo la chiave API dal file di configurazione PHP (config/groq.php)
    # In questo modo la chiave NON è scritta direttamente nel codice e può essere tenuta fuori da Git
    config_path = os.path.join(os.path.dirname(__file__), 'config', 'groq.php')
    try:
        with open(config_path, 'r') as f:
            contenuto = f.read()
        # Usiamo una regex per estrarre il valore della costante GROQ_API_KEY dal file PHP
        match = re.search(r"define\('GROQ_API_KEY',\s*'([^']+)'\)", contenuto)
        if match:
            return match.group(1)
        raise ValueError("Chiave GROQ_API_KEY non trovata in config/groq.php")
    except FileNotFoundError:
        raise FileNotFoundError(f"File di configurazione non trovato: {config_path}")

def get_plot(title):
    # 1. Leggiamo la chiave API dal file di config esterno e definiamo l'URL delle API di Groq
    api_key = leggi_api_key()
    url = "https://api.groq.com/openai/v1/chat/completions"
    
    # 2. Prepariamo gli "headers" (le intestazioni). 
    # Sono informazioni extra inviate con la richiesta per farsi riconoscere dal server.
    headers = {
        "Authorization": f"Bearer {api_key}", # Invia la chiave API per autenticarci
        "Content-Type": "application/json",   # Specifica che invieremo dati in formato JSON
        # Il User-Agent fa credere al server che la richiesta arrivi da un browser normale, 
        # evitando che i sistemi di sicurezza (come Cloudflare) blocchino la richiesta pensando sia un bot.
        "User-Agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    }
    
    # 3. Creiamo il "prompt", ovvero le istruzioni esatte per l'Intelligenza Artificiale.
    prompt = f"Sei un assistente virtuale esperto di libri. Genera una breve, avvincente e concisa trama (massimo 100 parole) per il libro intitolato '{title}'. Rispondi solo con la trama in italiano, senza preamboli, introduzioni o conclusioni."
    
    # 4. Assembliamo il "payload" (il pacchetto di dati da inviare).
    data = {
        "model": "llama-3.3-70b-versatile", # Specifichiamo quale modello IA di Groq vogliamo usare
        "messages": [{"role": "user", "content": prompt}], # Inseriamo la nostra istruzione
        "max_tokens": 300, # Limitiamo la lunghezza della risposta
        "temperature": 0.7 # Regoliamo la "creatività" (0 è molto rigido, 1 è molto creativo)
    }
    
    try:
        # 5. Impacchettiamo la richiesta, convertendo i dati in formato JSON
        req = urllib.request.Request(url, data=json.dumps(data).encode('utf-8'), headers=headers, method='POST')
        
        # 6. Inviamo effettivamente la richiesta e aspettiamo la risposta (timeout massimo 10 secondi)
        with urllib.request.urlopen(req, timeout=10) as response:
            # Leggiamo la risposta, la decodifichiamo da JSON in un dizionario Python
            result = json.loads(response.read().decode('utf-8'))
            
            # 7. Estraiamo solo il testo del messaggio e lo stampiamo a schermo
            print(result['choices'][0]['message']['content'].strip())
            
    except urllib.error.HTTPError as e:
        # Se c'è un errore HTTP (es. 401 Non Autorizzato o 400 Bad Request), lo stampa
        print(f"HTTP Error {e.code}: {e.read().decode('utf-8')}")
    except Exception as e:
        # Se c'è un errore generico (es. connessione saltata), mostra i dettagli
        import traceback
        print(f"Errore nella generazione: {e}")
        traceback.print_exc()

# Questo blocco verifica se lo script è stato eseguito direttamente (non importato)
if __name__ == "__main__":
    # Controlla se abbiamo passato almeno un parametro (il titolo del libro) oltre al nome dello script
    if len(sys.argv) > 1:
        title = sys.argv[1] # Prende il parametro (es. "Harry Potter")
        get_plot(title)     # Avvia la funzione principale
    else:
        print("Titolo non fornito.")
