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
?>

<section class="page-head">
    <div>
        <h1>Browse Devices</h1>
        <p class="muted" id="search-result-count"><?= $totalDevices ?> device<?= $totalDevices === 1 ? '' : 's' ?> in the catalog</p>
    </div>
</section>

<section class="card search-bar">
    <label class="visually-hidden" for="device-search">Search devices</label>
    <input type="text" id="device-search" placeholder="Type to filter by name, brand, category or processor..." autocomplete="off">
</section>

<div class="device-grid" id="device-grid">
    <?php foreach ($devices as $device): ?>
        <?php
        $searchableText = $device['name'] . ' ' . $device['brand'] . ' ' . $device['category'] . ' ' . $device['processor'];
        ?>
        <article class="device-card" data-search="<?= htmlspecialchars(strtolower($searchableText)) ?>">
            <div class="device-image">
                <?php if ($device['image_path'] !== ''): ?>
                    <img src="<?= htmlspecialchars($device['image_path']) ?>" alt="<?= htmlspecialchars($device['name']) ?>">
                <?php else: ?>
                    <span class="image-placeholder"><?= htmlspecialchars(strtoupper($device['category'])) ?></span>
                <?php endif; ?>
            </div>
            <div class="device-body">
                <span class="category-chip"><?= htmlspecialchars($device['category']) ?></span>
                <h3><?= htmlspecialchars($device['name']) ?></h3>
                <p class="device-brand"><?= htmlspecialchars($device['brand']) ?></p>
                <ul class="spec-list">
                    <li><?= number_format((int)$device['ram_gb']) ?> GB RAM</li>
                    <li><?= number_format((int)$device['storage_gb']) ?> GB Storage</li>
                    <li><?= number_format((int)$device['battery_mah']) ?> mAh Battery</li>
                </ul>
                <p class="device-price">$<?= number_format((float)$device['price'], 2) ?></p>
            </div>
            <div class="device-actions">
                <a class="btn btn-sm" href="index.php?device_1_id=<?= (int)$device['id'] ?>">Compare</a>
            </div>
        </article>
    <?php endforeach; ?>
</div>

<p id="no-results-message" class="alert alert-info hidden">No devices match your search. Try a different keyword.</p>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
