<?php
$subjects = $subjects ?? [];
$subject = $subject ?? [];
$isEditing = !empty($subject['id']);
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
                <?php if ($subjects === []): ?>
                    <tr>
                        <td colspan="100%">No subjects found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($subjects as $key => $rec): ?>
                        <tr>
                            <td><?= $key + 1 ?></td>
                            <td><?= htmlspecialchars($rec['code']) ?></td>
                            <td><?= htmlspecialchars($rec['name']) ?></td>
                            <td>
                                <a href="/admin/subjects/edit/<?= (int) $rec['id'] ?>" class="btn-sm btn-edit">Edit</a>
                                <form id="delete-subject-<?= (int) $rec['id'] ?>" action="/admin/subjects/delete/<?= (int) $rec['id'] ?>" method="post" onsubmit="return confirm('Delete this subject?');" hidden>
                                    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
                                    <input type="hidden" name="_method" value="DELETE">
                                </form>
                                <button class="btn-sm btn-delete" type="submit" form="delete-subject-<?= (int) $rec['id'] ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section>
    <h4><?= $isEditing ? 'Edit Subject' : 'Add Subject' ?></h4>
    <form action="<?= $isEditing ? '/admin/subjects/update/' . (int) $subject['id'] : '/admin/subjects/save' ?>" method="post">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
        <?php if ($isEditing): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>
        <div class="form-group">
            <label for="code">Subject Code</label>
            <input type="text" name="code" id="code" placeholder="E.g. ENG" value="<?= htmlspecialchars($subject['code'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="name">Subject Name</label>
            <input type="text" name="name" id="name" placeholder="E.g. English" value="<?= htmlspecialchars($subject['name'] ?? '') ?>">
        </div>

        <button class="btn-submit" type="submit"><?= $isEditing ? 'Update Subject' : 'Add Subject' ?></button>
        <?php if ($isEditing): ?>
            <a href="/admin/subjects" class="btn-sm">Cancel</a>
        <?php endif; ?>
    </form>
</section>
