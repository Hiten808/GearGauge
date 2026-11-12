<?php

require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Gear Gauge — Admin Dashboard';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$formErrors = [];
$formValues = [
    'name' => '',
    'brand' => '',
    'category' => 'laptop',
    'processor' => '',
    'ram_gb' => '',
    'storage_gb' => '',
    'gpu' => '',
    'battery_mah' => '',
    'price' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_device') {
    $deleteDeviceId = (int)($_POST['device_id'] ?? 0);
    if ($deleteDeviceId > 0) {
        $imageLookupStatement = $dbConnection->prepare('SELECT image_path FROM devices WHERE id = ?');
        $imageLookupStatement->bind_param('i', $deleteDeviceId);
        $imageLookupStatement->execute();
        $deviceImageRow = $imageLookupStatement->get_result()->fetch_assoc();
        $imageLookupStatement->close();

        $deleteStatement = $dbConnection->prepare('DELETE FROM devices WHERE id = ?');
        $deleteStatement->bind_param('i', $deleteDeviceId);
        $deleteStatement->execute();
        $deleteStatement->close();

        if ($deviceImageRow !== null && $deviceImageRow['image_path'] !== '') {
            $imageFilePath = __DIR__ . '/' . $deviceImageRow['image_path'];
            if (is_file($imageFilePath)) {
                unlink($imageFilePath);
            }
        }
        $_SESSION['flash_message'] = 'Device deleted successfully.';
        $_SESSION['flash_type'] = 'success';
    }
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_device') {
    $formValues['name'] = trim($_POST['name'] ?? '');
    $formValues['brand'] = trim($_POST['brand'] ?? '');
    $formValues['category'] = trim($_POST['category'] ?? '');
    $formValues['processor'] = trim($_POST['processor'] ?? '');
    $formValues['ram_gb'] = trim($_POST['ram_gb'] ?? '');
    $formValues['storage_gb'] = trim($_POST['storage_gb'] ?? '');
    $formValues['gpu'] = trim($_POST['gpu'] ?? '');
    $formValues['battery_mah'] = trim($_POST['battery_mah'] ?? '');
    $formValues['price'] = trim($_POST['price'] ?? '');

    if ($formValues['name'] === '') {
        $formErrors['name'] = 'Device name is required.';
    }
    if ($formValues['brand'] === '') {
        $formErrors['brand'] = 'Brand is required.';
    }
    if (!in_array($formValues['category'], ['laptop', 'phone', 'tablet', 'androidtv'], true)) {
        $formErrors['category'] = 'Category must be laptop, phone, tablet or Android TV.';
    }
    if ($formValues['processor'] === '') {
        $formErrors['processor'] = 'Processor is required.';
    }
    if (!ctype_digit($formValues['ram_gb']) || (int)$formValues['ram_gb'] < 1) {
        $formErrors['ram_gb'] = 'RAM must be a positive whole number of GB.';
    }
    if (!ctype_digit($formValues['storage_gb']) || (int)$formValues['storage_gb'] < 1) {
        $formErrors['storage_gb'] = 'Storage must be a positive whole number of GB.';
    }
    if (!ctype_digit($formValues['battery_mah']) || (int)$formValues['battery_mah'] < 0) {
        $formErrors['battery_mah'] = 'Battery capacity must be a non-negative number of mAh.';
    }
    if (!is_numeric($formValues['price']) || (float)$formValues['price'] < 0) {
        $formErrors['price'] = 'Price must be a valid non-negative number.';
    }

    $uploadedImagePath = '';
    if (!empty($_FILES['device_image']['name'])) {
        $imageFile = $_FILES['device_image'];
        $allowedImageExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $imageExtension = strtolower(pathinfo($imageFile['name'], PATHINFO_EXTENSION));
        if ($imageFile['error'] !== UPLOAD_ERR_OK) {
            $formErrors['device_image'] = 'The image upload failed. Please try again.';
        } elseif ($imageFile['size'] > 2 * 1024 * 1024) {
            $formErrors['device_image'] = 'The image must be 2 MB or smaller.';
        } elseif (!in_array($imageExtension, $allowedImageExtensions, true)) {
            $formErrors['device_image'] = 'Only JPG, PNG and WEBP images are allowed.';
        } elseif (getimagesize($imageFile['tmp_name']) === false) {
            $formErrors['device_image'] = 'The uploaded file is not a valid image.';
        } else {
            $safeImageName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($imageFile['name']));
            $imageTargetPath = __DIR__ . '/uploads/' . $safeImageName;
            if (move_uploaded_file($imageFile['tmp_name'], $imageTargetPath)) {
                $uploadedImagePath = 'uploads/' . $safeImageName;
            } else {
                $formErrors['device_image'] = 'The image could not be saved to the uploads folder.';
            }
        }
    }

    if (!$formErrors) {
        $insertRamGb = (int)$formValues['ram_gb'];
        $insertStorageGb = (int)$formValues['storage_gb'];
        $insertGpu = $formValues['gpu'];
        $insertBatteryMah = (int)$formValues['battery_mah'];
        $insertPrice = (float)$formValues['price'];
        $insertImagePath = $uploadedImagePath;
        $insertStatement = $dbConnection->prepare(
            'INSERT INTO devices (name, brand, category, processor, ram_gb, storage_gb, gpu, battery_mah, price, image_path)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $insertStatement->bind_param(
            'ssssiisids',
            $formValues['name'],
            $formValues['brand'],
            $formValues['category'],
            $formValues['processor'],
            $insertRamGb,
            $insertStorageGb,
            $insertGpu,
            $insertBatteryMah,
            $insertPrice,
            $insertImagePath
        );
        $insertStatement->execute();
        $insertStatement->close();

        $_SESSION['flash_message'] = 'Device "' . $formValues['name'] . '" added successfully.';
        $_SESSION['flash_type'] = 'success';
        header('Location: admin.php');
        exit;
    }
}

