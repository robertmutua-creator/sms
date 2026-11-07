<div class="dashboard-cards">
    <article class="card">
        <h4>Total Students</h4>
        <p class="stat"><?= htmlspecialchars($studentsCount ?? '--')  ?></p>
    </article>

    <article class="card">
        <h4>Total Teachers</h4>
        <p class="stat"><?= htmlspecialchars($teachersCount ?? '--')  ?></p>
    </article>

    <article class="card">
        <h4>Total Streams</h4>
        <p class="stat"><?= htmlspecialchars($classesCount ?? '--')  ?></p>
    </article>

    <article class="card">
        <h4>Total Subjects</h4>
        <p class="stat"><?= htmlspecialchars($subjectsCount ?? '--')  ?></p>
    </article>

    <article class="card">
        <h4>Current Academic Year</h4>
        <p class="stat">
            <?= htmlspecialchars($currentAcademicYear === null ? 'Not set' : (string) $currentAcademicYear, ENT_QUOTES, 'UTF-8') ?>
        </p>
        <a href="/admin/years">Manage Academic Years</a>
    </article>
</div>