<?php
$terms = $terms ?? [];
$term = $term ?? [];
$years = $years ?? [];
$yearLabels = $yearLabels ?? [];
$defaultYearId = $defaultYearId ?? null;
$isEditing = !empty($term['id']);
?>
<section>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Term</th>
                    <th>Academic Year</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($terms === []): ?>
                    <tr>
                        <td colspan="5">No terms found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($terms as $key => $record): ?>
                        <tr>
                            <td><?= $key + 1 ?></td>
                            <td><?= htmlspecialchars('Term ' . $record['term'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($yearLabels[$record['year_id']] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(ucfirst($record['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a href="/admin/terms/edit/<?= (int) $record['id'] ?>" class="btn-sm btn-edit">Edit</a>
                                <form id="delete-term-<?= (int) $record['id'] ?>" action="/admin/terms/delete/<?= (int) $record['id'] ?>" method="post" onsubmit="return confirm('Delete this term?');" hidden>
                                    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="_method" value="DELETE">
                                </form>
                                <button class="btn-sm btn-delete" type="submit" form="delete-term-<?= (int) $record['id'] ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section>
    <h4><?= $isEditing ? 'Edit Term' : 'Add Term' ?></h4>
    <form action="<?= $isEditing ? '/admin/terms/update/' . (int) $term['id'] : '/admin/terms/save' ?>" method="post">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <?php if ($isEditing): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>
        <div class="form-group">
            <label for="term">Term</label>
            <select name="term" id="term" required>
                <?php foreach (['1', '2', '3'] as $termNumber): ?>
                    <option value="<?= $termNumber ?>" <?= (string) ($term['term'] ?? '1') === $termNumber ? 'selected' : '' ?>>Term <?= $termNumber ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="year_id">Academic Year</label>
            <select name="year_id" id="year_id" required>
                <option value="">Select an academic year</option>
                <?php foreach ($years as $yearRecord): ?>
                    <option value="<?= (int) $yearRecord['id'] ?>" <?= (string) $defaultYearId === (string) $yearRecord['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string) $yearRecord['year'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status" required>
                <?php foreach (['upcoming' => 'Upcoming', 'current' => 'Current', 'completed' => 'Completed'] as $statusValue => $statusLabel): ?>
                    <option value="<?= $statusValue ?>" <?= ($term['status'] ?? 'upcoming') === $statusValue ? 'selected' : '' ?>><?= $statusLabel ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn-submit" type="submit"><?= $isEditing ? 'Update Term' : 'Add Term' ?></button>
        <?php if ($isEditing): ?>
            <a href="/admin/terms" class="btn-sm">Cancel</a>
        <?php endif; ?>
    </form>
</section>