$adminDevices = [];
$adminDevicesResult = $dbConnection->query(
    'SELECT id, name, brand, category, processor, ram_gb, storage_gb, battery_mah, price, image_path, created_at
     FROM devices ORDER BY created_at DESC, id DESC'
);
while ($adminDeviceRow = $adminDevicesResult->fetch_assoc()) {
    $adminDevices[] = $adminDeviceRow;
}
$adminDevicesResult->free();

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div>
        <h1>Admin Dashboard</h1>
        <p class="muted">Manage the device catalog shown across the site.</p>
    </div>
</section>

<section class="card">
    <h2>Add New Device</h2>
    <form method="post" action="admin.php" enctype="multipart/form-data" data-validate novalidate class="form-grid">
        <input type="hidden" name="action" value="add_device">
        <div class="form-field">
            <label for="name">Device Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($formValues['name']) ?>" data-required>
            <small class="field-error"><?= htmlspecialchars($formErrors['name'] ?? '') ?></small>
        </div>
        <div class="form-field">
            <label for="brand">Brand</label>
            <input type="text" id="brand" name="brand" value="<?= htmlspecialchars($formValues['brand']) ?>" data-required>
            <small class="field-error"><?= htmlspecialchars($formErrors['brand'] ?? '') ?></small>
        </div>
        <div class="form-field">
            <label for="category">Category</label>
            <select id="category" name="category" data-required>
                <option value="laptop"<?= $formValues['category'] === 'laptop' ? ' selected' : '' ?>>Laptop</option>
                <option value="phone"<?= $formValues['category'] === 'phone' ? ' selected' : '' ?>>Phone</option>
                <option value="tablet"<?= $formValues['category'] === 'tablet' ? ' selected' : '' ?>>Tablet</option>
                <option value="androidtv"<?= $formValues['category'] === 'androidtv' ? ' selected' : '' ?>>Android TV</option>
            </select>
            <small class="field-error"><?= htmlspecialchars($formErrors['category'] ?? '') ?></small>
        </div>
        <div class="form-field">
            <label for="processor">Processor</label>
            <input type="text" id="processor" name="processor" value="<?= htmlspecialchars($formValues['processor']) ?>" data-required>
            <small class="field-error"><?= htmlspecialchars($formErrors['processor'] ?? '') ?></small>
        </div>
        <div class="form-field">
            <label for="ram_gb">RAM (GB)</label>
            <input type="number" id="ram_gb" name="ram_gb" min="1" step="1" value="<?= htmlspecialchars($formValues['ram_gb']) ?>" data-required>
            <small class="field-error"><?= htmlspecialchars($formErrors['ram_gb'] ?? '') ?></small>
        </div>
        <div class="form-field">
            <label for="storage_gb">Storage (GB)</label>
            <input type="number" id="storage_gb" name="storage_gb" min="1" step="1" value="<?= htmlspecialchars($formValues['storage_gb']) ?>" data-required>
            <small class="field-error"><?= htmlspecialchars($formErrors['storage_gb'] ?? '') ?></small>
        </div>
        <div class="form-field">
            <label for="gpu">GPU (optional)</label>
            <input type="text" id="gpu" name="gpu" value="<?= htmlspecialchars($formValues['gpu']) ?>">
            <small class="field-error"></small>
        </div>
        <div class="form-field">
            <label for="battery_mah">Battery (mAh, 0 for mains-powered devices)</label>
            <input type="number" id="battery_mah" name="battery_mah" min="0" step="1" value="<?= htmlspecialchars($formValues['battery_mah']) ?>" data-required>
            <small class="field-error"><?= htmlspecialchars($formErrors['battery_mah'] ?? '') ?></small>
        </div>
        <div class="form-field">
            <label for="price">Price (USD)</label>
            <input type="number" id="price" name="price" min="0" step="0.01" value="<?= htmlspecialchars($formValues['price']) ?>" data-required>
            <small class="field-error"><?= htmlspecialchars($formErrors['price'] ?? '') ?></small>
        </div>
        <div class="form-field">
            <label for="device_image">Device Image (JPG, PNG or WEBP, max 2 MB)</label>
            <input type="file" id="device_image" name="device_image" accept=".jpg,.jpeg,.png,.webp">
            <small class="field-error"><?= htmlspecialchars($formErrors['device_image'] ?? '') ?></small>
        </div>
        <div class="form-field form-span">
            <button type="submit" class="btn">Add Device</button>
        </div>
    </form>
