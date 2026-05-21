# 📚 E-Book & Co. - La tua libreria ibrida

Piattaforma e-commerce sviluppata per **LibriDigitali s.r.l.** che permette l'acquisto di libri sia in formato digitale (**eBook**) che fisico (**cartaceo**), arricchita con funzionalità all'avanguardia basate sull'**Intelligenza Artificiale**.

---

## 🚀 Funzionalità Principali

### 🛒 E-Commerce & Logica Ibrida
* **Catalogo Dinamico**: Visualizzazione di tutti i libri estratti in tempo reale dal database tramite query SQL performanti, con barra di ricerca integrata in homepage (`index.php` / `index-logged.php`).
* **Dettaglio Prodotto (`prodotto.php`)**: Pagina specifica con indicazione di prezzo, formato (fisico o digitale), disponibilità e gestione scorte dinamica.
* **Promo Bundle Ibrido**: Se l'utente visualizza un libro cartaceo, può aggiungere con un semplice switch l'eBook digitale immediato con un piccolo sovrapprezzo (+2,00€). L'articolo viene inserito nel carrello con formato `ibrido`.
* **Carrello Persistente su Database (`carrello.php`)**: 
  * A differenza delle sessioni temporanee, il carrello è memorizzato su tabella DB (`Carrello`) per mantenere i prodotti salvati tra le varie sessioni.
  * Logica differenziata per formato: per i libri fisici/ibridi l'utente può inserire la quantità desiderata in base alla reale disponibilità a magazzino; per gli eBook la quantità è bloccata ad 1 per evitare acquisti ridondanti dello stesso file.
  * Calcolo in tempo reale di totali e subtotali.
* **Checkout Gestito via Transazioni**: Transazione SQL sicura che crea la testata dell'ordine (`Ordini`), inserisce le righe in `Dettagli_Ordine`, riduce lo stock in `Prodotti` (solo per copie fisiche) ed infine svuota il carrello dell'utente. Se le scorte si esauriscono contemporaneamente per l'acquisto di un altro utente, viene eseguito il rollback automatico.
* **Area Personale (`profilo.php`)**: 
  * Storico ordini completo con data dell'acquisto, spesa complessiva e visualizzazione dei dettagli degli ordini.
  * **Download eBook**: Per gli ordini contenenti eBook o bundle ibridi, è disponibile un pulsante per il download del file demo PDF (`assets/ebook_demo.pdf`).
  * Aggiornamento del profilo (Nome ed Email) con controlli anti-duplicazione email gestiti tramite transazione e blocco `FOR UPDATE`.

### 🤖 Intelligenza Artificiale (Groq API + Llama 3.3)
La piattaforma integra avanzate funzionalità generative tramite le API di **Groq** usando il modello **Llama 3.3 (70B-Versatile)**:
* **Leo, l'Assistente Virtuale Contestuale (`includes/chatbot_widget.php` + `chatbot.php`)**: 
  * Un widget di chat asincrono (AJAX) fluttuante presente in ogni pagina del sito per assistere il cliente.
  * Dispone di **memoria a breve termine** (cronologia degli ultimi 10 messaggi salvata in sessione) per mantenere il filo del discorso.
  * Riceve automaticamente dal DB il catalogo aggiornato dei prodotti per poter raccomandare i libri con precisione commerciale e prezzi reali.
* **Smart Box Trama Generativa (`genera_trama.php` + `ai_plot.py`)**: 
  * Genera trame in tempo reale basate sul titolo del libro in 5 stili personalizzabili selezionabili dal dettaglio del prodotto:
    * **Trama standard**: Sinossi classica, avvincente e concisa.
    * **Teaser**: Spoiler-free ed emozionante, ideale per incuriosire.
    * **In 3 punti chiave**: Spiegazione schematica in esattamente 3 punti elenco.
    * **Spiegato a un bambino**: Trama descritta con parole semplicissime ed immediate.
    * **Recensione Social**: Recensione simpatica e informale in stile influencer letterario di TikTok/Instagram.
  * Dispone di un'interfaccia asincrona fluida che blocca i clic duplicati e mostra un loader animato.
* **AI Copywriter per Admin (`admin/genera_descrizione.php`)**: 
  * Uno strumento integrato nella dashboard per consentire agli amministratori di generare automaticamente descrizioni accattivanti e commerciali per nuovi libri, inserendole automaticamente nel form di aggiunta o modifica con un clic.

