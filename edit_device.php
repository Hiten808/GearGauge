<?php

require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Gear Gauge — Edit Device';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$editDeviceId = (int)($_GET['id'] ?? 0);

$editDeviceStatement = $dbConnection->prepare(
    'SELECT id, name, brand, category, processor, ram_gb, storage_gb, gpu, battery_mah, price, image_path
     FROM devices WHERE id = ?'
);
$editDeviceStatement->bind_param('i', $editDeviceId);
$editDeviceStatement->execute();
$editDevice = $editDeviceStatement->get_result()->fetch_assoc();
$editDeviceStatement->close();

if ($editDevice === null) {
    $_SESSION['flash_message'] = 'The requested device could not be found.';
    $_SESSION['flash_type'] = 'error';
    header('Location: admin.php');
    exit;
}

$formErrors = [];
$formValues = [
    'name' => $editDevice['name'],
    'brand' => $editDevice['brand'],
    'category' => $editDevice['category'],
    'processor' => $editDevice['processor'],
    'ram_gb' => (string)$editDevice['ram_gb'],
    'storage_gb' => (string)$editDevice['storage_gb'],
    'gpu' => $editDevice['gpu'],
    'battery_mah' => (string)$editDevice['battery_mah'],
    'price' => (string)$editDevice['price'],
];
$currentImagePath = $editDevice['image_path'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_device') {
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
    if (!in_array($formValues['category'], ['laptop', 'phone'], true)) {
        $formErrors['category'] = 'Category must be laptop or phone.';
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
    if (!ctype_digit($formValues['battery_mah']) || (int)$formValues['battery_mah'] < 1) {
        $formErrors['battery_mah'] = 'Battery capacity must be a positive number of mAh.';
    }
    if (!is_numeric($formValues['price']) || (float)$formValues['price'] < 0) {
        $formErrors['price'] = 'Price must be a valid non-negative number.';
    }

    $newImagePath = $currentImagePath;
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
                $newImagePath = 'uploads/' . $safeImageName;
            } else {
                $formErrors['device_image'] = 'The image could not be saved to the uploads folder.';
            }
        }
    }

    if (!$formErrors) {
        $updateName = $formValues['name'];
        $updateBrand = $formValues['brand'];
        $updateCategory = $formValues['category'];
        $updateProcessor = $formValues['processor'];
        $updateRamGb = (int)$formValues['ram_gb'];
        $updateStorageGb = (int)$formValues['storage_gb'];
        $updateGpu = $formValues['gpu'];
        $updateBatteryMah = (int)$formValues['battery_mah'];
        $updatePrice = (float)$formValues['price'];
        $updateImagePath = $newImagePath;
        $updateStatement = $dbConnection->prepare(
            'UPDATE devices
             SET name = ?, brand = ?, category = ?, processor = ?, ram_gb = ?, storage_gb = ?, gpu = ?, battery_mah = ?, price = ?, image_path = ?
             WHERE id = ?'
        );
        $updateStatement->bind_param(
            'ssssiisidsi',
            $updateName,
            $updateBrand,
            $updateCategory,
            $updateProcessor,
            $updateRamGb,
            $updateStorageGb,
            $updateGpu,
            $updateBatteryMah,
            $updatePrice,
            $updateImagePath,
            $editDeviceId
        );
        $updateStatement->execute();
        $updateStatement->close();

        if ($currentImagePath !== '' && $currentImagePath !== $newImagePath) {
            $oldImageFilePath = __DIR__ . '/' . $currentImagePath;
            if (is_file($oldImageFilePath)) {
                unlink($oldImageFilePath);
            }
        }

        $_SESSION['flash_message'] = 'Device "' . $updateName . '" updated successfully.';
        $_SESSION['flash_type'] = 'success';
        header('Location: admin.php');
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div>
        <h1>Edit Device</h1>
        <p class="muted">Update the specifications of <?= htmlspecialchars($editDevice['name']) ?>.</p>
    </div>
</section>

<section class="card">
    <form method="post" action="edit_device.php?id=<?= (int)$editDeviceId ?>" enctype="multipart/form-data" data-validate novalidate class="form-grid">
        <input type="hidden" name="action" value="update_device">
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
            <label for="battery_mah">Battery (mAh)</label>
            <input type="number" id="battery_mah" name="battery_mah" min="1" step="1" value="<?= htmlspecialchars($formValues['battery_mah']) ?>" data-required>
            <small class="field-error"><?= htmlspecialchars($formErrors['battery_mah'] ?? '') ?></small>
        </div>
        <div class="form-field">
            <label for="price">Price (USD)</label>
            <input type="number" id="price" name="price" min="0" step="0.01" value="<?= htmlspecialchars($formValues['price']) ?>" data-required>
            <small class="field-error"><?= htmlspecialchars($formErrors['price'] ?? '') ?></small>
        </div>
        <div class="form-field">
            <label for="device_image">Replace Image (JPG, PNG or WEBP, max 2 MB)</label>
            <input type="file" id="device_image" name="device_image" accept=".jpg,.jpeg,.png,.webp">
            <small class="field-error"><?= htmlspecialchars($formErrors['device_image'] ?? '') ?></small>
        </div>
        <?php if ($currentImagePath !== ''): ?>
            <div class="form-field">
                <span class="field-label">Current Image</span>
                <img class="form-thumb" src="<?= htmlspecialchars($currentImagePath) ?>" alt="Current image of <?= htmlspecialchars($editDevice['name']) ?>">
            </div>
        <?php endif; ?>
        <div class="form-field form-span">
            <button type="submit" class="btn">Save Changes</button>
            <a class="btn btn-outline" href="admin.php">Cancel</a>
        </div>
    </form>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
