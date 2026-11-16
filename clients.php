<?php
require_once 'config.php';
require_once 'functions.php';
require_login();

$search = trim((string) ($_GET['q'] ?? ''));
$search = function_exists('mb_substr') ? mb_substr($search, 0, 100, 'UTF-8') : substr($search, 0, 100);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    $returnSearch = trim((string) ($_POST['return_q'] ?? ''));
    $check = $pdo->prepare('SELECT id, first_name, last_name FROM clients WHERE id = ?');
    $check->execute([$id]);
    $client = $check->fetch();

    if (!$client) {
        set_flash('Client not found.', 'error');
    } else {
        try {
            $pdo->beginTransaction();
            $delete = $pdo->prepare('DELETE FROM clients WHERE id = ?');
            $delete->execute([$id]);
            log_activity($pdo, 'clients', $id, 'DELETE', 'Deleted client: ' . $client['first_name'] . ' ' . $client['last_name']);
            $pdo->commit();
            set_flash('Client deleted. The action remains in Activity Logs.');
        } catch (PDOException $error) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            set_flash('The client could not be deleted.', 'error');
        }
    }

    $url = 'clients.php';
    if ($returnSearch !== '') $url .= '?q=' . rawurlencode($returnSearch);
    redirect($url);
}

$sql = 'SELECT c.*,
        CONCAT(p.first_name, " ", p.last_name) AS programmer_name,
        CONCAT(u1.first_name, " ", u1.last_name) AS added_by_name,
        CONCAT(u2.first_name, " ", u2.last_name) AS updated_by_name
        FROM clients c
        JOIN programmers p ON c.programmer_id = p.id
        JOIN users u1 ON c.added_by = u1.id
        JOIN users u2 ON c.last_updated_by = u2.id';

if ($search !== '') {
    $sql .= ' WHERE CAST(c.id AS CHAR) LIKE ?
              OR c.first_name LIKE ?
              OR c.last_name LIKE ?
              OR CONCAT(c.first_name, " ", c.last_name) LIKE ?
              OR c.email LIKE ?
              OR c.phone LIKE ?
              OR c.company_name LIKE ?
              OR c.project_name LIKE ?
              OR CONCAT(p.first_name, " ", p.last_name) LIKE ?';
    $term = '%' . $search . '%';
    $statement = $pdo->prepare($sql . ' ORDER BY c.id DESC');
    $statement->execute(array_fill(0, 9, $term));
    $clients = $statement->fetchAll();
} else {
    $clients = $pdo->query($sql . ' ORDER BY c.id DESC')->fetchAll();
}

require 'header.php';
?>
<section class="page-heading">
    <div>
        <h1>Clients</h1>
        <p class="muted">Child entity: every client is assigned to one programmer.</p>
    </div>
    <a class="button-link" href="client_form.php">+ Add Client</a>
</section>

<section class="panel">
    <h2>Search Client Records</h2>
    <form method="get" class="search-form">
        <label for="client-search">Search by ID, client name, email, phone, company, project, or programmer</label>
        <div class="search-row">
            <input id="client-search" type="search" name="q" value="<?= e($search) ?>" placeholder="Enter a client or programmer detail...">
            <button type="submit">Search</button>
            <?php if ($search !== ''): ?><a class="button-link secondary" href="clients.php">Clear</a><?php endif; ?>
        </div>
    </form>
</section>

<section class="panel">
    <h2><?= $search !== '' ? 'Search Results' : 'All Client Records' ?></h2>
    <?php if ($search !== '' && !$clients): ?>
        <p class="muted">No client records matched "<?= e($search) ?>". Try another keyword.</p>
    <?php elseif (!$clients): ?>
        <p class="muted">No clients yet. Add a programmer first, then create client records.</p>
    <?php else: ?>
        <?php if ($search !== ''): ?><p class="muted"><?= count($clients) ?> matching record(s) found.</p><?php endif; ?>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>ID</th><th>Client Name</th><th>Company</th><th>Project</th><th>Email</th><th>Phone</th>
                    <th>Assigned Programmer</th><th>Date Added</th><th>Added By</th><th>Last Updated</th><th>Last Updated By (User ID)</th><th>Actions</th>
                </tr></thead>
                <tbody>
                <?php foreach ($clients as $c): ?>
                    <tr>
                        <td><?= (int) $c['id'] ?></td>
                        <td><?= e($c['first_name'] . ' ' . $c['last_name']) ?></td>
                        <td><?= e($c['company_name']) ?></td>
                        <td><?= e($c['project_name']) ?></td>
                        <td><?= e($c['email']) ?></td>
                        <td><?= e($c['phone']) ?></td>
                        <td><?= e($c['programmer_name']) ?></td>
                        <td><?= e($c['date_added']) ?></td>
                        <td><?= (int) $c['added_by'] ?> — <?= e($c['added_by_name']) ?></td>
                        <td><?= e($c['last_updated']) ?></td>
                        <td><?= (int) $c['last_updated_by'] ?> — <?= e($c['updated_by_name']) ?></td>
                        <td class="actions">
                            <a href="client_form.php?id=<?= (int) $c['id'] ?>&amp;q=<?= rawurlencode($search) ?>">Edit</a>
                            <form method="post" data-confirm="Delete this client?">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
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
