<?php
include '../includes/db_connection.php';

$query = $pdo->query("SELECT * FROM classes ORDER BY id DESC");
$results = $query->fetchAll(PDO::FETCH_ASSOC);
$i = 1;
?>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Class Name</th>
                <th>Learning Area</th>
                <th>Year</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php if (count($results) > 0): ?>
            <?php foreach ($results as $row): ?>
            <tr>
                <td><?= $i++; ?></td>
                <td><?= $row['class_name']; ?></td>
                <td><?= $row['learning_area']; ?></td>
                <td>Year <?= $row['year_group']; ?></td>
                <td>
                    <a href="edit_class.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i>
                    </a>
                    <a href="../handlers/delete_class.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-danger"
                        onclick="return confirm('Are you sure?')">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <tr>
                <td colspan="5" class="text-center text-muted">No classes added yet.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>