</section>

<section class="card">
    <h2>Device Catalog (<?= count($adminDevices) ?>)</h2>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th scope="col">Image</th>
                    <th scope="col">Name</th>
                    <th scope="col">Brand</th>
                    <th scope="col">Category</th>
                    <th scope="col">Price</th>
                    <th scope="col">Added</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($adminDevices as $device): ?>
                    <tr>
                        <td>
                            <?php if ($device['image_path'] !== ''): ?>
                                <img class="table-thumb" src="<?= htmlspecialchars($device['image_path']) ?>" alt="<?= htmlspecialchars($device['name']) ?>">
                            <?php else: ?>
                                <span class="table-thumb table-thumb-empty">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($device['name']) ?><br>
                            <small class="muted"><?= htmlspecialchars($device['processor']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($device['brand']) ?></td>
                        <td><?= htmlspecialchars($device['category']) ?></td>
                        <td>$<?= number_format((float)$device['price'], 2) ?></td>
                        <td><?= date('d M Y', strtotime($device['created_at'])) ?></td>
                        <td class="actions-cell">
                            <a class="btn btn-sm btn-outline" href="edit_device.php?id=<?= (int)$device['id'] ?>">Edit</a>
                            <form class="inline-form" method="post" action="admin.php">
                                <input type="hidden" name="action" value="delete_device">
                                <input type="hidden" name="device_id" value="<?= (int)$device['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete <?= htmlspecialchars($device['name']) ?>? This cannot be undone.')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
