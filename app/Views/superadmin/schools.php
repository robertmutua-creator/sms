<?php
$schools = $schools ?? [];
$school = $school ?? [];
$isEditing = !empty($school['id']);
?>

<section>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Logo</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($schools === []): ?>
                    <tr>
                        <td colspan="7">No schools found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($schools as $listedSchool): ?>
                        <tr>
                            <td>
                                <?php if (!empty($listedSchool['logo_path'])): ?>
                                    <img src="/<?= htmlspecialchars($listedSchool['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($listedSchool['name'], ENT_QUOTES, 'UTF-8') ?> logo" width="48">
                                <?php else: ?>
                                    &mdash;
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($listedSchool['code'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($listedSchool['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($listedSchool['username'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($listedSchool['address'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($listedSchool['status'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a class="btn-sm btn-edit" href="/superadmin/schools/edit/<?= (int) $listedSchool['id'] ?>">Edit</a>
                                <form id="delete-school-<?= (int) $listedSchool['id'] ?>" action="/superadmin/schools/delete/<?= (int) $listedSchool['id'] ?>" method="post" onsubmit="return confirm('Delete this school?');" hidden>
                                </form>
                                <button class="btn-sm btn-delete" type="submit" form="delete-school-<?= (int) $listedSchool['id'] ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section>
    <h4><?= $isEditing ? 'Edit School' : 'Add School' ?></h4>
    <form action="<?= $isEditing ? '/superadmin/schools/update/' . (int) $school['id'] : '/superadmin/schools/save' ?>" method="post" enctype="multipart/form-data">

        <div class="form-group">
            <label for="code">School code</label>
            <input type="text" id="code" name="code" value="<?= htmlspecialchars($school['code'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label for="name">School name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($school['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($school['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" required><?= htmlspecialchars($school['address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="Active" <?= ($school['status'] ?? 'Active') === 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="Inactive" <?= ($school['status'] ?? '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <div class="form-group">
            <label for="logo">Logo <?= $isEditing ? '(optional)' : '' ?></label>
            <input type="file" id="logo" name="logo" accept="image/*" <?= $isEditing ? '' : 'required' ?>>
            <?php if (!empty($school['logo_path'])): ?>
                <p>Current logo: <img src="/<?= htmlspecialchars($school['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Current school logo" width="64"></p>
            <?php endif; ?>
        </div>
        <button class="btn-submit" type="submit"><?= $isEditing ? 'Update School' : 'Add School' ?></button>
        <?php if ($isEditing): ?>
            <a class="btn-sm" href="/superadmin/schools">Cancel</a>
        <?php endif; ?>
    </form>
</section>