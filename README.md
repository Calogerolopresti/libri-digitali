# 📚 E-Book & Co. - La tua libreria ibrida

Piattaforma e-commerce sviluppata per **LibriDigitali s.r.l.** che permette l'acquisto di libri sia in formato digitale (**eBook**) che fisico (**cartaceo**), arricchita con funzionalità all'avanguardia basate sull'**Intelligenza Artificiale**.

---

## 🚀 Funzionalità Principali

### 🛒 E-Commerce & Logica Ibrida
* **Catalogo Dinamico**: Visualizzazione di tutti i libri estratti in tempo reale dal database tramite query SQL performanti.
* **Dettaglio Prodotto**: Pagina specifica con indicazione di prezzo, formato (fisico o digitale), disponibilità e gestione scorte dinamica.
* **Carrello Intelligente (`carrello.php`)**: 
  * Logica differenziata per formato: per i libri fisici l'utente può inserire la quantità desiderata in base alle scorte; per gli eBook la quantità è bloccata ad 1 per evitare acquisti ridondanti dello stesso file.
  * Calcolo in tempo reale di totali e subtotali.
* **Area Personale (`profilo.php`)**: Storico ordini completo con data dell'acquisto, quantità, importo totale speso e dati dell'utente.
* **Checkout Simulato**: Acquisto sicuro con transazione DB per aggiornare le scorte reali in modo sicuro e prevenire la vendita di articoli esauriti.

### 🤖 Intelligenza Artificiale (Groq API + Llama 3.3)
La piattaforma integra avanzate funzionalità generative tramite le API di **Groq** usando il modello **Llama 3.3 (70B-Versatile)**:
* **Leo, l'Assistente Virtuale Contestuale (`includes/chatbot_widget.php` + `chatbot.php`)**: 
  * Un widget flessibile ad ogni pagina del sito per assistere il cliente.
  * Dispone di **memoria a breve termine** (cronologia degli ultimi 10 messaggi salvata in sessione) per mantenere il filo del discorso.
  * Riceve automaticamente dal DB il catalogo aggiornato dei prodotti per poter raccomandare i libri con precisione commerciale.
* **Smart Box Trama Generativa (`genera_trama.php` + `ai_plot.py`)**: 
  * Genera trame in tempo reale basate sul titolo del libro in 4 stili personalizzabili:
    * **Spoiler**: Teaser avvincente di 100 parole, ideale per incuriosire senza rovinare il finale.
    * **3 Punti**: Spiegazione schematica in esattamente 3 punti elenco.
    * **Bambini**: Trama descritta con parole semplicissime ed immediate.
    * **Recensione**: Recensione simpatica e informale in stile influencer letterario di TikTok/Instagram.
  * Dispone di un'interfaccia asincrona fluida che blocca i clic duplicati e mostra un loader animato.
* **AI Copywriter per Admin (`admin/genera_descrizione.php`)**: 
  * Uno strumento integrato nella dashboard per consentire agli amministratori di generare automaticamente descrizioni accattivanti e commerciali per nuovi libri, inserendole automaticamente nel form di aggiunta o modifica con un clic.

---

## 🛠️ Requisiti Tecnici & Sicurezza (Must-Have)

Il progetto è sviluppato seguendo rigorosamente gli standard di sicurezza e robustezza architetturale:
* **Connessione DB**: Connessione centralizzata tramite **PDO** gestita con robusti blocchi `try/catch` per catturare eventuali errori e loggarli senza mostrare dettagli sensibili all'utente.
* **Sicurezza dei Dati (Prevenzione Exploit)**:
  * **Prepared Statements**: Protezione totale da **SQL Injection** tramite query preparate con PDO e disabilitazione dell'emulazione delle prepare.
  * **Sanitizzazione XSS**: Utilizzo sistematico di `htmlspecialchars()` su ogni output testuale renderizzato nelle pagine.
  * **Hashing Password**: Hashing sicuro tramite algoritmo Bcrypt con la funzione nativa `password_hash()`.
  * **Sessioni Protette**: Gestione dei permessi e delle aree private tramite `$_SESSION['ruolo']` (Visitatore / Utente / Admin).
  * **Command Injection Safeguard**: Chiamate di sistema allo script Python protette al 100% tramite `escapeshellarg()` per neutralizzare caratteri malevoli nei titoli dei libri.

---

## 📂 Architettura del Progetto

La struttura delle cartelle è organizzata in modo pulito ed intuitivo per facilitare il lavoro di squadra:

```bash
libri-digitali/
├── admin/                     # Dashboard Amministrativa
│   ├── index.php              # Pannello principale (statistiche, CRUD, generazione descrizioni)
│   ├── aggiungi.php           # Form di aggiunta prodotto
│   ├── modifica.php           # Form di modifica prodotto
│   ├── elimina.php            # Script di eliminazione sicura
│   └── genera_descrizione.php # Chiamata API Groq per descrizioni AI
├── auth/                      # Sistema di Autenticazione
│   ├── login.php              # Gestione accesso utenti ed admin
│   ├── register.php           # Registrazione di nuovi profili
│   └── logout.php             # Chiusura sessione utente
├── config/                    # Configurazione e Credenziali
│   ├── db.php                 # Connessione PDO ed elaborazione URL base
│   ├── secrets.php            # Credenziali DB (non caricato su Git)
│   └── groq.php               # Chiave API Groq (non caricato su Git)
├── css/                       # Fogli di Stile
│   └── stile.css              # Stile CSS del portale (Design System, palette, animazioni)
├── includes/                  # Componenti Riutilizzabili (Layout)
│   ├── head.php               # Head comune, caricamento font e Bootstrap
│   ├── footer.php             # Footer comune
│   ├── chatbot_widget.php     # Interfaccia grafica ed asincrona di Leo AI
│   ├── navbar_public.php      # Barra di navigazione per visitatori
│   ├── navbar_user.php        # Barra di navigazione per utenti loggati
│   ├── navbar_admin.php       # Barra di navigazione per amministratori
│   └── select_prodotti.php    # Utility di query per estrazione prodotti
├── ai_plot.py                 # Script Python standalone per interfacciarsi con Groq
├── chatbot.php                # Endpoint PHP per l'assistente Leo (gestione memoria e catalogo)
├── genera_trama.php           # Bridge PHP sicuro per invocare ai_plot.py
├── carrello.php               # Carrello interattivo con logica differenziata e checkout
├── prodotto.php               # Pagina di dettaglio con Smart Box Trama IA
├── profilo.php                # Area utente con storico ordini
├── index.php                  # Homepage per visitatori non registrati
└── index-logged.php           # Homepage personalizzata per utenti registrati
```

---

## 💾 Struttura del Database

Il database si basa su tre tabelle relazionali principali:

1. **`users`**: Memorizza i dati dei profili utente.
   * `id` (INT, Primary Key, Auto Increment)
   * `nome` (VARCHAR, nome dell'utente)
   * `email` (VARCHAR, Unique, credenziale di accesso)
   * `password` (VARCHAR, hash della password)
   * `ruolo` (VARCHAR, default 'user', definisce l'accesso ad admin o user standard)

2. **`products`**: Memorizza il catalogo dei libri.
   * `id` (INT, Primary Key, Auto Increment)
   * `titolo` (VARCHAR, titolo del libro)
   * `descrizione` (TEXT, descrizione commerciale del libro)
   * `prezzo` (DECIMAL, costo di listino)
   * `formato` (VARCHAR, 'cartaceo', 'ebook' o 'ibrido')
   * `copertina` (VARCHAR, link o nome del file dell'immagine)
   * `stock` (INT, quantità disponibile per i libri cartacei)

3. **`orders`**: Registro degli acquisti effettuati.
   * `id` (INT, Primary Key, Auto Increment)
   * `user_id` (INT, Foreign Key collegata a `users`)
   * `product_id` (INT, Foreign Key collegata a `products`)
   * `quantita` (INT, numero di copie acquistate)
   * `totale` (DECIMAL, spesa complessiva dell'ordine)
   * `data_ordine` (TIMESTAMP, momento esatto dell'ordine)

---

## 🛠️ Configurazione e Installazione in Locale

1. **Prerequisiti**:
   * Server locale PHP/MySQL attivo (consigliato **MAMP**, porta default `8888`).
   * **Python 3** installato nel sistema (richiesto per la generazione delle trame con lo script `ai_plot.py`, compatibile nativamente in quanto usa esclusivamente librerie standard preinstallate).

2. **Database Setup**:
   * Creare un database MySQL locale (es. `libridigitali`).
   * Importare lo schema SQL fornito nel file di database.
   * Creare il file `config/secrets.php` inserendo le proprie credenziali:
     ```php
     <?php
     $host = 'localhost';
     $db   = 'nome_tuo_database';
     $user = 'tuo_utente';
     $pass = 'tua_password';
     ```

3. **Configurazione Chiave API Groq**:
   * Creare o aggiornare il file `config/groq.php` inserendo la propria API key di Groq:
     ```php
     <?php
     define('GROQ_API_KEY', 'tua_chiave_gsk_qui');
     ```

4. **Avvio del Progetto**:
   * Copiare la cartella all'interno della root del server locale (es. `htdocs/libri-digitali/`).
   * Accedere da browser all'indirizzo `http://localhost:8888/libri-digitali/`.

---

## 👥 Team di Sviluppo

| Membro | Ruolo Ufficiale | Cosa presenta |
|---|---|---|
| **Calogero Lo Presti** | PMO + Fullstack Developer + Database | Apertura, architettura DB, backend, AI, chiusura |
| **Francesca Rinallo** | UX/UI Designer | Design System, palette, componenti CSS, animazioni |
| **Antonio Marrone** | Frontend Developer + Pagina di Registrazione | HTML/Bootstrap, componenti frontend, register.php |
| **Agostino Vaccare** | Tester & Bug Reporter | Metodologia di test, bug trovati, fix verificati |
