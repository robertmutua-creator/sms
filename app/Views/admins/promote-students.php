<?php
$classes = $classes ?? [];
$years = $years ?? [];
$enrolledStudents = $enrolledStudents ?? [];
$selectedSourceClass = $selectedSourceClass ?? null;
$selectedSourceYear = $selectedSourceYear ?? null;
?>

<section>
    <form method="get" action="/admin/promote-students">
        <div class="grid">
            <div class="form-group">
                <label for="source_year_id">Current academic year</label>
                <select name="source_year_id" id="source_year_id" required>
                    <option value="">-- Choose year --</option>
                    <?php foreach ($years as $year): ?>
                        <option value="<?= (int) $year['id'] ?>" <?= (string) $selectedSourceYear === (string) $year['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) $year['year'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="source_class_id">Current class</label>
                <select name="source_class_id" id="source_class_id" required>
                    <option value="">-- Choose class --</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= (int) $class['id'] ?>" <?= (string) $selectedSourceClass === (string) $class['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) $class['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn-submit">Load students</button>
    </form>

    <?php if ($selectedSourceClass && $selectedSourceYear): ?>
        <section>
            <h4>Students in the selected class (<?= count($enrolledStudents) ?>)</h4>
            <?php if ($enrolledStudents !== []): ?>
                <form method="post" action="/admin/promote-students/store">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="source_class_id" value="<?= (int) $selectedSourceClass ?>">
                    <input type="hidden" name="source_year_id" value="<?= (int) $selectedSourceYear ?>">

                    <div class="grid">
                        <div class="form-group">
                            <label for="target_year_id">New academic year</label>
                            <select name="target_year_id" id="target_year_id" required>
                                <option value="">-- Choose year --</option>
                                <?php foreach ($years as $year): ?>
                                    <?php if ((int) $year['id'] !== (int) $selectedSourceYear): ?>
                                        <option value="<?= (int) $year['id'] ?>">
                                            <?= htmlspecialchars((string) $year['year'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="target_class_id">New class</label>
                            <select name="target_class_id" id="target_class_id" required>
                                <option value="">-- Choose class --</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?= (int) $class['id'] ?>">
                                        <?= htmlspecialchars((string) $class['name'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAllPromotion" aria-label="Select all students"></th>
                                    <th>Admission number</th>
                                    <th>Student name</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enrolledStudents as $student): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="student_ids[]" value="<?= (int) $student['id'] ?>" class="promotion-checkbox" aria-label="Select <?= htmlspecialchars((string) $student['name'], ENT_QUOTES, 'UTF-8') ?>">
                                        </td>
                                        <td><?= htmlspecialchars((string) ($student['admission_number'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) $student['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn-submit">Promote selected students</button>
                </form>
            <?php else: ?>
                <p>No students are enrolled in this class for the selected year.</p>
            <?php endif; ?>
        </section>
    <?php else: ?>
        <p>Select a current academic year and class to see enrolled students.</p>
    <?php endif; ?>
</section>

<script>
    document.getElementById('selectAllPromotion')?.addEventListener('change', function() {
        document.querySelectorAll('.promotion-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
</script>
