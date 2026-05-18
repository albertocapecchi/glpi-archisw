# Archisw – Plugin GLPI: Summary e Guida alla Migrazione GLPI 11

## Cos'è Archisw

**Archisw** (Apps Structure) è un plugin per GLPI che permette di gestire un **inventario strutturato delle applicazioni software** aziendali. Ogni applicazione viene modellata come radice di un albero gerarchico di *software component*, ognuno dei quali può avere da uno a N livelli di sotto-componenti.

- **Versione attuale**: 3.0.25
- **Compatibilità dichiarata**: GLPI 10.0.0 – 11.0.99
- **Autore**: Eric Feron
- **Licenza**: GPLv2+
- **Homepage**: https://github.com/ericferon/glpi-archisw

---

## Funzionalità principali

### 1. Software Component (`PluginArchiswSwcomponent`)
Classe principale, estende `CommonTreeDropdown` (struttura ad albero nativa GLPI). Ogni componente espone:

| Campo | Descrizione |
|---|---|
| `name` / `shortname` / `completename` | Identificativi del componente |
| `description` / `comment` | Note libere |
| `plugin_archisw_swcomponenttypes_id` | Tipo (Custom Dev, COTS, …) |
| `plugin_archisw_swcomponentstates_id` | Stato (In sviluppo, In produzione, …) |
| `statedate` | Data di validità dello stato |
| `plugin_archisw_swcomponenttechnics_id` | Tecnologia principale (linguaggio, DB, …) |
| `users_id` / `groups_id` | Responsabile tecnico / gruppo |
| `suppliers_id` / `manufacturers_id` | Fornitore / produttore |
| `locations_id` | Ubicazione |
| `version` / `startyear` | Versione e anno di avvio in produzione |
| `plugin_archisw_swcomponentslas_id` | Service Level Agreement |
| `plugin_archisw_swcomponentdbs_id` | Repository dati |
| `plugin_archisw_swcomponentinstances_id` | Tipologia istanze (dev+qa+prod, …) |
| `plugin_archisw_swcomponenttargets_id` | Segmenti utenti target |
| `plugin_archisw_swcomponentlicenses_id` | Tipo di licenza |
| `plugin_archisw_standards_id` | Stato di standardizzazione |
| `address` / `address_qa` | URL produzione / QA |
| `health_check` | Endpoint di health-check |
| `repo` | Repository del codice sorgente |

### 2. Campi personalizzabili (`PluginArchiswConfigsw`)
Il plugin supporta **campi configurabili dinamicamente** sulla tabella principale. Ogni campo è definito da:
- **Tipo dato** (`text`, `boolean`, `date`, `datetime`, `number`, `textarea`, `dropdown`, `tree-dropdown`, `itemlink`)
- **Tipo DB** (VARCHAR, INT, TINYINT, TEXT, …) — la colonna viene creata/rinominata/eliminata via `ALTER TABLE` nei hook `pre_item_add/update/purge`
- **Gruppo** e **allineamento orizzontale** per la visualizzazione nel form
- **Traduzioni** delle etichette (`PluginArchiswLabeltranslation`)

### 3. Associazioni con altri oggetti GLPI (`PluginArchiswSwcomponent_Item`)
Un software component può essere collegato ai seguenti tipi di oggetto GLPI nativi:

`Computer`, `Project`, `ProjectTask`, `User`, `Software`, `SoftwareLicense`, `Group`, `Entity`, `Contract`, `Appliance`, `Printer`, `NetworkEquipment`, `Certificate`, `Database`

Per ogni associazione è possibile specificare un **ruolo** (es. 1-DEV, 3-QA, 5-PROD) tramite `PluginArchiswSwcomponent_ItemRole`.

### 4. Integrazioni con altri plugin
| Plugin | Integrazione |
|---|---|
| `statecheck` | **Prerequisito obbligatorio** — deve essere attivo prima di Archisw |
| `genericobject` | I tipi di oggetto generici vengono registrati automaticamente come tipi associabili |
| `fields` | Aggiunta di tab di campi custom al software component |
| `archimap` | Visualizzazione grafica delle mappe architetturali |
| `accounts` | Associazione con account/contratti |
| `datainjection` | Importazione massiva di software component |

