<?php
$classes = $classes ?? [];
$years = $years ?? [];
$enrolledStudents = $enrolledStudents ?? [];
$unassignedStudents = $unassignedStudents ?? [];
$selectedClass = $selectedClass ?? null;
$selectedYear = $selectedYear ?? null;
?>

<section>
    <h3>Class Enrollment Management</h3>

    <form method="get" action="/admin/enrollments">
        <div class="grid">
            <div class="form-group">
                <label for="year_id">Academic year</label>
                <select name="year_id" id="year_id" onchange="this.form.submit()">
                    <option value="">-- Choose year --</option>
                    <?php foreach ($years as $year): ?>
                        <option value="<?= (int) $year['id'] ?>" <?= (string) $selectedYear === (string) $year['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) $year['year'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="class_id">Class</label>
                <select name="class_id" id="class_id" onchange="this.form.submit()">
                    <option value="">-- Choose class --</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= (int) $class['id'] ?>" <?= (string) $selectedClass === (string) $class['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) $class['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>

    <?php if ($selectedClass && $selectedYear): ?>
        <section>
            <h4>Students in this class (<?= count($enrolledStudents) ?>)</h4>
            <form method="post" action="/admin/enrollments/remove">
                <input type="hidden" name="token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="class_id" value="<?= (int) $selectedClass ?>">
                <input type="hidden" name="year_id" value="<?= (int) $selectedYear ?>">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAllEnrolled" aria-label="Select all enrolled students"></th>
                                <th>Admission number</th>
                                <th>Student name</th>
                                <th>Enrollment status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($enrolledStudents): ?>
                                <?php foreach ($enrolledStudents as $student): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="student_ids[]" value="<?= (int) $student['id'] ?>" class="enrolled-checkbox" aria-label="Select <?= htmlspecialchars((string) $student['name'], ENT_QUOTES, 'UTF-8') ?>">
                                        </td>
                                        <td><?= htmlspecialchars((string) ($student['admission_number'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) $student['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) $student['enrollment_status'], ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4">No students are enrolled in this class for the selected year.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn-submit btn-delete" <?= !$enrolledStudents ? 'disabled' : '' ?>>Remove selected students</button>
            </form>
        </section>

        <section>
            <h4>Students not assigned to a class this year (<?= count($unassignedStudents) ?>)</h4>
            <form method="post" action="/admin/enrollments/store">
                <input type="hidden" name="token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="class_id" value="<?= (int) $selectedClass ?>">
                <input type="hidden" name="year_id" value="<?= (int) $selectedYear ?>">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAllUnassigned" aria-label="Select all unassigned students"></th>
                                <th>Admission number</th>
                                <th>Student name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($unassignedStudents): ?>
                                <?php foreach ($unassignedStudents as $student): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="student_ids[]" value="<?= (int) $student['id'] ?>" class="unassigned-checkbox" aria-label="Select <?= htmlspecialchars((string) $student['name'], ENT_QUOTES, 'UTF-8') ?>">
                                        </td>
                                        <td><?= htmlspecialchars((string) ($student['admission_number'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) $student['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3">No unassigned students for the selected year.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn-submit" <?= !$unassignedStudents ? 'disabled' : '' ?>>Add selected students</button>
            </form>
        </section>
    <?php else: ?>
        <p>Select both an academic year and a class to see enrolled and unassigned students.</p>
    <?php endif; ?>
</section>

<script>
    document.getElementById('selectAllEnrolled')?.addEventListener('change', function () {
        document.querySelectorAll('.enrolled-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    document.getElementById('selectAllUnassigned')?.addEventListener('change', function () {
        document.querySelectorAll('.unassigned-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
</script>
