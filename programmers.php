<?php
require_once 'config.php';
require_once 'functions.php';
require_login();

$search = trim((string) ($_GET['q'] ?? ''));
$search = function_exists('mb_substr') ? mb_substr($search, 0, 100, 'UTF-8') : substr($search, 0, 100);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    $returnSearch = trim((string) ($_POST['return_q'] ?? ''));
    $check = $pdo->prepare('SELECT id, first_name, last_name FROM programmers WHERE id = ?');
    $check->execute([$id]);
    $programmer = $check->fetch();

    if (!$programmer) {
        set_flash('Programmer not found.', 'error');
    } else {
        $clientCheck = $pdo->prepare('SELECT COUNT(*) FROM clients WHERE programmer_id = ?');
        $clientCheck->execute([$id]);
        if ((int) $clientCheck->fetchColumn() > 0) {
            set_flash('This programmer still has clients. Reassign or delete those clients first.', 'error');
        } else {
            try {
                $pdo->beginTransaction();
                $delete = $pdo->prepare('DELETE FROM programmers WHERE id = ?');
                $delete->execute([$id]);
                log_activity($pdo, 'programmers', $id, 'DELETE', 'Deleted programmer: ' . $programmer['first_name'] . ' ' . $programmer['last_name']);
                $pdo->commit();
                set_flash('Programmer deleted. The action remains in Activity Logs.');
            } catch (PDOException $error) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                set_flash('The programmer could not be deleted. Check its assigned clients.', 'error');
            }
        }
    }

    $url = 'programmers.php';
    if ($returnSearch !== '') $url .= '?q=' . rawurlencode($returnSearch);
    redirect($url);
}

$sql = 'SELECT p.*,
        CONCAT(u1.first_name, " ", u1.last_name) AS added_by_name,
        CONCAT(u2.first_name, " ", u2.last_name) AS updated_by_name
        FROM programmers p
        JOIN users u1 ON p.added_by = u1.id
        JOIN users u2 ON p.last_updated_by = u2.id';

if ($search !== '') {
    $sql .= ' WHERE CAST(p.id AS CHAR) LIKE ?
              OR p.first_name LIKE ?
              OR p.last_name LIKE ?
              OR CONCAT(p.first_name, " ", p.last_name) LIKE ?
              OR p.email LIKE ?
              OR p.specialization LIKE ?
              OR p.phone LIKE ?
              OR p.address LIKE ?';
    $term = '%' . $search . '%';
    $statement = $pdo->prepare($sql . ' ORDER BY p.id DESC');
    $statement->execute(array_fill(0, 8, $term));
    $programmers = $statement->fetchAll();
} else {
    $programmers = $pdo->query($sql . ' ORDER BY p.id DESC')->fetchAll();
}

require 'header.php';
?>
<section class="page-heading">
    <div>
        <h1>Programmers</h1>
        <p class="muted">Parent entity: one programmer can have many clients.</p>
    </div>
    <a class="button-link" href="programmer_form.php">+ Add Programmer</a>
</section>

<section class="panel">
    <h2>Search Programmer Records</h2>
    <form method="get" class="search-form">
        <label for="programmer-search">Search by ID, name, email, specialization, phone, or address</label>
        <div class="search-row">
            <input id="programmer-search" type="search" name="q" value="<?= e($search) ?>" placeholder="Enter a programmer detail...">
            <button type="submit">Search</button>
            <?php if ($search !== ''): ?><a class="button-link secondary" href="programmers.php">Clear</a><?php endif; ?>
        </div>
    </form>
</section>

<section class="panel">
    <h2><?= $search !== '' ? 'Search Results' : 'All Programmer Records' ?></h2>
    <?php if ($search !== '' && !$programmers): ?>
        <p class="muted">No programmer records matched "<?= e($search) ?>". Try another keyword.</p>
    <?php elseif (!$programmers): ?>
        <p class="muted">No programmers yet. Select “Add Programmer” to create the first record.</p>
    <?php else: ?>
        <?php if ($search !== ''): ?><p class="muted"><?= count($programmers) ?> matching record(s) found.</p><?php endif; ?>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>ID</th><th>Name</th><th>Email</th><th>Specialization</th><th>Phone</th><th>Address</th>
                    <th>Date Added</th><th>Added By</th><th>Last Updated</th><th>Last Updated By (User ID)</th><th>Actions</th>
                </tr></thead>
                <tbody>
                <?php foreach ($programmers as $p): ?>
                    <tr>
                        <td><?= (int) $p['id'] ?></td>
                        <td><?= e($p['first_name'] . ' ' . $p['last_name']) ?></td>
                        <td><?= e($p['email']) ?></td>
                        <td><?= e($p['specialization']) ?></td>
                        <td><?= e($p['phone']) ?></td>
                        <td><?= e($p['address']) ?></td>
                        <td><?= e($p['date_added']) ?></td>
                        <td><?= (int) $p['added_by'] ?> — <?= e($p['added_by_name']) ?></td>
                        <td><?= e($p['last_updated']) ?></td>
                        <td><?= (int) $p['last_updated_by'] ?> — <?= e($p['updated_by_name']) ?></td>
                        <td class="actions">
                            <a href="programmer_form.php?id=<?= (int) $p['id'] ?>&amp;q=<?= rawurlencode($search) ?>">Edit</a>
                            <form method="post" data-confirm="Delete this programmer?">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                <input type="hidden" name="return_q" value="<?= e($search) ?>">
                                <button class="small danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php require 'footer.php'; ?>