### 💼 Pannello Amministratore (`admin/`)
* **Gestione Catalogo (`admin/index.php`)**: Visualizzazione del catalogo con ricerca, inserimento di nuovi libri (con supporto a caricamento copertina e generazione sinossi via IA) e modifica/eliminazione dei prodotti esistenti.
* **Gestione Clienti (`admin/clienti.php`)**: Elenco di tutti gli utenti registrati con ruolo "user".
* **Dettagli Cliente (`admin/dettagli_cliente.php`)**: Pagina per visualizzare il profilo, lo storico ordini e le statistiche finanziarie (totale ordini e totale speso nell'ultimo anno) di un singolo cliente.

---

## 🛠️ Requisiti Tecnici & Sicurezza

Il progetto è sviluppato seguendo rigorosamente gli standard di sicurezza e robustezza architetturale:
* **Connessione DB**: Connessione centralizzata tramite **PDO** gestita con robusti blocchi `try/catch` per catturare eventuali errori e loggarli senza mostrare dettagli sensibili all'utente.
* **Sicurezza dei Dati (Prevenzione Exploit)**:
  * **Prepared Statements**: Protezione totale da **SQL Injection** tramite query preparate con PDO e disabilitazione dell'emulazione delle prepare.
  * **Sanitizzazione XSS**: Utilizzo sistematico di `htmlspecialchars()` su ogni output testuale renderizzato nelle pagine.
  * **Hashing Password**: Hashing sicuro tramite algoritmo Bcrypt con la funzione nativa `password_hash()`.
  * **Sessioni Protette**: Gestione dei permessi e delle aree private tramite `$_SESSION['ruolo']` (Visitatore / Utente / Admin).
  * **CSRF Protection**: Token crittografici CSRF generati in sessione (`$_SESSION['csrf_token']`) e verificati in tutte le operazioni POST critiche (login, registrazione, carrello, acquisto, modifiche profilo, aggiunta/modifica/eliminazione libri).
  * **Command Injection Safeguard**: Chiamate di sistema allo script Python protette al 100% tramite `escapeshellarg()` per neutralizzare caratteri malevoli nei titoli dei libri.
  * **Brute Force Protection**: Blocco del login dopo 5 tentativi falliti consecutivi per prevenire attacchi a dizionario.

---

## 📂 Architettura del Progetto

La struttura delle cartelle è organizzata in modo pulito ed intuitivo:

```bash
libri-digitali/
├── admin/                     # Dashboard Amministrativa
│   ├── index.php              # Pannello principale (statistiche, CRUD, generazione descrizioni)
│   ├── aggiungi.php           # Form di aggiunta prodotto
│   ├── modifica.php           # Form di modifica prodotto
│   ├── elimina.php            # Script di eliminazione sicura
│   ├── genera_descrizione.php # Chiamata API Groq per descrizioni AI
│   ├── clienti.php            # Lista e gestione dei clienti registrati
│   └── dettagli_cliente.php   # Dettagli del singolo cliente con storico ordini e statistiche di spesa
├── assets/                    # Risorse statiche
│   ├── css/
│   │   └── styles.css         # Stile CSS del portale (Design System, palette, animazioni)
│   ├── img/                   # Immagini statiche (vuota)
│   ├── js/                    # Script JS (vuota)
│   └── ebook_demo.pdf         # File PDF dimostrativo per il download degli eBook
├── auth/                      # Sistema di Autenticazione
│   ├── login.php              # Gestione accesso utenti ed admin (con blocco tentativi bruteforce)
│   ├── register.php           # Registrazione di nuovi profili (con validazione password complessa e CSRF)
│   └── logout.php             # Chiusura sessione utente
├── config/                    # Configurazione e Credenziali
│   ├── db.php                 # Connessione PDO ed elaborazione URL base
│   ├── secrets.php            # Credenziali DB (non caricato su Git)
│   └── groq.php               # Chiave API Groq (non caricato su Git)
├── css/                       # Cartella obsoleta o di riserva (vuota)
├── includes/                  # Componenti Riutilizzabili (Layout)
│   ├── head.php               # Head comune, caricamento font e Bootstrap
│   ├── footer.php             # Footer comune
│   ├── chatbot_widget.php     # Interfaccia grafica ed asincrona di Leo AI (Chatbot)
│   ├── navbar_public.php      # Barra di navigazione per visitatori
│   ├── navbar_user.php        # Barra di navigazione per utenti loggati
│   ├── navbar_admin.php       # Barra di navigazione per amministratori
│   └── select_prodotti.php    # Utility di query per estrazione prodotti (con filtro ricerca)
├── ai_plot.py                 # Script Python standalone per interfacciarsi con Groq
├── chatbot.php                # Endpoint PHP per l'assistente Leo (gestione memoria e catalogo)
├── genera_trama.php           # Bridge PHP sicuro per invocare ai_plot.py
├── carrello.php               # Carrello persistente su DB, checkout simulato e promo bundle ibrido
├── prodotto.php               # Pagina di dettaglio con Smart Box Trama IA e switch Bundle
├── profilo.php                # Area utente con storico ordini, download eBook e modifica profilo
├── index.php                  # Homepage per visitatori non registrati
└── index-logged.php           # Homepage personalizzata per utenti registrati
```

---

## 💾 Struttura del Database

Il database si basa su cinque tabelle relazionali principali:

1. **`Utenti`**: Memorizza i dati dei profili utente.
   * `id` (INT, Primary Key, Auto Increment)
   * `nome` (VARCHAR, nome dell'utente)
   * `email` (VARCHAR, Unique, credenziale di accesso)
   * `password` (VARCHAR, hash della password)
   * `ruolo` (VARCHAR, default 'user', definisce l'accesso ad admin o user standard)

2. **`Prodotti`**: Memorizza il catalogo dei libri.
   * `id` (INT, Primary Key, Auto Increment)
   * `titolo` (VARCHAR, titolo del libro)
   * `descrizione` (TEXT, descrizione commerciale del libro)
   * `prezzo` (DECIMAL, costo di listino)
   * `formato` (VARCHAR, 'fisico' o 'digitale')
   * `copertina` (VARCHAR, link o nome del file dell'immagine)
   * `disponibilita` (INT, quantità disponibile per i libri cartacei)

3. **`Carrello`**: Gestisce gli articoli salvati temporaneamente dagli utenti.
   * `id` (INT, Primary Key, Auto Increment)
   * `id_utente` (INT, Foreign Key collegata a `Utenti`)
   * `id_prodotto` (INT, Foreign Key collegata a `Prodotti`)
   * `quantita` (INT, quantità salvata nel carrello)
   * `formato` (VARCHAR, formato dell'articolo: 'fisico', 'digitale' o 'ibrido')
   * `prezzo` (DECIMAL, prezzo unitario applicato)

4. **`Ordini`**: Registro della testata degli acquisti effettuati.
   * `id` (INT, Primary Key, Auto Increment)
   * `id_utente` (INT, Foreign Key collegata a `Utenti`)
   * `totale_ordine` (DECIMAL, spesa complessiva dell'ordine)
   * `data_ordine` (TIMESTAMP / DATETIME, data e ora della transazione)

5. **`Dettagli_Ordine`**: Dettaglio delle singole righe di ciascun ordine.
   * `id` (INT, Primary Key, Auto Increment)
   * `id_ordine` (INT, Foreign Key collegata a `Ordini`)
   * `id_prodotto` (INT, Foreign Key collegata a `Prodotti`)
   * `quantita` (INT, numero di copie acquistate)
   * `prezzo_unitario` (DECIMAL, prezzo del singolo libro all'acquisto)
   * `formato` (VARCHAR, formato al momento dell'acquisto: 'fisico', 'digitale' o 'ibrido')

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

| Membro | Ruolo Ufficiale | Contributo principale |
|---|---|---|
| **Calogero Lo Presti** | PMO + Fullstack Developer + Database | Apertura, architettura DB, backend, AI, chiusura, transazioni e checkout |
| **Francesca Rinallo** | UX/UI Designer | Design System, palette, componenti CSS, animazioni, layout fluidi |
| **Antonio Marrone** | Frontend Developer + Pagina di Registrazione | HTML/Bootstrap, componenti frontend, register.php e validazioni |
| **Agostino Vaccaro** | Tester & Bug Reporter | Metodologia di test, bug trovati, fix verificati, QA |
