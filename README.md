# PHP Zotero Web Viewer

This PHP web application fetches entries from the Zotero Web API and displays them on a web page. Users can filter displayed entries using search terms.

## Usage

- Place your Zotero API credentials in the script if needed.
- Open `index.php` in your browser.
- Use the search box to filter entries.

## Requirements

- PHP 7.4 or newer
- Internet connection

## Customization

- Edit `index.php` to change the Zotero API endpoint or display logic.

## Problems

### 2025-08-11

- Autor: 'unknown' if only Editor is avaiable
  - Search term "Vierte Bundeswaldinventur" would show at the end
  - Maybe put the abbreviation of the Editor as author?
- no idea how to implement the special NW-FVA style
- there are still many entries with an author 'unknown'
  - maybe these are not articles but a different entry type
- Fetch PDF if DOI links to a PDF file drectly, not so if it links to a html page
  - in the folder 'G:/zotero_pdfs/alle_pdfs_save/alle_pdfs' all entries after the 1. August 2025 are downloaded papers

## Start the server

- php -S localhost:8000

## Zitierstiel

### Tests

1. Suche: "Monitoring des Zustands von Waldböden in Hessen"

- "Berichte der Sektion Waldökosystemfoschung" (Reihe) auch mit der Namen der Serien und der Nummer des Bandes.

1. Suche: "Effects of Climate and Atmospheric Nitrogen Deposition"

- viele, viele Autoren (mehr als 20)

### Results

- "Ecological Modelling" (ecological-modelling)
  - zu 1. auch mit der Namen der Serien aber keine Band Nummer.
  - zu 2. ALLE Autoren
  - und die Jahreszahl ist nicht in Klammern
- "Journal of Ecology" (journal-of-ecology)
  - Jahreszahl in Klammern
  - zu 2. Viele Autoren, dann nur 20 Autoren
  - zu 1. dafür aber nicht der Serienname
- "Ecology Letters" (ecology-letters)
  - zu 2. max. 6 Autoren sonst et al
  - zu 1. Berichte wird auch ausgegeben, allerdings ohne die Band Nummer
- "Conservation Letters"
  - zu 1. ohne Band Nummer
  - zu 2. Alle Autoren
- "Ecological Entomology"
  - zu 1. ohne Band Nummer
  - zu 2. max 6 Autoren
- "Evolution Letters"
  - zu 1. ohne Band Nummer
  - zu 2. max 6 Autoren
- "Folia Biologica"
  - zu 1. mit Band-Nummer voran
  - zu 2. alle Autoren
- "Journal of Fish Biology"
  - zu 1. mit "Berichte .." und Band Nummer
  - zu 2. 6 Autoren  ... letzter Autor (Jahr)
  - aber das "In" bei Buchteil ohne Doppelpunkt

Immer noch nicht das richtige Format gefunden. Eignetlich sieht "Natur und Landschaft" gut aus ODER "Egretta".
