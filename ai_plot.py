import sys
import json
import re
import os
import urllib.request
import urllib.error

def leggi_api_key():
    # leggiamo la chiave api di groq direttamente dal file php cosi non la scriviamo in chiaro qui dentro e non rischiamo di caricarla su git
    config_path = os.path.join(os.path.dirname(__file__), 'config', 'groq.php')
    try:
        with open(config_path, 'r') as f:
            contenuto = f.read()
        # siccome il file è php usiamo una regex al volo per beccare la costante GROQ_API_KEY definita nel define
        match = re.search(r"define\('GROQ_API_KEY',\s*'([^']+)'\)", contenuto)
        if match:
            return match.group(1)
        # se la regex fallisce e non trova niente lanciamo un errore 
        raise ValueError("Chiave GROQ_API_KEY non trovata in config/groq.php")
    except FileNotFoundError:
        # errore se il file non esiste proprio nella cartella config
        raise FileNotFoundError(f"File di configurazione non trovato: {config_path}")

def get_plot(title, stile="normale"):
    # per prima cosa recuperiamo la chiave api usando la funzione sopra e impostiamo l endpoint di groq
    api_key = leggi_api_key()
    url = "https://api.groq.com/openai/v1/chat/completions"
    
    # prepariamo tutti gli header per la richiesta post a groq compreso il token di autorizzazione
    headers = {
        "Authorization": f"Bearer {api_key}",
        "Content-Type": "application/json",
        "User-Agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    }
    
    # in base allo stile selezionato dall utente cambiamo le istruzioni da inviare all ia per farle sputare fuori il testo giusto
    if stile == "spoiler":
        # stile teaser: super accattivante, corto e rigorosamente senza spoiler altrimenti l utente si arrabbia
        prompt_istruzione = f"Genera una trama di massimo 100 parole per il libro '{title}'. Deve essere un teaser molto accattivante e assolutamente senza spoiler per incuriosire"
    elif stile == "3punti":
        # stile riassunto: esattamente tre punti elenco molto chiari per chi va di fretta
        prompt_istruzione = f"Spiega di cosa parla il libro '{title}' in esattamente 3 punti elenco brevissimi ed efficaci che spieghino i temi principali"
    elif stile == "bambini":
        # stile per i piu piccoli: parole super semplici e massimo 80 parole, niente termini difficili
        prompt_istruzione = f"Spiega la trama del libro '{title}' con parole semplicissime come se lo stessi spiegando a un bambino piccolo in massimo 80 parole"
    elif stile == "recensione":
        # stile influencer: recensione simpatica e giovanile, quasi da instagram o tiktok
        prompt_istruzione = f"Scrivi una mini recensione simpatica e scherzosa in stile influencer letterario per il libro '{title}', spiegandone il fulcro e perche va letto in massimo 100 parole"
    else:
        # stile standard: una trama classica avvincente e concisa
        prompt_istruzione = f"Genera una breve, avvincente e concisa trama (massimo 100 parole) per il libro intitolato '{title}'"

    # prompt di sistema per dare un ruolo all ia cosi risponde solo in italiano e senza fare chiacchiere inutili tipo 'ecco a te'
    prompt = f"Sei un assistente virtuale esperto di libri. {prompt_istruzione}. Rispondi solo con il testo richiesto in italiano, senza preamboli, introduzioni o conclusioni."
    
    # prepariamo il pacchetto dati in json indicando il modello llama 3.3 e impostando la temperatura a 0.7 per un po di creatività
    data = {
        "model": "llama-3.3-70b-versatile",
        "messages": [{"role": "user", "content": prompt}],
        "max_tokens": 300,
        "temperature": 0.7
    }
    
    try:
        # convertiamo i dati in formato json e codifichiamo in utf-8 prima di spedire la richiesta post
        req = urllib.request.Request(url, data=json.dumps(data).encode('utf-8'), headers=headers, method='POST')
        
        # inviamo la chiamata e leggiamo la risposta del server
        with urllib.request.urlopen(req, timeout=10) as response:
            result = json.loads(response.read().decode('utf-8'))
            # estraiamo il contenuto del messaggio generato dall ia e lo stampiamo pulito a schermo
            print(result['choices'][0]['message']['content'].strip())
            
    except urllib.error.HTTPError as e:
        # se c è un errore http (es. token scaduto o troppe richieste) stampiamo l errore di groq
        print(f"HTTP Error {e.code}: {e.read().decode('utf-8')}")
    except Exception as e:
        # catturiamo qualsiasi altro problema imprevisto (es. timeout o problemi di rete) e stampiamo il trace per fare debug
        import traceback
        print(f"Errore nella generazione: {e}")
        traceback.print_exc()

# questo blocco serve se lanciamo lo script direttamente da terminale o da php tramite shell_exec
if __name__ == "__main__":
    # verifichiamo che ci sia passato almeno un argomento (il titolo del libro)
    if len(sys.argv) > 1:
        title = sys.argv[1]
        # se viene passato anche lo stile (secondo argomento) lo usiamo, altrimenti mettiamo quello di default normale
        stile = sys.argv[2] if len(sys.argv) > 2 else "normale"
        get_plot(title, stile)
    else:
        # errore se qualcuno prova ad avviarlo senza passargli nessun titolo
        print("Titolo non fornito.")
