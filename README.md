# E-Book & Co. - La tua libreria ibrida

Piattaforma e-commerce sviluppata per **LibriDigitali s.r.l.** che permette l'acquisto di libri sia in formato digitale (eBook) che fisico (cartaceo).

## 🚀 Funzionalità Principali

* **Catalogo Dinamico**: Visualizzazione di tutti i libri estratti dal database tramite query SQL .
* **Dettaglio Prodotto**: Pagina specifica con indicazione di prezzo, formato e disponibilità .
* **Area Utente**:
    * Registrazione e Login sicuro .
    * Gestione del Carrello (logica differenziata per eBook e libri fisici).
    * Checkout simulato e generazione ordini .
    * Storico ordini nel profilo personale .
* **Dashboard Admin**: Accesso riservato per la gestione CRUD (Create, Read, Update, Delete) del catalogo prodotti e monitoraggio degli ordini totali .

## 🛠️ Requisiti Tecnici (Must-Have)

Il progetto è sviluppato rispettando i criteri di sufficienza obbligatori .
* **Connessione DB**: Utilizzo di **PDO** con gestione delle eccezioni (`try/catch`) .
* **Sicurezza**: 
    * Protezione da SQL Injection tramite **Prepared Statements** .
    * Protezione da XSS tramite `htmlspecialchars()` .
    * Hashing delle password con `password_hash()` .
* **Sessioni**: Gestione dei permessi e delle aree protette tramite `$_SESSION['ruolo']`.

## 📂 Struttura del Database

Il database si basa su tre tabelle principali con nomi di colonne standardizzati per il lavoro di team :
1.  **Users**: Gestione utenti (id, email, password, nome, ruolo) .
2.  **Products**: Catalogo libri (id, titolo, descrizione, prezzo, formato, copertina) .
3.  **Orders**: Registro acquisti (id, user_id, product_id, quantita, totale, data_ordine) .

## 👥 Team di Sviluppo

* **PMO (Project Manager)**: Calogero Lo Presti - Coordinamento, Schema DB e Integrazione .
* **Sviluppatore 1**: Gestione Login, Registrazione e Sessioni .
* **Sviluppatore 2**: Homepage pubblica e Dashboard Venditore (CRUD) .
* **Sviluppatore 3**: Logica Carrello, Checkout e Storico Ordini .

---
*Progetto finale sviluppato per il test di programmazione Backend PHP.*