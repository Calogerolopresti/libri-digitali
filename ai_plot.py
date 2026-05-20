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

def get_plot(title, stile="normale"):
    # 1. Leggiamo la chiave API dal file di config esterno e definiamo l'URL delle API di Groq
    api_key = leggi_api_key()
    url = "https://api.groq.com/openai/v1/chat/completions"
    
    # 2. Prepariamo gli "headers" (le intestazioni)
    headers = {
        "Authorization": f"Bearer {api_key}",
        "Content-Type": "application/json",
        "User-Agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    }
    
    # 3. Creiamo il "prompt" basato sullo stile scelto dall utente
    if stile == "spoiler":
        prompt_istruzione = f"Genera una trama di massimo 100 parole per il libro '{title}'. Deve essere un teaser molto accattivante e assolutamente senza spoiler per incuriosire"
    elif stile == "3punti":
        prompt_istruzione = f"Spiega di cosa parla il libro '{title}' in esattamente 3 punti elenco brevissimi ed efficaci che spieghino i temi principali"
    elif stile == "bambini":
        prompt_istruzione = f"Spiega la trama del libro '{title}' con parole semplicissime come se lo stessi spiegando a un bambino piccolo in massimo 80 parole"
    elif stile == "recensione":
        prompt_istruzione = f"Scrivi una mini recensione simpatica e scherzosa in stile influencer letterario per il libro '{title}', spiegandone il fulcro e perche va letto in massimo 100 parole"
    else:
        prompt_istruzione = f"Genera una breve, avvincente e concisa trama (massimo 100 parole) per il libro intitolato '{title}'"

    prompt = f"Sei un assistente virtuale esperto di libri. {prompt_istruzione}. Rispondi solo con il testo richiesto in italiano, senza preamboli, introduzioni o conclusioni."
    
    # 4. Assembliamo il "payload" (il pacchetto di dati da inviare)
    data = {
        "model": "llama-3.3-70b-versatile",
        "messages": [{"role": "user", "content": prompt}],
        "max_tokens": 300,
        "temperature": 0.7
    }
    
    try:
        # 5. Impacchettiamo la richiesta, convertendo i dati in formato JSON
        req = urllib.request.Request(url, data=json.dumps(data).encode('utf-8'), headers=headers, method='POST')
        
        # 6. Inviamo la richiesta al server
        with urllib.request.urlopen(req, timeout=10) as response:
            result = json.loads(response.read().decode('utf-8'))
            # 7. Estraiamo il messaggio e lo stampiamo a schermo
            print(result['choices'][0]['message']['content'].strip())
            
    except urllib.error.HTTPError as e:
        print(f"HTTP Error {e.code}: {e.read().decode('utf-8')}")
    except Exception as e:
        import traceback
        print(f"Errore nella generazione: {e}")
        traceback.print_exc()

# questo blocco verifica se lo script viene eseguito direttamente
if __name__ == "__main__":
    if len(sys.argv) > 1:
        title = sys.argv[1]
        # leggiamo lo stile se fornito come secondo argomento
        stile = sys.argv[2] if len(sys.argv) > 2 else "normale"
        get_plot(title, stile)
    else:
        print("Titolo non fornito.")