### 5. Integrazione GLPI nativa
- **Impact Analysis**: i software component compaiono nella mappa di impatto GLPI
- **Helpdesk / Ticket**: i software component sono associabili ai ticket; visibili nell'helpdesk
- **Supplier**: tab aggiuntivo nella scheda del fornitore con i suoi software component
- **Profile**: gestione diritti granulare (`plugin_archisw`, `plugin_archisw_configuration`)
- **Massive Actions**: aggiornamento massivo di attributi
- **Multi-entity**: supporto completo all'ereditarietà entità GLPI
- **Notepad**: note interne su ogni componente
- **History**: storico modifiche

### 6. Struttura database (tabelle principali)

| Tabella | Contenuto |
|---|---|
| `glpi_plugin_archisw_swcomponents` | Albero dei software component |
| `glpi_plugin_archisw_swcomponents_items` | Associazioni con oggetti GLPI |
| `glpi_plugin_archisw_swcomponents_itemroles` | Ruoli delle associazioni |
| `glpi_plugin_archisw_configsws` | Definizione campi custom |
| `glpi_plugin_archisw_configswlinks` | Link a tabelle dropdown esterne |
| `glpi_plugin_archisw_configswdatatypes` | Tipi di dato |
| `glpi_plugin_archisw_configswdbfieldtypes` | Tipi SQL dei campi |
| `glpi_plugin_archisw_configswfieldgroups` | Gruppi di campi nel form |
| `glpi_plugin_archisw_labeltranslations` | Traduzioni etichette campi custom |
| `glpi_plugin_archisw_swcomponent*` (lookup) | Tabelle dropdown: state, type, technic, sla, db, instance, target, license, user |

---

## Struttura del codice

```
archisw/
├── setup.php              # Inizializzazione plugin, hook, registrazione classi
├── hook.php               # Hook install/uninstall e hook pre_item_*
├── inc/                   # Classi PHP
│   ├── swcomponent.class.php          # Entità principale (CommonTreeDropdown)
│   ├── swcomponent_item.class.php     # Associazioni con oggetti GLPI
│   ├── swcomponent_itemrole.class.php # Ruoli associazione
│   ├── configsw.class.php             # Configurazione campi custom
│   ├── configswlink.class.php         # Link tabelle dropdown
│   ├── configswdatatype.class.php     # Tipi dato
│   ├── configswdbfieldtype.class.php  # Tipi SQL
│   ├── configswfieldgroup.class.php   # Gruppi campi form
│   ├── configswhalign.class.php       # Allineamento orizzontale
│   ├── labeltranslation.class.php     # Traduzioni etichette
│   ├── profile.class.php              # Gestione profili/diritti
│   ├── menu.class.php                 # Menu Assets
│   ├── configswmenu.class.php         # Menu Configurazione
│   ├── roadmap.class.php              # Roadmap applicazioni
│   ├── standard.class.php             # Stato standardizzazione
│   └── swcomponent{state,type,technic,db,instance,...}.class.php  # Lookup dropdown
├── front/                 # Entry-point HTTP (form + lista per ogni classe)
├── ajax/                  # Endpoint AJAX
├── sql/                   # Script SQL installazione e aggiornamento
│   ├── empty-3.0.5.sql    # Schema completo versione corrente
│   └── update-*.sql       # Migrazioni incrementali
└── locales/               # File di traduzione (.po/.mo)
```

---

## Migrazione da GLPI 10 a GLPI 11

### Contesto
Le ultime versioni del plugin (≥ 3.0.23) dichiarano già compatibilità con GLPI 11 (`~10.0.0 || ~11.0.0`), ma il codice contiene pattern deprecati o rimossi in GLPI 11. Di seguito le aree critiche.

