<?php
$years = $years ?? [];
$year = $year ?? [];
$isEditing = !empty($year['id']);
?>
<section>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Academic Year</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($years === []): ?>
                    <tr>
                        <td colspan="4">No academic years found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($years as $key => $record): ?>
                        <tr>
                            <td><?= $key + 1 ?></td>
                            <td><?= htmlspecialchars((string) $record['year'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(ucfirst($record['status']), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a href="/admin/years/edit/<?= (int) $record['id'] ?>" class="btn-sm btn-edit">Edit</a>
                                <form id="delete-year-<?= (int) $record['id'] ?>" action="/admin/years/delete/<?= (int) $record['id'] ?>" method="post" onsubmit="return confirm('Delete this academic year?');" hidden>
                                    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="_method" value="DELETE">
                                </form>
                                <button class="btn-sm btn-delete" type="submit" form="delete-year-<?= (int) $record['id'] ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section>
    <h4><?= $isEditing ? 'Edit Academic Year' : 'Add Academic Year' ?></h4>
    <form action="<?= $isEditing ? '/admin/years/update/' . (int) $year['id'] : '/admin/years/save' ?>" method="post">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <?php if ($isEditing): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>
        <div class="form-group">
            <label for="year">Academic Year</label>
            <input type="number" name="year" id="year" min="1" max="9999" step="1" value="<?= htmlspecialchars((string) ($year['year'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status" required>
                <option value="active" <?= ($year['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active (current)</option>
                <option value="inactive" <?= ($year['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <button class="btn-submit" type="submit"><?= $isEditing ? 'Update Academic Year' : 'Add Academic Year' ?></button>
        <?php if ($isEditing): ?>
            <a href="/admin/years" class="btn-sm">Cancel</a>
        <?php endif; ?>
    </form>
</section>
