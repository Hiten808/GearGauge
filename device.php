<?php

require_once __DIR__ . '/includes/db.php';

$detailDeviceId = (int)($_GET['id'] ?? 0);

$detailDevice = null;
if ($detailDeviceId > 0) {
    $detailDeviceStatement = $dbConnection->prepare(
        'SELECT id, name, brand, category, processor, ram_gb, storage_gb, gpu, battery_mah, price, image_path
         FROM devices WHERE id = ?'
    );
    $detailDeviceStatement->bind_param('i', $detailDeviceId);
    $detailDeviceStatement->execute();
    $detailDevice = $detailDeviceStatement->get_result()->fetch_assoc();
    $detailDeviceStatement->close();
}

if ($detailDevice !== null) {
    $pageTitle = 'Gear Gauge — ' . $detailDevice['brand'] . ' ' . $detailDevice['name'];

    $relatedDevices = [];
    $relatedStatement = $dbConnection->prepare(
        'SELECT id, name, brand, category, ram_gb, storage_gb, battery_mah, price, image_path
         FROM devices WHERE category = ? AND id <> ? ORDER BY price LIMIT 4'
    );
    $relatedCategory = $detailDevice['category'];
    $relatedStatement->bind_param('si', $relatedCategory, $detailDeviceId);
    $relatedStatement->execute();
    $relatedResult = $relatedStatement->get_result();
    while ($relatedRow = $relatedResult->fetch_assoc()) {
        $relatedDevices[] = $relatedRow;
    }
    $relatedResult->free();
    $relatedStatement->close();
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($detailDevice === null): ?>
    <section class="page-head">
        <div>
            <h1>Device Not Found</h1>
        </div>
    </section>
    <div class="alert alert-error">The requested product page could not be found in the catalog.</div>
    <p><a class="btn btn-outline" href="browse.php">Back to Browse Devices</a></p>
<?php else: ?>
    <nav class="breadcrumb">
        <a href="browse.php">Browse Devices</a>
        <span class="muted">/ <?= htmlspecialchars(format_category($detailDevice['category'])) ?></span>
        <span class="muted">/ <?= htmlspecialchars($detailDevice['name']) ?></span>
    </nav>

    <section class="card device-detail">
        <div class="detail-image">
            <?php if ($detailDevice['image_path'] !== ''): ?>
                <img src="<?= htmlspecialchars($detailDevice['image_path']) ?>" alt="<?= htmlspecialchars($detailDevice['name']) ?>">
            <?php else: ?>
                <span class="image-placeholder"><?= htmlspecialchars(format_category($detailDevice['category'])) ?></span>
            <?php endif; ?>
        </div>
        <div class="detail-info">
            <span class="category-chip"><?= htmlspecialchars(format_category($detailDevice['category'])) ?></span>
            <h1><?= htmlspecialchars($detailDevice['name']) ?></h1>
            <p class="device-brand">by <?= htmlspecialchars($detailDevice['brand']) ?></p>
            <p class="detail-price">$<?= number_format((float)$detailDevice['price'], 2) ?></p>
            <div class="detail-actions">
                <a class="btn" href="index.php?device_1_id=<?= (int)$detailDevice['id'] ?>">Compare This Device</a>
                <a class="btn btn-outline" href="browse.php">Back to Browse</a>
            </div>
        </div>
    </section>

    <section class="card">
        <h2>Full Specifications</h2>
        <div class="table-wrap">
            <table class="comparison-table detail-spec-table">
                <tbody>
                    <tr>
                        <th scope="row">Processor</th>
                        <td><?= htmlspecialchars($detailDevice['processor']) ?></td>
                    </tr>
                    <tr>
                        <th scope="row">GPU</th>
                        <td><?= $detailDevice['gpu'] !== '' ? htmlspecialchars($detailDevice['gpu']) : 'Integrated' ?></td>
                    </tr>
                    <tr>
                        <th scope="row">RAM</th>
                        <td><?= number_format((int)$detailDevice['ram_gb']) ?> GB</td>
                    </tr>
                    <tr>
                        <th scope="row">Storage</th>
                        <td><?= number_format((int)$detailDevice['storage_gb']) ?> GB</td>
                    </tr>
                    <tr>
                        <th scope="row">Battery Capacity</th>
                        <td><?= (int)$detailDevice['battery_mah'] > 0 ? number_format((int)$detailDevice['battery_mah']) . ' mAh' : 'Mains powered (no battery)' ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Price</th>
                        <td>$<?= number_format((float)$detailDevice['price'], 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <?php if ($relatedDevices !== []): ?>
        <section class="related-section">
            <h2>More <?= htmlspecialchars(format_category($detailDevice['category'])) ?>s</h2>
            <div class="device-grid">
                <?php foreach ($relatedDevices as $relatedDevice): ?>
                    <article class="device-card">
                        <a class="device-image" href="device.php?id=<?= (int)$relatedDevice['id'] ?>">
                            <?php if ($relatedDevice['image_path'] !== ''): ?>
                                <img src="<?= htmlspecialchars($relatedDevice['image_path']) ?>" alt="<?= htmlspecialchars($relatedDevice['name']) ?>">
                            <?php else: ?>
                                <span class="image-placeholder"><?= htmlspecialchars(format_category($relatedDevice['category'])) ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="device-body">
                            <span class="category-chip"><?= htmlspecialchars(format_category($relatedDevice['category'])) ?></span>
                            <h3><a class="card-title-link" href="device.php?id=<?= (int)$relatedDevice['id'] ?>"><?= htmlspecialchars($relatedDevice['name']) ?></a></h3>
                            <p class="device-brand"><?= htmlspecialchars($relatedDevice['brand']) ?></p>
                            <ul class="spec-list">
                                <li><?= number_format((int)$relatedDevice['ram_gb']) ?> GB RAM</li>
                                <li><?= number_format((int)$relatedDevice['storage_gb']) ?> GB Storage</li>
                            </ul>
                            <p class="device-price">$<?= number_format((float)$relatedDevice['price'], 2) ?></p>
                        </div>
                        <div class="device-actions">
                            <a class="btn btn-sm" href="device.php?id=<?= (int)$relatedDevice['id'] ?>">View</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
