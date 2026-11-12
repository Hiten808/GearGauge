<?php

require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Gear Gauge — Browse Devices';

require_once __DIR__ . '/includes/header.php';

$devices = [];
$devicesResult = $dbConnection->query(
    'SELECT id, name, brand, category, processor, ram_gb, storage_gb, battery_mah, price, image_path
     FROM devices ORDER BY category, brand, name'
);
while ($deviceRow = $devicesResult->fetch_assoc()) {
    $devices[] = $deviceRow;
}
$devicesResult->free();

$totalDevices = count($devices);

$categoryFilters = [
    'all' => 'All',
    'laptop' => 'Laptops',
    'phone' => 'Phones',
    'tablet' => 'Tablets',
    'androidtv' => 'Android TVs',
];

$categoryCounts = ['all' => $totalDevices, 'laptop' => 0, 'phone' => 0, 'tablet' => 0, 'androidtv' => 0];
foreach ($devices as $device) {
    if (isset($categoryCounts[$device['category']])) {
        $categoryCounts[$device['category']]++;
    }
}
?>

<section class="page-head">
    <div>
        <h1>Browse Devices</h1>
        <p class="muted" id="search-result-count"><?= $totalDevices ?> device<?= $totalDevices === 1 ? '' : 's' ?> in the catalog</p>
    </div>
</section>

<section class="card search-bar">
    <div class="filter-chips" id="filter-chips">
        <?php foreach ($categoryFilters as $filterValue => $filterLabel): ?>
            <button type="button" class="chip<?= $filterValue === 'all' ? ' active' : '' ?>" data-category="<?= $filterValue ?>"><?= htmlspecialchars($filterLabel) ?> (<?= (int)$categoryCounts[$filterValue] ?>)</button>
        <?php endforeach; ?>
    </div>
    <label class="visually-hidden" for="device-search">Search devices</label>
    <input type="text" id="device-search" placeholder="Type to filter by name, brand, category or processor..." autocomplete="off">
</section>

<div class="device-grid" id="device-grid">
    <?php foreach ($devices as $device): ?>
        <?php
        $searchableText = $device['name'] . ' ' . $device['brand'] . ' ' . $device['category'] . ' ' . $device['processor'];
        ?>
        <article class="device-card" data-search="<?= htmlspecialchars(strtolower($searchableText)) ?>" data-category="<?= htmlspecialchars($device['category']) ?>">
            <a class="device-image" href="device.php?id=<?= (int)$device['id'] ?>">
                <?php if ($device['image_path'] !== ''): ?>
                    <img src="<?= htmlspecialchars($device['image_path']) ?>" alt="<?= htmlspecialchars($device['name']) ?>">
                <?php else: ?>
                    <span class="image-placeholder"><?= htmlspecialchars(format_category($device['category'])) ?></span>
                <?php endif; ?>
            </a>
            <div class="device-body">
                <span class="category-chip"><?= htmlspecialchars(format_category($device['category'])) ?></span>
                <h3><a class="card-title-link" href="device.php?id=<?= (int)$device['id'] ?>"><?= htmlspecialchars($device['name']) ?></a></h3>
                <p class="device-brand"><?= htmlspecialchars($device['brand']) ?></p>
                <ul class="spec-list">
                    <li><?= number_format((int)$device['ram_gb']) ?> GB RAM</li>
                    <li><?= number_format((int)$device['storage_gb']) ?> GB Storage</li>
                    <?php if ((int)$device['battery_mah'] > 0): ?>
                        <li><?= number_format((int)$device['battery_mah']) ?> mAh Battery</li>
                    <?php endif; ?>
                </ul>
                <p class="device-price">$<?= number_format((float)$device['price'], 2) ?></p>
            </div>
            <div class="device-actions">
                <a class="btn btn-sm btn-outline" href="device.php?id=<?= (int)$device['id'] ?>">View</a>
                <a class="btn btn-sm" href="index.php?device_1_id=<?= (int)$device['id'] ?>">Compare</a>
            </div>
        </article>
    <?php endforeach; ?>
</div>

<p id="no-results-message" class="alert alert-info hidden">No devices match your search. Try a different keyword.</p>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
