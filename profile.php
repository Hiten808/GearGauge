<?php

require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Gear Gauge — My Comparisons';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_comparison') {
    $deleteComparisonId = (int)($_POST['comparison_id'] ?? 0);
    if ($deleteComparisonId > 0) {
        $deleteStatement = $dbConnection->prepare(
            'DELETE FROM saved_comparisons WHERE id = ? AND user_id = ?'
        );
        $deleteStatement->bind_param('ii', $deleteComparisonId, $_SESSION['user_id']);
        $deleteStatement->execute();
        $deleteStatement->close();
        $_SESSION['flash_message'] = 'Saved comparison deleted.';
        $_SESSION['flash_type'] = 'success';
    }
    header('Location: profile.php');
    exit;
}

$savedComparisons = [];
$comparisonsStatement = $dbConnection->prepare(
    'SELECT sc.id, sc.device_1_id, sc.device_2_id, sc.created_at,
            d1.name AS device_1_name, d1.brand AS device_1_brand,
            d2.name AS device_2_name, d2.brand AS device_2_brand
     FROM saved_comparisons sc
     INNER JOIN devices d1 ON d1.id = sc.device_1_id
     INNER JOIN devices d2 ON d2.id = sc.device_2_id
     WHERE sc.user_id = ?
     ORDER BY sc.created_at DESC'
);
$comparisonsStatement->bind_param('i', $_SESSION['user_id']);
$comparisonsStatement->execute();
$comparisonsResult = $comparisonsStatement->get_result();
while ($comparisonRow = $comparisonsResult->fetch_assoc()) {
    $savedComparisons[] = $comparisonRow;
}
$comparisonsResult->free();
$comparisonsStatement->close();

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-head">
    <div>
        <h1>My Comparisons</h1>
        <p class="muted">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>. Here are the comparisons you saved.</p>
    </div>
</section>

<?php if ($savedComparisons === []): ?>
    <div class="alert alert-info">
        You have not saved any comparisons yet. Build one on the <a href="index.php">comparison page</a>.
    </div>
<?php else: ?>
    <div class="card table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th scope="col">Device 1</th>
                    <th scope="col">Device 2</th>
                    <th scope="col">Saved On</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($savedComparisons as $comparison): ?>
                    <tr>
                        <td><?= htmlspecialchars($comparison['device_1_brand'] . ' ' . $comparison['device_1_name']) ?></td>
                        <td><?= htmlspecialchars($comparison['device_2_brand'] . ' ' . $comparison['device_2_name']) ?></td>
                        <td><?= date('d M Y, H:i', strtotime($comparison['created_at'])) ?></td>
                        <td class="actions-cell">
                            <a class="btn btn-sm btn-outline" href="index.php?device_1_id=<?= (int)$comparison['device_1_id'] ?>&amp;device_2_id=<?= (int)$comparison['device_2_id'] ?>">Open</a>
                            <form class="inline-form" method="post" action="profile.php">
                                <input type="hidden" name="action" value="delete_comparison">
                                <input type="hidden" name="comparison_id" value="<?= (int)$comparison['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this saved comparison? This cannot be undone.')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
