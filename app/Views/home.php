<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CBE Assessment Management System</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        header {
            border-bottom: 1px solid #eaeaea;
            padding-bottom: 20px;
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        h1 {
            font-size: 1.8rem;
            margin: 0;
            color: #111;
        }

        .badge {
            background: #e0f2fe;
            color: #0369a1;
            padding: 4px 10px;
            font-size: 0.85rem;
            border-radius: 4px;
            font-weight: 500;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .card {
            border: 1px solid #e5e7eb;
            padding: 20px;
            border-radius: 6px;
            background: #f9fafb;
        }

        .btn {
            display: inline-block;
            background: #0f172a;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            margin-top: 20px;
        }

        .btn:hover {
            background: #334155;
        }

        footer {
            margin-top: 60px;
            font-size: 0.85rem;
            color: #666;
            border-top: 1px solid #eaeaea;
            padding-top: 20px;
        }
    </style>
</head>

<body>

    <header>
        <h1>SMS</h1>
        <span class="badge">School Portal</span>
    </header>

    <main>
        <h2>Competency-Based Education Assessment Management</h2>
        <p>A streamlined, secure platform built to track, manage, and report student progress effortlessly.</p>

        <div class="grid">
            <div class="card">
                <h3>Formative Assessments</h3>
                <p>Track ongoing learning, practical tasks, and portfolio development smoothly throughout the term.</p>
            </div>
            <div class="card">
                <h3>Summative Assessments</h3>
                <p>Record end-of-cycle evaluations and compute final competency outcomes with complete precision.</p>
            </div>
        </div>

        <a href="/login" class="btn">Access School Portal</a>
    </main>

    <footer>
        &copy; <?= date('Y'); ?> School Management System &bull; Developed by mutuaaps
    </footer>

</body>

</html>