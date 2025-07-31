<?php
// Simple PHP script to fetch and display Zotero entries with search functionality

// --- CONFIGURATION ---
$zoteroUserID = '24259'; // Replace with your Zotero user ID
$zoteroAPIKey = 'a743gqc7AS3HgY57iZlshfH9'; // Replace with your Zotero API key (if needed)

$zoteroAPIUrlBase = "https://api.zotero.org/users/$zoteroUserID/items";
$zoteroLimit = 100; // Max allowed by Zotero API

// --- HANDLE SEARCH & SORT ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'year'; // default sort by year

// --- FETCH DATA FROM ZOTERO ---

$options = [
    'http' => [
        'header' => "Zotero-API-Key: $zoteroAPIKey\r\n"
    ]
];
$context = stream_context_create($options);

// --- FETCH ALL ENTRIES WITH PAGINATION ---

// If a search term is present, filter on the server using 'q' parameter
if ($search) {
    $items = [];
    $start = 0;
    $q = urlencode($search);
    do {
        $url = $zoteroAPIUrlBase . '?format=json&limit=' . $zoteroLimit . '&start=' . $start . '&q=' . $q;
        $response = @file_get_contents($url, false, $context);
        $batch = $response ? json_decode($response, true) : [];
        $items = array_merge($items, $batch);
        $fetched = count($batch);
        $start += $zoteroLimit;
    } while ($fetched === $zoteroLimit);
} else {
    // No search: fetch all entries with pagination
    $items = [];
    $start = 0;
    do {
        $url = $zoteroAPIUrlBase . '?format=json&limit=' . $zoteroLimit . '&start=' . $start;
        $response = @file_get_contents($url, false, $context);
        $batch = $response ? json_decode($response, true) : [];
        $items = array_merge($items, $batch);
        $fetched = count($batch);
        $start += $zoteroLimit;
    } while ($fetched === $zoteroLimit);
}

// --- FILTER ENTRIES ---



// Filter out attachment items (e.g., PDFs)
$displayItems = array_filter($items, function($item) {
    $data = $item['data'] ?? [];
    return isset($data['itemType']) && $data['itemType'] !== 'attachment';
});

// Sort $displayItems by year or author
if (!empty($displayItems)) {
    $displayItems = array_values($displayItems); // reindex
    if ($sort === 'author') {
        usort($displayItems, function($a, $b) {
            $aAuthors = $a['data']['creators'][0]['lastName'] ?? '';
            $bAuthors = $b['data']['creators'][0]['lastName'] ?? '';
            return strcasecmp($aAuthors, $bAuthors);
        });
    } else { // default: year
        usort($displayItems, function($a, $b) {
            $aYear = $a['data']['date'] ?? '';
            $bYear = $b['data']['date'] ?? '';
            // Try to extract year from date string
            preg_match('/\\d{4}/', $aYear, $aMatch);
            preg_match('/\\d{4}/', $bYear, $bMatch);
            $aYearNum = isset($aMatch[0]) ? (int)$aMatch[0] : 0;
            $bYearNum = isset($bMatch[0]) ? (int)$bMatch[0] : 0;
            return $bYearNum <=> $aYearNum; // descending (most recent first)
        });
    }
}

// Fetch formatted citations for the display items (one by one for reliability)
$citationStyle = 'journal-of-ecology';
$citationMap = [];
if (!empty($displayItems)) {
    foreach ($displayItems as $item) {
        $key = $item['key'] ?? '';
        if ($key) {
            $bibUrl = "https://api.zotero.org/users/$zoteroUserID/items/$key?format=bib&style=$citationStyle";
            $bibOptions = [
                'http' => [
                    'header' => "Zotero-API-Key: $zoteroAPIKey\r\nAccept: text/html\r\n"
                ]
            ];
            $bibContext = stream_context_create($bibOptions);
            $bibResponse = @file_get_contents($bibUrl, false, $bibContext);
            if ($bibResponse !== false) {
                // Extract citation from <div class="csl-entry">...</div>
                if (preg_match('/<div class="csl-entry">(.*?)<\/div>/s', $bibResponse, $match)) {
                    $citationMap[$key] = $match[1];
                }
            }
        }
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zotero Entries Viewer</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2em; }
        .entry { border-bottom: 1px solid #ccc; padding: 1em 0; }
        .title { font-weight: bold; font-size: 1.2em; }
        .abstract { color: #555; }
        form { margin-bottom: 2em; }
    </style>
</head>
<body>
    <h1>Zotero Entries Viewer (VSCode)</h1>
    <form method="get" style="display: flex; gap: 1em; align-items: center;">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search entries...">
        <label for="sort">Sort by:</label>
        <select name="sort" id="sort">
            <option value="year"<?php if ($sort === 'year') echo ' selected'; ?>>Year</option>
            <option value="author"<?php if ($sort === 'author') echo ' selected'; ?>>Author</option>
        </select>
        <button type="submit">Search</button>
    </form>
    <?php if (empty($displayItems)): ?>
        <p>No entries found.</p>
    <?php else: ?>
        <?php foreach ($displayItems as $item):
            $data = $item['data'] ?? [];
        ?>
        <div class="entry">
            <?php
            $key = $item['key'] ?? '';
            $citation = isset($citationMap[$key]) && $citationMap[$key] ? $citationMap[$key] : null;
            if ($citation) {
                // Remove any trailing DOI URL from the citation
                $citationClean = preg_replace('/\s*https?:\/\/doi\.org\/[\w\/\.\-()]+/i', '', $citation);
                $doi = $data['DOI'] ?? '';
                echo $citationClean;
                if ($doi) {
                    $doiUrl = 'https://doi.org/' . htmlspecialchars($doi);
                    echo ' <a href="' . $doiUrl . '" target="_blank" rel="noopener">DOI: ' . htmlspecialchars($doi) . '</a>';
                }
            } else {
                echo '<em>No citation available.</em>';
            }
            ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
