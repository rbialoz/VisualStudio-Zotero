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

### 2025-08-01

- the '%E2' problem still remains
  - Search term "Vierte Bundeswaldinventur" would show at the end
- at the moment the big 'Ü' is not made to lower case
  - Search term "Klimawandel und Forstwirtschaft"
    - but only of the '%E2' line is not activated
  - Serach term "Übertragbarkeit empirischer statistischer Waldwachstumsmodelle"
    - here also, when the '%E2' line is active
- no idea how to implement the special NW-FVA style
- there are still many entries with an author 'unknown'
  - maybe these are not articles but a different entry type
- Fetch PDF if DOI links to a PDF file drectly, not so if it links to a html page
  - in the folder 'G:/zotero_pdfs/alle_pdfs_save/alle_pdfs' all entries after the 1. August 2025 are downloaded papers

## Start the server

- php -S localhost:8000
