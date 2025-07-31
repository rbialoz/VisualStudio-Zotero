<?php
// Simple PHP script to fetch and display Zotero entries with search functionality

// --- CONFIGURATION ---
$zoteroUserID = '24259'; // Replace with your Zotero user ID
$zoteroAPIKey = 'a743gqc7AS3HgY57iZlshfH9'; // Replace with your Zotero API key (if needed)

$zoteroAPIUrlBase = "https://api.zotero.org/users/$zoteroUserID/items";
$zoteroLimit = 100; // Max allowed by Zotero API

// --- HANDLE SEARCH ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

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
    <form method="get">
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search entries...">
        <button type="submit">Search</button>
    </form>
    <?php if (empty($displayItems)): ?>
        <p>No entries found.</p>
    <?php else: ?>
        <?php foreach ($displayItems as $item):
            $data = $item['data'] ?? [];
        ?>
        <div class="entry">
            <div class="title"><?php echo htmlspecialchars($data['title'] ?? '[No Title]'); ?></div>
            <div class="abstract">
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
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
