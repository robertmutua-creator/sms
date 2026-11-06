<?php
$teachers = $teachers ?? [];
$teacher = $teacher ?? [];
$isEditing = !empty($teacher['id']);
?>

<section>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Profile</th>
                    <th>Name</th>
                    <th>TSC Number</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($teachers === []): ?>
                    <tr>
                        <td colspan="6">No teachers found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($teachers as $key => $listedTeacher): ?>
                        <tr>
                            <td><?= $key + 1 ?></td>
                            <td>
                                <?php if (!empty($listedTeacher['profile_photo'])): ?>
                                    <img src="/<?= htmlspecialchars($listedTeacher['profile_photo'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($listedTeacher['name'] ?? 'Teacher', ENT_QUOTES, 'UTF-8') ?> profile photo" width="48">
                                <?php else: ?>
                                    &mdash;
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($listedTeacher['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($listedTeacher['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($listedTeacher['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a class="btn-sm btn-edit" href="/admin/teachers/edit/<?= (int) $listedTeacher['id'] ?>">Edit</a>
                                <form id="delete-teacher-<?= (int) $listedTeacher['id'] ?>" action="/admin/teachers/delete/<?= (int) $listedTeacher['id'] ?>" method="post" onsubmit="return confirm('Delete this teacher?');" hidden>
                                    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="_method" value="DELETE">
                                </form>
                                <button class="btn-sm btn-delete" type="submit" form="delete-teacher-<?= (int) $listedTeacher['id'] ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section>
    <h4><?= $isEditing ? 'Edit Teacher' : 'Add Teacher' ?></h4>
    <form action="<?= $isEditing ? '/admin/teachers/update/' . (int) $teacher['id'] : '/admin/teachers/save' ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <?php if ($isEditing): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>
        <div class="form-group">
            <label for="teacher-name">Name</label>
            <input type="text" id="teacher-name" name="name" value="<?= htmlspecialchars($teacher['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label for="teacher-tsc">TSC Number</label>
            <input type="text" id="teacher-tsc" name="tsc" value="<?= htmlspecialchars($teacher['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label for="teacher-email">Email</label>
            <input type="email" id="teacher-email" name="email" value="<?= htmlspecialchars($teacher['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <?php if (!$isEditing): ?>
            <p>The teacher's default password will be their email address.</p>
        <?php endif; ?>
        <div class="form-group">
            <label for="teacher-profile">Profile photo (optional)</label>
            <input type="file" id="teacher-profile" name="profile" accept="image/jpeg,image/png,image/gif,image/webp">
            <?php if (!empty($teacher['profile_photo'])): ?>
                <p>Current photo: <img src="/<?= htmlspecialchars($teacher['profile_photo'], ENT_QUOTES, 'UTF-8') ?>" alt="Current teacher profile photo" width="48"></p>
            <?php endif; ?>
        </div>
        <button class="btn-submit" type="submit"><?= $isEditing ? 'Update Teacher' : 'Add Teacher' ?></button>
        <?php if ($isEditing): ?>
            <a class="btn-sm" href="/admin/teachers">Cancel</a>
        <?php endif; ?>
    </form>
</section>
