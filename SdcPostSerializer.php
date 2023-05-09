<?php

use Opencontent\Sensor\Api\Values\Post;

class SdcPostSerializer
{
    public function serialize(Post $post, array $userData, array $images, array $files, string $serviceId = "inefficiencies"): array
    {
        $mapMicroMacro = [
            [
                "label" => "Ambiente: Ambiente",
                "value" => "a035E00000OXefTQAT"
            ],
            [
                "label" => "Ambiente: Pulizia Strade",
                "value" => "a035E00000OXefUQAT"
            ],
            [
                "label" => "Ambiente: Igiene sull'abitato",
                "value" => "a035E00000OXefVQAT"
            ],
            [
                "label" => "Ambiente: Animali (topi, cinghiali, cani/gatti, ..)",
                "value" => "a035E00000OXefWQAT"
            ],
            [
                "label" => "Ambiente: Inquinamento acqua (autorizzazioni scarichi fognari)",
                "value" => "a035E00000OXefXQAT"
            ],
            [
                "label" => "Ambiente: Inquinamento aria (autorizzazioni alle emissioni in atmosfera)",
                "value" => "a035E00000OXefYQAT"
            ],
            [
                "label" => "Ambiente: Rifiuti, cassonetti, pulizia",
                "value" => "a035E00000OXefZQAT"
            ],
            [
                "label" => "Ambiente: Rumore, inquinamento acustico da attività  produttive, professionali e commerciali",
                "value" => "a035E00000OXefaQAD"
            ],
            [
                "label" => "Ambiente: Pulizia Caditoie",
                "value" => "a035E00000OXefbQAD"
            ],
            [
                "label" => "Ambiente: Processionarie su aree private",
                "value" => "a035E00000OXefcQAD"
            ],
            [
                "label" => "Ambiente: Alberi su tutto il territorio comunale",
                "value" => "a035E00000OXefdQAD"
            ],
            [
                "label" => "Ambiente: Processionaria su aree pubbliche",
                "value" => "a035E00000OXefeQAD"
            ],
            [
                "label" => "Ambiente: Pulizia Vespasiani",
                "value" => "a035E00000OXeffQAD"
            ],
            [
                "label" => "Ambiente: Manutenzione, riparazioni e pulizia servizi igienici autopulenti",
                "value" => "a035E00000OXefgQAD"
            ],
            [
                "label" => "Ambiente: Manutenzioni pannelli luminosi per attraversamenti pedonali",
                "value" => "a035E00000OXefhQAD"
            ],
            [
                "label" => "Ambiente: Illuminazione Pubblica",
                "value" => "a035E00000OXefiQAD"
            ],
            [
                "label" => "Ambiente: Chiarimenti su progettazione illuminazione pubblica",
                "value" => "a035E00000OXefjQAD"
            ],
            [
                "label" => "Assicurazioni: Assicurazioni",
                "value" => "a035E00000OXefkQAD"
            ],
            [
                "label" => "Assicurazioni: Assicurazioni (richieste danni, denuncia sinistro)",
                "value" => "a035E00000OXeflQAD"
            ],
            [
                "label" => "Attuazione Opere Pubbliche: Attuazione Opere Pubbliche",
                "value" => "a035E00000OXefmQAD"
            ],
            [
                "label" => "Attuazione Opere Pubbliche: Cantieri in corso attuazione opere pubbliche",
                "value" => "a035E00000OXefnQAD"
            ],
            [
                "label" => "Beni e attività culturali: Beni e attività culturali",
                "value" => "a035E00000OXefoQAD"
            ],
            [
                "label" => "Beni e attività culturali: Servizi bibliotecari (prestiti e prenotazioni, accessibilità , catalogo online biGmet, portale biblioteche, iniziative culturali in biblioteca)",
                "value" => "a035E00000OXefpQAD"
            ],
            [
                "label" => "Beni e attività culturali: Servizi museali (bigliettazione, accessibilità , collezioni, mostre organizzate nei musei civici)",
                "value" => "a035E00000OXefqQAD"
            ],
            [
                "label" => "Carta Servizi: Carta Servizi",
                "value" => "a035E00000OXefrQAD"
            ],
            [
                "label" => "Commercio: Commercio",
                "value" => "a035E00000OXefsQAD"
            ],
            [
                "label" => "Commercio: Commercio in sede fissa (esercizi vicinato, medie strutture, grandi strutture)",
                "value" => "a035E00000OXeftQAD"
            ],
            [
                "label" => "Commercio: Pubblici esercizi e attività  extralberghiere",
                "value" => "a035E00000OXefuQAD"
            ],
            [
                "label" => "Commercio: Attività  dei servizi alla persona (acconciatori ed estetisti)",
                "value" => "a035E00000OXefvQAD"
            ],
            [
                "label" => "Commercio: Occupazioni suolo pubblico connesse alla somministrazione e al consumo sul posto",
                "value" => "a035E00000OXefwQAD"
            ],
            [
                "label" => "Commercio: Sportello Unico Attività  Produttive",
                "value" => "a035E00000OXefxQAD"
            ],
            [
                "label" => "Commercio: Mercati coperti, mercati merci varie, fiere",
                "value" => "a035E00000OXefyQAD"
            ],
            [
                "label" => "Commercio: Occupazioni suolo a carattere commerciale (mercatini,esposizione merci, occupazioni suolo temporanee..)",
                "value" => "a035E00000OXefzQAD"
            ],
            [
                "label" => "Drenaggi del suolo: Drenaggi del suolo",
                "value" => "a035E00000OXeg0QAD"
            ],
            [
                "label" => "Drenaggi del suolo: Perdite fognarie  (fognature pubbliche)",
                "value" => "a035E00000OXeg1QAD"
            ],
            [
                "label" => "Drenaggi del suolo: Infiltrazioni dalla strada in intercapedini, cantine, locali sottomessi di proprietà  private",
                "value" => "a035E00000OXeg2QAD"
            ],
            [
                "label" => "Drenaggi del suolo: Spandimenti di acqua su strada da muraglioni",
                "value" => "a035E00000OXeg3QAD"
            ],
            [
                "label" => "Drenaggi del suolo: Allagamenti stradali o di proprietà private a causa scarso drenaggio o caditoie tappate",
                "value" => "a035E00000OXeg4QAD"
            ],
            [
                "label" => "Facility Management: Facility Management",
                "value" => "a035E00000OXeg5QAD"
            ],
            [
                "label" => "Facility Management: Scavi, Tombini Stradali e zone limitrofe",
                "value" => "a035E00000OXeg6QAD"
            ],
            [
                "label" => "Facility Management: Ringhiere e Parapetti",
                "value" => "a035E00000OXeg7QAD"
            ],
            [
                "label" => "Facility Management: Coordinamento verde e sfalci stradali",
                "value" => "a035E00000OXeg8QAD"
            ],
            [
                "label" => "Facility Management: Manutenzione semafori e impianti elettrici",
                "value" => "a035E00000OXeg9QAD"
            ],
            [
                "label" => "Facility Management: Piccole manutenzioni puntuali su creuze",
                "value" => "a035E00000OXegAQAT"
            ],
            [
                "label" => "Facility Management: Lavori Diffusi su Creuze",
                "value" => "a035E00000OXegBQAT"
            ],
            [
                "label" => "Facility Management: Manutenzione muri, muri contenimento",
                "value" => "a035E00000OXegCQAT"
            ],
            [
                "label" => "Facility Management: Manutenzione palazzi storici ed edifici pubblici (e impianti)",
                "value" => "a035E00000OXegDQAT"
            ],
            [
                "label" => "Facility Management: Manutenzione strade, muretti",
                "value" => "a035E00000OXegEQAT"
            ],
            [
                "label" => "Facility Management: Aree verdi grandi, parchi recintati e ville chiuse (sfalci, arredi, rifiuti e pulizia caditoria ..)",
                "value" => "a035E00000OXegFQAT"
            ],
            [
                "label" => "Facility Management: Ritracciamenti segnaletica orizzontale",
                "value" => "a035E00000OXegGQAT"
            ],
            [
                "label" => "Facility Management: Manutenzione dissuasori di sosta (paletti, dossi, cordoli, limitatori velocità",
                "value" => "a035E00000OXegHQAT"
            ],
            [
                "label" => "Facility Management: Manutenzione segnaletica verticale e semafori",
                "value" => "a035E00000OXegIQAT"
            ],
            [
                "label" => "Facility Management: Interventi manutenzione straordinaria Vespasiani (tubi)",
                "value" => "a035E00000OXegJQAT"
            ],
            [
                "label" => "Facility Management: Spazzatura in scarpate",
                "value" => "a035E00000OXegKQAT"
            ],
            [
                "label" => "Facility Management: Eliminazione barriere architettoniche",
                "value" => "a035E00000OXegLQAT"
            ],
            [
                "label" => "Facility Management: Ponteggi e Cantieri Stradali",
                "value" => "a035E00000OXegMQAT"
            ],
            [
                "label" => "Facility Management: Manutenzione ponti, impalcati",
                "value" => "a035E00000OXegNQAT"
            ],
            [
                "label" => "Grandi Eventi UPA: Grandi Eventi UPA",
                "value" => "a035E00000OXegOQAT"
            ],
            [
                "label" => "Grandi Eventi UPA: Organizzazione dei Festival e permessi",
                "value" => "a035E00000OXegPQAT"
            ],
            [
                "label" => "Grandi Eventi UPA: Eventi e Grandi Eventi",
                "value" => "a035E00000OXegQQAT"
            ],
            [
                "label" => "Grandi Eventi UPA: Permessi per Eventi e spettacoli",
                "value" => "a035E00000OXegRQAT"
            ],
            [
                "label" => "Innovazione e Sviluppo economico: Innovazione e Sviluppo economico",
                "value" => "a035E00000OXegSQAT"
            ],
            [
                "label" => "Innovazione e Sviluppo economico: Bandi start-up",
                "value" => "a035E00000OXegTQAT"
            ],
            [
                "label" => "Innovazione e Sviluppo economico: Suggerimenti su progetti innovativi",
                "value" => "a035E00000OXegUQAT"
            ],
            [
                "label" => "Innovazione e Sviluppo economico: Smart City",
                "value" => "a035E00000OXegVQAT"
            ],
            [
                "label" => "Innovazione e Sviluppo economico: Informazioni su progetti europei",
                "value" => "a035E00000OXegWQAT"
            ],
            [
                "label" => "Marketing territoriale e promozione della città: Marketing territoriale e promozione della città",
                "value" => "a035E00000OXegXQAT"
            ],
            [
                "label" => "Marketing territoriale e promozione della città: Programmazione dei teatri e festival",
                "value" => "a035E00000OXegYQAT"
            ],
            [
                "label" => "Marketing territoriale e promozione della città: Portale Visit Genoa",
                "value" => "a035E00000OXegZQAT"
            ],
            [
                "label" => "Marketing territoriale e promozione della città: Sala Dogana (Palazzo Ducale)",
                "value" => "a035E00000OXegaQAD"
            ],
            [
                "label" => "Marketing territoriale e promozione della città: Gemellaggi nazionali e internazionali",
                "value" => "a035E00000OXegbQAD"
            ],
            [
                "label" => "Mobilità: Mobilità",
                "value" => "a035E00000OXegcQAD"
            ],
            [
                "label" => "Mobilità: Proposte installazione specchi parabolici pubblici",
                "value" => "a035E00000OXegdQAD"
            ],
            [
                "label" => "Mobilità: Mobilità  casa-scuola, casa-lavoro",
                "value" => "a035E00000OXegeQAD"
            ],
            [
                "label" => "Mobilità: Incentivi mobilita  sostenibile",
                "value" => "a035E00000OXegfQAD"
            ],
            [
                "label" => "Mobilità: Ricarica mezzi elettrici",
                "value" => "a035E00000OXeggQAD"
            ],
            [
                "label" => "Mobilità: Regolazione (ordinanze x circolazione di una strada, ordinanze cantieri)",
                "value" => "a035E00000OXeghQAD"
            ],
            [
                "label" => "Mobilità: Proposta nuovi semafori o modifica impianti esistenti, richieste nuove installazioni luminose",
                "value" => "a035E00000OXegiQAD"
            ],
            [
                "label" => "Mobilità: Proposta nuova segnaletica strade secondarie e principali",
                "value" => "a035E00000OXegjQAD"
            ],
            [
                "label" => "Mobilità: Parcheggi disabili",
                "value" => "a035E00000OXegkQAD"
            ],
            [
                "label" => "Mobilità: Trasporto pubblico",
                "value" => "a035E00000OXeglQAD"
            ],
            [
                "label" => "Mobilità: Piste ciclabili",
                "value" => "a035E00000OXegmQAD"
            ],
            [
                "label" => "Mobilità: Microcircolazione (monopattini, ...)",
                "value" => "a035E00000OXegnQAD"
            ],
            [
                "label" => "Mobilità: Sharing mobility",
                "value" => "a035E00000OXegoQAD"
            ],
            [
                "label" => "Mobilità: Parcheggi e permessi (blu area, isole azzurre, ZTL)",
                "value" => "a035E00000OXegpQAD"
            ],
            [
                "label" => "Mobilità: Proposte nuovi dissuasori (paletti, dossi, cordoli,limitatori velocità ) ad uso pubblico",
                "value" => "a035E00000OXegqQAD"
            ],
            [
                "label" => "Mobilità: Proposte di modifica viabilità",
                "value" => "a035E00000OXegrQAD"
            ],
            [
                "label" => "Municipi Manutenzioni: Municipi Manutenzioni",
                "value" => "a035E00000OXegsQAD"
            ],
            [
                "label" => "Municipi Manutenzioni: Fontanelle- Idranti",
                "value" => "a035E00000OXegtQAD"
            ],
            [
                "label" => "Municipi Manutenzioni: Orti Urbani assegnazione",
                "value" => "a035E00000OXeguQAD"
            ],
            [
                "label" => "Municipi Manutenzioni: Piccoli giardini aperti e piccole aree verdi (sfalci, arredi,...)",
                "value" => "a035E00000OXegvQAD"
            ],
            [
                "label" => "Municipi Manutenzioni: Interventi manutenzione ordinaria vespasiani e Lavatoi",
                "value" => "a035E00000OXegwQAD"
            ],
            [
                "label" => "Municipi Manutenzioni: Proposte installazione nuovi punti luce",
                "value" => "a035E00000OXegxQAD"
            ],
            [
                "label" => "Municipi Manutenzioni: Ripristino Impianti Elettrici",
                "value" => "a035E00000OXegyQAD"
            ],
            [
                "label" => "Municipi Servizi al Cittadino: Municipi Servizi al Cittadino",
                "value" => "a035E00000OXegzQAD"
            ],
            [
                "label" => "Municipi Servizi al Cittadino: Centri Civici municipali",
                "value" => "a035E00000OXeh0QAD"
            ],
            [
                "label" => "Municipi Servizi al Cittadino: Eventi manifestazioni municipali",
                "value" => "a035E00000OXeh1QAD"
            ],
            [
                "label" => "Municipi Servizi al Cittadino: Assegnazioni locali ad uso associativo di competenza municipale",
                "value" => "a035E00000OXeh2QAD"
            ],
            [
                "label" => "Municipi Servizi al Cittadino: Sportelli del cittadino/Front office municipali",
                "value" => "a035E00000OXeh3QAD"
            ],
            [
                "label" => "Municipi Servizi al Cittadino: Progetti di partecipazione attiva",
                "value" => "a035E00000OXeh4QAD"
            ],
            [
                "label" => "Municipi Servizi al Cittadino: Autorizzazioni occupazioni suolo a fini edili",
                "value" => "a035E00000OXeh5QAD"
            ],
            [
                "label" => "Municipi Servizi al Cittadino: Autorizzazione rotture suolo piccoli utenti",
                "value" => "a035E00000OXeh6QAD"
            ],
            [
                "label" => "Municipi Servizi al Cittadino: Concessione passi carrabili",
                "value" => "a035E00000OXeh7QAD"
            ],
            [
                "label" => "Municipi Servizi al Cittadino: Autorizzazione/nulla osta dissuasori di sosta ad uso privato",
                "value" => "a035E00000OXeh8QAD"
            ],
            [
                "label" => "Municipi Servizi al Cittadino: Attribuzione numerazione civica",
                "value" => "a035E00000OXeh9QAD"
            ],
            [
                "label" => "Municipi Servizi al Cittadino: Concessione di patrocini di competenza municipale",
                "value" => "a035E00000OXehAQAT"
            ],
            [
                "label" => "Municipi Servizi al Cittadino: Installazione di specchi parabolici a servizio di passi carrabili",
                "value" => "a035E00000OXehBQAT"
            ],
            [
                "label" => "Politiche della Casa: Politiche della Casa",
                "value" => "a035E00000OXehYQAT"
            ],
            [
                "label" => "Politiche della Casa: Occupazione abusiva",
                "value" => "a035E00000OXehZQAT"
            ],
            [
                "label" => "Politiche della Casa: Sostegno all'affitto",
                "value" => "a035E00000OXehaQAD"
            ],
            [
                "label" => "Politiche della Casa: Assegnazione alloggi",
                "value" => "a035E00000OXehbQAD"
            ],
            [
                "label" => "Politiche della Casa: Bollettazione canoni affitto",
                "value" => "a035E00000OXehcQAD"
            ],
            [
                "label" => "Politiche della Casa: Emergenza abitativa (mancanza alloggio per sfratto/vendita all'asta, ...)",
                "value" => "a035E00000OXehdQAD"
            ],
            [
                "label" => "Politiche della Casa: Bandi e graduatorie edilizia residenziale pubblica",
                "value" => "a035E00000OXeheQAD"
            ],
            [
                "label" => "Politiche dell'Istruzione: Politiche dell'Istruzione",
                "value" => "a035E00000OXehJQAT"
            ],
            [
                "label" => "Politiche dell'Istruzione: Nidi d'infanzia 0/3",
                "value" => "a035E00000OXehKQAT"
            ],
            [
                "label" => "Politiche dell'Istruzione: Scuole d'infanzia 3/6",
                "value" => "a035E00000OXehLQAT"
            ],
            [
                "label" => "Politiche dell'Istruzione: Iscrizioni Scuola 0/6",
                "value" => "a035E00000OXehMQAT"
            ],
            [
                "label" => "Politiche dell'Istruzione: Diete/mensa scolastiche",
                "value" => "a035E00000OXehNQAT"
            ],
            [
                "label" => "Politiche dell'Istruzione: Mensa Scolastica",
                "value" => "a035E00000OXehOQAT"
            ],
            [
                "label" => "Politiche dell'Istruzione: Iscrizione Ristorazione",
                "value" => "a035E00000OXehPQAT"
            ],
            [
                "label" => "Politiche dell'Istruzione: Domanda Agevolazione Tariffaria Servizi Scolastici",
                "value" => "a035E00000OXehQQAT"
            ],
            [
                "label" => "Politiche dell'Istruzione: Bollettini Servizio Scuola",
                "value" => "a035E00000OXehRQAT"
            ],
            [
                "label" => "Politiche dell'Istruzione: Alunni con disabilità",
                "value" => "a035E00000OXehSQAT"
            ],
            [
                "label" => "Politiche dell'Istruzione: Trasporto Scolastico",
                "value" => "a035E00000OXehTQAT"
            ],
            [
                "label" => "Politiche dell'Istruzione: Borse di Studio",
                "value" => "a035E00000OXehUQAT"
            ],
            [
                "label" => "Politiche dell'Istruzione: Manutenzione Edifici Scolastici",
                "value" => "a035E00000OXehVQAT"
            ],
            [
                "label" => "Politiche dell'Istruzione: Aree Esterne Edifici Scolastici",
                "value" => "a035E00000OXehWQAT"
            ],
            [
                "label" => "Politiche dell'Istruzione: Riscaldamento Edifici Scolastici",
                "value" => "a035E00000OXehXQAT"
            ],
            [
                "label" => "Politiche dello Sport: Politiche dello Sport",
                "value" => "a035E00000OXehfQAD"
            ],
            [
                "label" => "Politiche dello Sport: Problematiche strutturali di Impianti Sportivi (sicurezza, perdite, allagamenti)",
                "value" => "a035E00000OXehgQAD"
            ],
            [
                "label" => "Politiche dello Sport: Richieste relative ad attivita sportive di genitori con figli disabili",
                "value" => "a035E00000OXehhQAD"
            ],
            [
                "label" => "Politiche Sociali: Politiche Sociali",
                "value" => "a035E00000OXehCQAT"
            ],
            [
                "label" => "Politiche Sociali: Problematiche sociali relativi a minori",
                "value" => "a035E00000OXehDQAT"
            ],
            [
                "label" => "Politiche Sociali: Problematiche sociali relativi a adulti",
                "value" => "a035E00000OXehEQAT"
            ],
            [
                "label" => "Politiche Sociali: Problematiche sociali relativi a anziani",
                "value" => "a035E00000OXehFQAT"
            ],
            [
                "label" => "Politiche Sociali: Problematiche sociali relativi a disabili",
                "value" => "a035E00000OXehGQAT"
            ],
            [
                "label" => "Politiche Sociali: Problematiche sociali relativi a migranti",
                "value" => "a035E00000OXehHQAT"
            ],
            [
                "label" => "Politiche Sociali: Problematiche per CST",
                "value" => "a035E00000OXehIQAT"
            ],
            [
                "label" => "Polizia Locale: Polizia Locale",
                "value" => "a035E00000OXehiQAD"
            ],
            [
                "label" => "Polizia Locale: Videosorveglianza",
                "value" => "a035E00000OXehjQAD"
            ],
            [
                "label" => "Polizia Locale: Informazioni su Sanzioni/Verbali",
                "value" => "a035E00000OXehkQAD"
            ],
            [
                "label" => "Polizia Locale: Veicoli abbandonati",
                "value" => "a035E00000OXehlQAD"
            ],
            [
                "label" => "Polizia Locale: Oggetti smarriti",
                "value" => "a035E00000OXehmQAD"
            ],
            [
                "label" => "Polizia Locale: Problemi alla circolazione/traffico/soste vietate",
                "value" => "a035E00000OXehnQAD"
            ],
            [
                "label" => "Polizia Locale: Animali vaganti pericolosi (cinghiali, serpenti, ..)",
                "value" => "a035E00000OXehoQAD"
            ],
            [
                "label" => "Polizia Locale: Movida/chiasso",
                "value" => "a035E00000OXehpQAD"
            ],
            [
                "label" => "Polizia Locale: Degrado (aspetti relativi alla sicurezza)",
                "value" => "a035E00000OXehqQAD"
            ],
            [
                "label" => "Polizia Locale: Pericolo",
                "value" => "a035E00000OXehrQAD"
            ],
            [
                "label" => "Polizia Locale: Problematiche e norme COVID",
                "value" => "a035E00000OXehsQAD"
            ],
            [
                "label" => "Polizia Locale: Situazioni pericolose strade",
                "value" => "a035E00000OXehtQAD"
            ],
            [
                "label" => "Progettazione: Progettazione",
                "value" => "a035E00000OXehuQAD"
            ],
            [
                "label" => "Progettazione: Stato della progettazione di un'opera pubblica",
                "value" => "a035E00000OXehvQAD"
            ],
            [
                "label" => "Progetti per la Città: Progetti per la Città",
                "value" => "a035E00000OXehwQAD"
            ],
            [
                "label" => "Progetti per la Città: Idrogeologia (versanti di vallate in frana, perizie geologiche/sismiche)",
                "value" => "a035E00000OXehxQAD"
            ],
            [
                "label" => "Progetti per la Città: Opere infrastrutturali strategiche (Gronda, Terzo valico, Nodo ferroviario)",
                "value" => "a035E00000OXehyQAD"
            ],
            [
                "label" => "Rigenerazione urbana, Urban Center e Centro Storico: Rigenerazione urbana, Urban Center e Centro Storico",
                "value" => "a035E00000OXehzQAD"
            ],
            [
                "label" => "Rigenerazione urbana, Urban Center e Centro Storico: Rigenerazione urbana",
                "value" => "a035E00000OXei0QAD"
            ],
            [
                "label" => "Rigenerazione urbana, Urban Center e Centro Storico: Coordinamento attuazioni urbanistiche",
                "value" => "a035E00000OXei1QAD"
            ],
            [
                "label" => "Riqualificazione Urbana: Riqualificazione Urbana",
                "value" => "a035E00000OXei2QAD"
            ],
            [
                "label" => "Riqualificazione Urbana: Cantieri in corso riqualificazione urbana",
                "value" => "a035E00000OXei3QAD"
            ],
            [
                "label" => "Rivi: Rivi",
                "value" => "a035E00000OXei4QAD"
            ],
            [
                "label" => "Rivi: Vegetazione negli alvei dei torrenti",
                "value" => "a035E00000OXei5QAD"
            ],
            [
                "label" => "Rivi: Richiesta manutenzione straordinaria dei rivi (ammaloramento argini, sovralluvionamento, ...)",
                "value" => "a035E00000OXei6QAD"
            ],
            [
                "label" => "Scelte Strategiche: Scelte Strategiche",
                "value" => "a035E00000OXei7QAD"
            ],
            [
                "label" => "Servizi civici: Servizi civici",
                "value" => "a035E00000OXei8QAD"
            ],
            [
                "label" => "Servizi civici: Cimiteri - manutenzioni (verde, struttura, pulizie)",
                "value" => "a035E00000OXei9QAD"
            ],
            [
                "label" => "Servizi civici: Servizi cimiteriali (ricerche, riesumazioni, acquisti, informazioni)",
                "value" => "a035E00000OXeiAQAT"
            ],
            [
                "label" => "Servizi civici: Anagrafe - accesso ai servizi (prenotazione, orari, ...)",
                "value" => "a035E00000OXeiBQAT"
            ],
            [
                "label" => "Servizi civici: Anagrafe - Residenza (variazioni, cancellazioni, ...)",
                "value" => "a035E00000OXeiCQAT"
            ],
            [
                "label" => "Servizi civici: Anagrafe - servizi convenzionati per certificati (edicole, ACI)",
                "value" => "a035E00000OXeiDQAT"
            ],
            [
                "label" => "Servizi civici: Anagrafe - Certificati (residenza, stato famiglia, notorietà )",
                "value" => "a035E00000OXeiEQAT"
            ],
            [
                "label" => "Servizi civici: Elettorale - rilascio tessere elettorali",
                "value" => "a035E00000OXeiFQAT"
            ],
            [
                "label" => "Servizi civici: Stato civile - certificati (nascita, matrimonio, morte)",
                "value" => "a035E00000OXeiGQAT"
            ],
            [
                "label" => "Servizi civici: Stato civile - ricerche storiche",
                "value" => "a035E00000OXeiHQAT"
            ],
            [
                "label" => "Servizi Finanziari: Servizi Finanziari",
                "value" => "a035E00000OXeejQAD"
            ],
            [
                "label" => "Sistemi Informativi: Sistemi Informativi",
                "value" => "a035E00000OXeiIQAT"
            ],
            [
                "label" => "Sistemi Informativi: Geoportale",
                "value" => "a035E00000OXeiJQAT"
            ],
            [
                "label" => "Sistemi Informativi: Monic@ (autorizzazione occupazione/rottura suolo)",
                "value" => "a035E00000OXeiKQAT"
            ],
            [
                "label" => "Sistemi Informativi: Servizio online di certificati anagrafici e verifiche timbro digitale",
                "value" => "a035E00000OXeiLQAT"
            ],
            [
                "label" => "Sistemi Informativi: Piattaforma di autenticazione ai servizi del Comune",
                "value" => "a035E00000OXeiMQAT"
            ],
            [
                "label" => "Sistemi Informativi: Piattaforma per esposizione di appalti di Gara e Contratti",
                "value" => "a035E00000OXeiNQAT"
            ],
            [
                "label" => "Sistemi Informativi: Problemi tecnici/informatici sui pagamenti online PagoPA",
                "value" => "a035E00000OXeiOQAT"
            ],
            [
                "label" => "Sistemi Informativi: Fascicolo del cittadino",
                "value" => "a035E00000OXeiPQAT"
            ],
            [
                "label" => "Sistemi Informativi: Portale iscrizione ai servizi di allerta meteo, allerta sosta, allerta acqua",
                "value" => "a035E00000OXeiQQAT"
            ],
            [
                "label" => "Sistemi Informativi: Servizi sms del Comune di Genova",
                "value" => "a035E00000OXeiRQAT"
            ],
            [
                "label" => "Sistemi Informativi: Albo pretorio on line",
                "value" => "a035E00000OXeiSQAT"
            ],
            [
                "label" => "Sistemi Informativi: Problemi informatici consultazione storico delibere",
                "value" => "a035E00000OXeiTQAT"
            ],
            [
                "label" => "Sistemi Informativi: Sito Web Istituzionale",
                "value" => "a035E00000OXeiUQAT"
            ],
            [
                "label" => "Sistemi Informativi: SegnalaCi",
                "value" => "a035E00000OXeiVQAT"
            ],
            [
                "label" => "Sistemi Informativi: Reti Wifi",
                "value" => "a035E00000OXeiWQAT"
            ],
            [
                "label" => "Sistemi Informativi: Sportello Telematico",
                "value" => "a035E00000OXeiXQAT"
            ],
            [
                "label" => "Spiagge: Spiagge",
                "value" => "a035E00000OXeiYQAT"
            ],
            [
                "label" => "Spiagge: Spiagge - richieste rimodellamenti, ripascimenti, apertura barre di foce",
                "value" => "a035E00000OXeiZQAT"
            ],
            [
                "label" => "Spiagge: Spiagge - pulizia da alghe o legname trasportato dalle mareggiate",
                "value" => "a035E00000OXeiaQAD"
            ],
            [
                "label" => "Sviluppo del Personale e Formazione: Sviluppo del Personale e Formazione",
                "value" => "a035E00000OXeibQAD"
            ],
            [
                "label" => "Sviluppo del Personale e Formazione: Concorsi",
                "value" => "a035E00000OXeicQAD"
            ],
            [
                "label" => "Tributi: Pagamento occupazione suolo (COSAP)",
                "value" => "a035E00000OXee4QAD"
            ],
            [
                "label" => "Tributi: Pubblicità, affissioni, manifesti, insegne",
                "value" => "a035E00000OXee5QAD"
            ],
            [
                "label" => "Tributi: Accertamenti e intimazioni IMU, TASI, TARI",
                "value" => "a035E00000OXeeBQAT"
            ],
            [
                "label" => "Tributi: IMU, TASI",
                "value" => "a035E00000OXeeCQAT"
            ],
            [
                "label" => "Tributi: TARI (tassa rifiuti)",
                "value" => "a035E00000OXeeDQAT"
            ],
            [
                "label" => "Tributi: Tributi",
                "value" => "a035E00000OXeidQAD"
            ],
            [
                "label" => "Tributi: Pagamento passo carrabile",
                "value" => "a035E00000OXeieQAD"
            ],
            [
                "label" => "Turismo: Turismo",
                "value" => "a035E00000OXeeEQAT"
            ],
            [
                "label" => "Turismo: Itinerari Turistici",
                "value" => "a035E00000OXeeFQAT"
            ],
            [
                "label" => "Turismo: Strutture Ricettive",
                "value" => "a035E00000OXeeGQAT"
            ],
            [
                "label" => "Turismo: Tassa di Soggiorno",
                "value" => "a035E00000OXeeHQAT"
            ],
            [
                "label" => "Turismo: Prodotti e Servizi turistici/Portale Genova City Pass",
                "value" => "a035E00000OXeeIQAT"
            ],
            [
                "label" => "Turismo: Segnaletica Turistica",
                "value" => "a035E00000OXeeJQAT"
            ],
            [
                "label" => "Turismo: Strutture ricettive non mappate sul Geoportale",
                "value" => "a035E00000OXeeKQAT"
            ],
            [
                "label" => "Urbanistica ed Edilizia Privata: Urbanistica ed Edilizia Privata",
                "value" => "a035E00000OXeeLQAT"
            ],
            [
                "label" => "Urbanistica ed Edilizia Privata: Segnalazione Lavori in corso o eseguiti non autorizzati",
                "value" => "a035E00000OXeeMQAT"
            ],
            [
                "label" => "Urbanistica ed Edilizia Privata: Segnalazione Cantieri Abbandonati",
                "value" => "a035E00000OXeeNQAT"
            ],
            [
                "label" => "Urbanistica ed Edilizia Privata: Domande su Aree in cui sono previsti Interventi Edilizi",
                "value" => "a035E00000OXeeOQAT"
            ],
            [
                "label" => "Urbanistica ed Edilizia Privata: Autorizzazioni per la realizzazione di manufatti, pergole, verande, impianti fissi, insegne, aperture di finestre, piastrelle terrazzi, etc.",
                "value" => "a035E00000OXeePQAT"
            ],
            [
                "label" => "Urbanistica ed Edilizia Privata: Nuova normativa Edilizia (bonus 110%, bonus facciate, etc.)",
                "value" => "a035E00000OXeeQQAT"
            ],
            [
                "label" => "Urbanistica ed Edilizia Privata: Insediamento di un attività  commerciale in un'area",
                "value" => "a035E00000OXeeRQAT"
            ],
            [
                "label" => "Urbanistica ed Edilizia Privata: Cambio di destinazione d'uso o uso consentito di un immobile",
                "value" => "a035E00000OXeeSQAT"
            ],
            [
                "label" => "Urbanistica ed Edilizia Privata: Oneri di Urbanizzazione",
                "value" => "a035E00000OXeeTQAT"
            ],
            [
                "label" => "Urbanistica ed Edilizia Privata: Aree Edificabili",
                "value" => "a035E00000OXeeUQAT"
            ],
            [
                "label" => "Urbanistica ed Edilizia Privata: Regolarità Edilizia di una abitazione",
                "value" => "a035E00000OXeeVQAT"
            ],
            [
                "label" => "Urbanistica ed Edilizia Privata: Verifica di un immobile in area esondabile o franosa",
                "value" => "a035E00000OXeeWQAT"
            ],
            [
                "label" => "Urbanistica ed Edilizia Privata: Svincolo posto auto asservito",
                "value" => "a035E00000OXeeXQAT"
            ],
            [
                "label" => "Urbanistica ed Edilizia Privata: Compatibilità  con il PUC per apertura officine, carrozzerie, altre attività  industriali o artigianali",
                "value" => "a035E00000OXeeYQAT"
            ],
            [
                "label" => "Urbanistica ed Edilizia Privata: Visure Progetti e Condoni",
                "value" => "a035E00000OXeeZQAT"
            ],
            [
                "label" => "Urbanistica ed Edilizia Privata: Certificati di Destinazione Urbanistica (CDU)",
                "value" => "a035E00000OXeeaQAD"
            ],
            [
                "label" => "Urbanistica ed Edilizia Privata: Eliminazione di un albero nel proprio giardino",
                "value" => "a035E00000OXeebQAD"
            ],
            [
                "label" => "Urbanistica ed Edilizia Privata: Domanda su immobile in area soggetta a tutela paesaggistica e su quali opere non sono soggette ad autorizzazione paesaggistica",
                "value" => "a035E00000OXeecQAD"
            ],
            [
                "label" => "Urbanistica ed Edilizia Privata: Cambiamento di colore ai prospetti di un edificio",
                "value" => "a035E00000OXeedQAD"
            ],
            [
                "label" => "Urbanistica ed Edilizia Privata: Riapertura o Rettifica Condoni",
                "value" => "a035E00000OXeeeQAD"
            ],
            [
                "label" => "Valorizzazione Patrimonio e Demanio Marittimo: Valorizzazione Patrimonio e Demanio Marittimo",
                "value" => "a035E00000OXeefQAD"
            ],
            [
                "label" => "Valorizzazione Patrimonio e Demanio Marittimo: Domanda di un appartenenza o meno di un bene al comune di Genova (solo edifici, strade)",
                "value" => "a035E00000OXeegQAD"
            ],
            [
                "label" => "Valorizzazione Patrimonio e Demanio Marittimo: Segnalazioni su Demanio Marittimo da Voltri a Vesima",
                "value" => "a035E00000OXeehQAD"
            ],
            [
                "label" => "Valorizzazione Patrimonio e Demanio Marittimo: Segnalazioni su Demanio Marittimo da Punta Vagno a Nervi",
                "value" => "a035E00000OXeeiQAD"
            ]
        ];

        $mapMicroMacroHash = array_combine(
            array_column($mapMicroMacro, 'label'),
            array_column($mapMicroMacro, 'value')
        );

        $microMacro = null;
        if ($post->categories > 0){
            $category = $post->categories[0];
            if ($category->parent == 0){
                $microMacro = "{$category->name}: {$category->name}";
            }else{
                $parentCategory = OpenPaSensorRepository::instance()->getCategoryService()->loadCategory($category->parent);
                $microMacro = "{$parentCategory->name}: {$category->name}";
            }
        }

        $missing = [
            'submitted_at' => $post->published->format('c'),
            'created_at' => $post->published->format('c'),
            'modified_at' => $post->modified->format('c'),
            'pdf_link' => '/sensor/posts/' . $post->id . '/pdf', // dominio?,
            'sensor_category' => $post->categories > 0 ? $post->categories[0]->name : null,
            'sensor_area' => $post->areas > 0 ? $post->areas[0]->name : null,
            'id_v3' => $post->id,
            'uuid_v3' => $post->uuid,
        ];

        $data = [
            "service" => $serviceId,
            "status" => "1900",
            "data" => [
                "applicant" => [
                    "data" => [
                        "email_address" => $userData['email'],
                        "phone_number" => $userData['cellulare'],
                        "completename" => [
                            "data" => [
                                "name" => $userData['nome'],
                                "surname" => $userData['cognome'],
                            ]
                        ],
                        "fiscal_code" => [
                            "data" => [
                                "fiscal_code" => $userData['codice_fiscale'],
                            ]
                        ],
                        "person_identifier" => $userData['codice_fiscale'],
                    ]
                ],
//                "type" => "70cbba61-47e4-4d85-98bf-03e4817cf272",
                "details" => $post->description,
                "subject" => $post->subject,
                "_meta" => $missing,
            ],
        ];

        if ($microMacro){
            $data["micromacrocategory.label"] = $microMacro;
            $data["micromacrocategory.value"] = $mapMicroMacroHash[$microMacro] ?? '';
        }

        if (!empty($images)){
            $data["data"]["images"] = $images;
        }
        if (!empty($files)){
            $data["data"]["docs"] = $files;
        }
        if ($post->geoLocation instanceof Post\Field\GeoLocation){
            $data["data"]["address"] = [
                "lat" => $post->geoLocation->latitude,
                "lon" => $post->geoLocation->longitude,
                "display_name" => $post->geoLocation->address,
            ];
        }

        return $data;
    }
}