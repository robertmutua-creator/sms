<?php
$admins = $admins ?? [];
$schools = $schools ?? [];
$admin = $admin ?? [];
$isEditing = !empty($admin['id']);
?>

<section>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Profile</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>School</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($admins === []): ?>
                    <tr>
                        <td colspan="6">No admins found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($admins as $key => $listedAdmin): ?>
                        <tr>
                            <td><?= $key + 1 ?></td>
                            <td>
                                <?php if (!empty($listedAdmin['profile_photo'])): ?>
                                    <img src="/<?= htmlspecialchars($listedAdmin['profile_photo'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($listedAdmin['full_name'], ENT_QUOTES, 'UTF-8') ?> profile photo" width="48">
                                <?php else: ?>
                                    &mdash;
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($listedAdmin['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($listedAdmin['username'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($listedAdmin['school_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a class="btn-sm btn-edit" href="/superadmin/admins/edit/<?= (int) $listedAdmin['id'] ?>">Edit</a>
                                <form id="delete-admin-<?= (int) $listedAdmin['id'] ?>" action="/superadmin/admins/delete/<?= (int) $listedAdmin['id'] ?>" method="post" onsubmit="return confirm('Delete this admin?');" hidden>
                                    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                </form>
                                <button class="btn-sm btn-delete" type="submit" form="delete-admin-<?= (int) $listedAdmin['id'] ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif;?>
            </tbody>
        </table>
    </div>
</section>

<section>
    <h4><?= $isEditing ? 'Edit School Admin' : 'Add School Admin' ?></h4>
    <form action="<?= $isEditing ? '/superadmin/admins/update/' . (int) $admin['id'] : '/superadmin/admins/save' ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <div class="form-group">
            <label for="full_name">Full name</label>
            <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($admin['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($admin['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label for="profile_photo">Profile photo <?= $isEditing ? '(optional)' : '' ?></label>
            <input type="file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png,image/gif,image/webp" <?= $isEditing ? '' : 'required' ?>>
            <?php if (!empty($admin['profile_photo'])): ?>
                <p>Current photo: <img src="/<?= htmlspecialchars($admin['profile_photo'], ENT_QUOTES, 'UTF-8') ?>" alt="Current admin profile photo" width="48"></p>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="school_id">School</label>
            <select id="school_id" name="school_id" required>
                <option value="">Select a school</option>
                <?php foreach ($schools as $school): ?>
                    <option value="<?= (int) $school['id'] ?>" <?= (int) ($admin['school_id'] ?? 0) === (int) $school['id'] ? 'selected' : '' ?>><?= htmlspecialchars($school['name'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <p>The default password will be the admin's username.</p>
        <button class="btn-submit" type="submit"><?= $isEditing ? 'Update Admin' : 'Add Admin' ?></button>
        <?php if ($isEditing): ?>
            <a class="btn-sm" href="/superadmin/admins">Cancel</a>
        <?php endif; ?>
    </form>
</section>