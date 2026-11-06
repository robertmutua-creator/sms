<?php
$classes = $classes ?? [];
$class = $class ?? [];
$isEditing = !empty($class['id']);
?>
<section>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($classes === []): ?>
                    <tr>
                        <td colspan="100%">No classes found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($classes as $key => $rec): ?>
                        <tr>
                            <td>
                                <?= $key + 1 ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($rec['code']) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($rec['name']) ?>
                            </td>
                            <td>
                                <a href="/admin/classes/edit/<?= $rec['id'] ?>" class="btn-sm btn-edit">Edit</a>
                                <form id="delete-class-<?= $rec['id'] ?>" action="/admin/classes/delete/<?= $rec['id'] ?>" method="post" onsubmit="return confirm('Delete this class?');" hidden>
                                    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
                                    <input type="hidden" name="_method" value="DELETE">
                                </form>
                                <button class="btn-sm btn-delete" type="submit" form="delete-class-<?= $rec['id'] ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section>
    <h4><?= $isEditing ? 'Edit Class' : 'Add Class' ?></h4>
    <form action="<?= $isEditing ? '/admin/classes/update/' . $class['id'] : '/admin/classes/save' ?>" method="post">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
        <?php if ($isEditing): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>
        <div class="form-group">
            <label for="code">Class Code</label>
            <input type="text" name="code" id="code" placeholder="E.g. G10" value="<?= htmlspecialchars($class['code'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="name">Class Name</label>
            <input type="text" name="name" id="name" placeholder="E.g. Grade Ten" value="<?= htmlspecialchars($class['name'] ?? '') ?>">
        </div>

        <button class="btn-submit" type="submit"><?= $isEditing ? 'Update Class' : 'Add Class' ?></button>
        <?php if ($isEditing): ?>
            <a href="/admin/classes" class="btn-sm">Cancel</a>
        <?php endif; ?>
    </form>
</section>