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

// --- FILTER ENTRIES ---
function filter_items($items, $search) {
    if (!$search) return $items;
    $filtered = [];
    foreach ($items as $item) {
        $data = $item['data'] ?? [];
        $title = $data['title'] ?? '';
        $abstract = $data['abstractNote'] ?? '';
        if (stripos($title, $search) !== false || stripos($abstract, $search) !== false) {
            $filtered[] = $item;
        }
    }
    return $filtered;
}
$displayItems = filter_items($items, $search);

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
            <div class="abstract"><?php echo nl2br(htmlspecialchars($data['abstractNote'] ?? '')); ?></div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
