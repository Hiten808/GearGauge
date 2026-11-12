<?php

require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Gear Gauge — Compare Devices';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_comparison') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    $deviceOneId = (int)($_POST['device_1_id'] ?? 0);
    $deviceTwoId = (int)($_POST['device_2_id'] ?? 0);
    if ($deviceOneId > 0 && $deviceTwoId > 0 && $deviceOneId !== $deviceTwoId) {
        $saveStatement = $dbConnection->prepare(
            'INSERT INTO saved_comparisons (user_id, device_1_id, device_2_id) VALUES (?, ?, ?)'
        );
        $saveStatement->bind_param('iii', $_SESSION['user_id'], $deviceOneId, $deviceTwoId);
        $saveStatement->execute();
        $saveStatement->close();
        $_SESSION['flash_message'] = 'Comparison saved. You can find it on your profile page.';
        $_SESSION['flash_type'] = 'success';
    }
    header('Location: index.php?device_1_id=' . $deviceOneId . '&device_2_id=' . $deviceTwoId);
    exit;
}

$deviceList = [];
$deviceListResult = $dbConnection->query('SELECT id, name, brand, category FROM devices ORDER BY category, brand, name');
while ($deviceListRow = $deviceListResult->fetch_assoc()) {
    $deviceList[] = $deviceListRow;
}
$deviceListResult->free();

$groupedDeviceList = [];
foreach ($deviceList as $deviceListDevice) {
    $groupedDeviceList[$deviceListDevice['category']][] = $deviceListDevice;
}

$selectedDeviceOneId = isset($_GET['device_1_id']) ? (int)$_GET['device_1_id'] : 0;
$selectedDeviceTwoId = isset($_GET['device_2_id']) ? (int)$_GET['device_2_id'] : 0;

$deviceOne = null;
$deviceTwo = null;
$comparisonError = '';

if ($selectedDeviceOneId > 0 && $selectedDeviceTwoId > 0) {
    if ($selectedDeviceOneId === $selectedDeviceTwoId) {
        $comparisonError = 'Please choose two different devices to compare.';
    } else {
        $selectedDeviceIds = [$selectedDeviceOneId, $selectedDeviceTwoId];
        $fetchedDevices = [];
        $deviceFetchStatement = $dbConnection->prepare(
            'SELECT id, name, brand, category, processor, ram_gb, storage_gb, gpu, battery_mah, price
             FROM devices WHERE id = ?'
        );
        foreach ($selectedDeviceIds as $selectedDeviceId) {
            $deviceFetchStatement->bind_param('i', $selectedDeviceId);
            $deviceFetchStatement->execute();
            $fetchedDevice = $deviceFetchStatement->get_result()->fetch_assoc();
            if ($fetchedDevice !== null) {
                $fetchedDevices[$fetchedDevice['id']] = $fetchedDevice;
            }
        }
        $deviceFetchStatement->close();

        if (isset($fetchedDevices[$selectedDeviceOneId], $fetchedDevices[$selectedDeviceTwoId])) {
            $deviceOne = $fetchedDevices[$selectedDeviceOneId];
            $deviceTwo = $fetchedDevices[$selectedDeviceTwoId];
        } else {
            $comparisonError = 'One of the selected devices could not be found.';
        }
    }
} elseif ($selectedDeviceOneId > 0 || $selectedDeviceTwoId > 0) {
    $comparisonError = 'Select a device in both dropdowns to build the comparison table.';
}

$comparisonRows = [
    ['label' => 'Name', 'field' => 'name'],
    ['label' => 'Brand', 'field' => 'brand'],
    ['label' => 'Category', 'field' => 'category'],
    ['label' => 'Processor', 'field' => 'processor'],
    ['label' => 'RAM', 'field' => 'ram_gb'],
    ['label' => 'Storage', 'field' => 'storage_gb'],
    ['label' => 'GPU', 'field' => 'gpu'],
    ['label' => 'Battery Capacity', 'field' => 'battery_mah'],
    ['label' => 'Price', 'field' => 'price'],
];

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div>
        <h1>Compare Two Devices</h1>
        <p class="muted">Pick any two gadgets from the database and see their specifications side by side.</p>
    </div>
</section>

