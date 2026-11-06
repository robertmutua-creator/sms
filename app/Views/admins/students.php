<?php
$students = $students ?? [];
$student = $student ?? [];
$isEditing = !empty($student['id']);
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
                    <th>UPI</th>
                    <th>Date Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($students === []): ?>
                    <tr>
                        <td colspan="7">No students found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $key => $listedStudent): ?>
                        <tr>
                            <td><?= $key + 1 ?></td>
                            <td>
                                <?php if (!empty($listedStudent['profile_photo'])): ?>
                                    <img src="/<?= htmlspecialchars($listedStudent['profile_photo'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($listedStudent['name'] ?? 'Student', ENT_QUOTES, 'UTF-8') ?> profile photo" width="48">
                                <?php else: ?>
                                    &mdash;
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($listedStudent['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($listedStudent['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($listedStudent['upi'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($listedStudent['created_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a class="btn-sm btn-edit" href="/admin/students/edit/<?= (int) $listedStudent['id'] ?>">Edit</a>
                                <form id="delete-student-<?= (int) $listedStudent['id'] ?>" action="/admin/students/delete/<?= (int) $listedStudent['id'] ?>" method="post" onsubmit="return confirm('Delete this student?');" hidden>
                                    <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="_method" value="DELETE">
                                </form>
                                <button class="btn-sm btn-delete" type="submit" form="delete-student-<?= (int) $listedStudent['id'] ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section>
    <h4><?= $isEditing ? 'Edit Student' : 'Add Student' ?></h4>
    <form action="<?= $isEditing ? '/admin/students/update/' . (int) $student['id'] : '/admin/students/save' ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <?php if ($isEditing): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>
        <div class="form-group">
            <label for="student-name">Full name</label>
            <input type="text" id="student-name" name="name" value="<?= htmlspecialchars($student['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label for="student-adm">Admission number</label>
            <input type="text" id="student-adm" name="adm" value="<?= htmlspecialchars($student['admission_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label for="student-upi">UPI</label>
            <input type="text" id="student-upi" name="upi" value="<?= htmlspecialchars($student['upi'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="form-group">
            <label for="student-status">Status</label>
            <select id="student-status" name="status">
                <option value="Active" <?= ($student['status'] ?? 'Active') === 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="Inactive" <?= ($student['status'] ?? '') === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <div class="form-group">
            <label for="student-profile">Profile photo (optional)</label>
            <input type="file" id="student-profile" name="profile" accept="image/jpeg,image/png,image/gif,image/webp">
            <?php if (!empty($student['profile_photo'])): ?>
                <p>Current photo: <img src="/<?= htmlspecialchars($student['profile_photo'], ENT_QUOTES, 'UTF-8') ?>" alt="Current student profile photo" width="48"></p>
            <?php endif; ?>
        </div>
        <button class="btn-submit" type="submit"><?= $isEditing ? 'Update Student' : 'Add Student' ?></button>
        <?php if ($isEditing): ?>
            <a class="btn-sm" href="/admin/students">Cancel</a>
        <?php endif; ?>
    </form>
</section>

<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="/admin/students/import" method="POST" enctype="multipart/form-data" class="modal-content">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Students via Spreadsheet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label for="spreadsheet">Select Excel or CSV File</label>
                    <input type="file" id="spreadsheet" name="spreadsheet" accept=".xlsx, .xls, .csv" required>
                    <p class="help-text">Required Columns: Full Name (A), Admission Number (B), UPI (C). Row 1 headers.</p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-submit">Import Records</button>
            </div>
        </form>
    </div>
</div>