### Riferimento documentazione ufficiale
- Plugin development: https://glpi-developer-documentation.readthedocs.io/en/master/plugins/index.html
- Migration guide GLPI 11: https://glpi-developer-documentation.readthedocs.io/en/master/plugins/updates.html

---

### Problemi noti e aree di intervento

#### 1. API Database deprecate
GLPI 11 ha rimosso o deprecato i metodi legacy di `DBmysql`:

| Pattern GLPI 10 (da sostituire) | Alternativa GLPI 11 |
|---|---|
| `$DB->doQuery($query)` | `$DB->query($query)` oppure `$DB->request([...])` |
| `$DB->numrows($result)` | `$DB->numrows($result)` → `countElementsInTable()` o `iterator_count()` |
| `$DB->fetchAssoc($result)` | `$result->current()` / iteratore foreach |
| Query SQL raw con concatenazione stringhe | `$DB->request()` con array parametri |

Il plugin usa estensivamente `$DB->doQuery()` / `$DB->numrows()` / `$DB->fetchAssoc()` in `setup.php`, `hook.php` e in molte classi `inc/`.

#### 2. `CommonTreeDropdown` e API classi base
Verificare che i metodi override in `PluginArchiswSwcomponent` siano ancora compatibili con la firma di `CommonTreeDropdown` in GLPI 11 (eventuali typehint aggiunti nei metodi genitore).

#### 3. `Plugin::registerClass()` — parametri
In GLPI 11 alcuni parametri di `Plugin::registerClass()` sono stati rinominati o rimossi (es. `helpdesk_visible_types`). Verificare il blocco in `setup.php`.

#### 4. `Plugin::getWebDir()` vs `Plugin::getPhpDir()`
GLPI 11 distingue più nettamente tra path web e filesystem. Verificare le chiamate a `Plugin::getWebDir()`.

#### 5. `GLPI_ROOT` e autoloading
GLPI 11 usa PSR-4 con Composer per l'autoloading. I file `inc/` vengono ancora caricati, ma verificare che il nome delle classi segua la convenzione `PluginArchiswXxx` e che non ci siano `require_once` manuali non necessari.

#### 6. Check prerequisito `statecheck`
Il codice in `plugin_archisw_check_prerequisites()` usa `$DB->doQuery()` raw e `$DB->numRows()`. Deve essere aggiornato con l'API moderna.

#### 7. `$CFG_GLPI['impact_asset_types']`
Verificare che la chiave `impact_asset_types` sia ancora presente e funzionante in GLPI 11.

#### 8. Hook `assign_to_ticket_dropdown` e `assign_to_ticket_itemtype`
Verificare se questi hook sono stati rinominati o rimossi in GLPI 11.

#### 9. Schema SQL
Le tabelle usano `INT(11) UNSIGNED`, `utf8_unicode_ci`, `utf8_general_ci`. In GLPI 11 il charset raccomandato è `utf8mb4`. Valutare uno script di migrazione.

#### 10. `version_compare(GLPI_VERSION, '9.2', 'le')` in `rawSearchOptions()`
Check obsoleto (GLPI 11 è molto superiore a 9.2) — da rimuovere per pulizia.

---

### Checklist di migrazione

- [ ] Sostituire `$DB->doQuery()` con `$DB->query()` o `$DB->request()`
- [ ] Sostituire `$DB->numrows()` / `$DB->fetchAssoc()` con API moderne
- [ ] Verificare firma metodi override di `CommonTreeDropdown`
- [ ] Verificare parametri `Plugin::registerClass()`
- [ ] Verificare hook `assign_to_ticket_dropdown` e `assign_to_ticket_itemtype`
- [ ] Rimuovere `version_compare` contro GLPI 9.2
- [ ] Aggiornare charset SQL a `utf8mb4` se necessario
- [ ] Testare installazione fresh su GLPI 11
- [ ] Testare aggiornamento da GLPI 10 a GLPI 11 con dati esistenti
- [ ] Verificare compatibilità plugin dipendenti (`statecheck`, `fields`, `genericobject`)