<section class="card">
    <form id="comparison-form" method="get" action="index.php" class="comparison-selector">
        <div class="form-field">
            <label for="device_1_id">Device 1</label>
            <select id="device_1_id" name="device_1_id">
                <option value="">— Select a device —</option>
                <?php foreach ($groupedDeviceList as $groupCategory => $groupDevices): ?>
                    <optgroup label="<?= htmlspecialchars(format_category($groupCategory)) ?>">
                        <?php foreach ($groupDevices as $device): ?>
                            <option value="<?= (int)$device['id'] ?>"<?= $selectedDeviceOneId === (int)$device['id'] ? ' selected' : '' ?>>
                                <?= htmlspecialchars($device['brand'] . ' ' . $device['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </div>
        <span class="versus-badge">VS</span>
        <div class="form-field">
            <label for="device_2_id">Device 2</label>
            <select id="device_2_id" name="device_2_id">
                <option value="">— Select a device —</option>
                <?php foreach ($groupedDeviceList as $groupCategory => $groupDevices): ?>
                    <optgroup label="<?= htmlspecialchars(format_category($groupCategory)) ?>">
                        <?php foreach ($groupDevices as $device): ?>
                            <option value="<?= (int)$device['id'] ?>"<?= $selectedDeviceTwoId === (int)$device['id'] ? ' selected' : '' ?>>
                                <?= htmlspecialchars($device['brand'] . ' ' . $device['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-outline">Compare</button>
    </form>
</section>

<?php if ($comparisonError !== ''): ?>
    <div class="alert alert-error"><?= htmlspecialchars($comparisonError) ?></div>
<?php elseif ($deviceOne !== null && $deviceTwo !== null): ?>
    <section class="card">
        <div class="comparison-head">
            <h2>Specification Showdown</h2>
            <?php if ($isLoggedIn): ?>
                <form method="post" action="index.php" class="inline-form">
                    <input type="hidden" name="action" value="save_comparison">
                    <input type="hidden" name="device_1_id" value="<?= (int)$deviceOne['id'] ?>">
                    <input type="hidden" name="device_2_id" value="<?= (int)$deviceTwo['id'] ?>">
                    <button type="submit" class="btn">Save Comparison</button>
                </form>
            <?php else: ?>
                <p class="muted"><a href="login.php">Log in</a> to save this comparison to your profile.</p>
            <?php endif; ?>
        </div>
        <div class="table-wrap">
            <table class="comparison-table">
                <thead>
                    <tr>
                        <th scope="col">Specification</th>
                        <th scope="col"><?= htmlspecialchars($deviceOne['brand'] . ' ' . $deviceOne['name']) ?></th>
                        <th scope="col"><?= htmlspecialchars($deviceTwo['brand'] . ' ' . $deviceTwo['name']) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comparisonRows as $specRow): ?>
                        <?php
                        $specField = $specRow['field'];
                        $isNumericSpec = in_array($specField, ['ram_gb', 'storage_gb', 'battery_mah', 'price'], true);
                        ?>
                        <tr>
                            <th scope="row"><?= htmlspecialchars($specRow['label']) ?></th>
                            <?php foreach ([$deviceOne, $deviceTwo] as $comparedDevice): ?>
                                <?php
                                $specValue = $comparedDevice[$specField];
                                $dataAttributes = '';
                                if ($isNumericSpec) {
                                    $dataAttributes = ' data-field="' . $specField . '" data-value="'
                                        . ($specField === 'price'
                                            ? htmlspecialchars((string)(float)$specValue)
                                            : (int)$specValue)
                                        . '"';
                                }
                                $displayValue = match ($specField) {
                                    'ram_gb' => number_format((int)$specValue) . ' GB',
                                    'storage_gb' => number_format((int)$specValue) . ' GB',
                                    'battery_mah' => (int)$specValue > 0 ? number_format((int)$specValue) . ' mAh' : 'Mains powered (no battery)',
                                    'price' => '$' . number_format((float)$specValue, 2),
                                    'category' => htmlspecialchars(format_category((string)$specValue)),
                                    default => htmlspecialchars((string)$specValue),
                                };
                                ?>
                                <td<?= $dataAttributes ?>><?= $displayValue ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="muted small">JavaScript highlights the better value in green for RAM, storage and battery (higher wins) and price (lower wins).</p>
    </section>
<?php endif; ?>

<section class="hint-grid">
    <div class="card">
        <h3>Browse the catalog</h3>
        <p class="muted">Search and filter every device stored in the database.</p>
        <a class="btn btn-outline" href="browse.php">Browse Devices</a>
    </div>
    <div class="card">
        <h3>Keep your favourites</h3>
        <p class="muted">Save the comparisons you care about and revisit them any time.</p>
        <a class="btn btn-outline" href="profile.php">My Comparisons</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
