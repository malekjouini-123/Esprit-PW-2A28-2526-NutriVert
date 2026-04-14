<?php
$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Coaching Program</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f6f8f7; color: #1f2937; }
        .wrap { max-width: 760px; margin: 2rem auto; padding: 0 1rem; }
        .card { background: #fff; border: 1px solid #d9e2dd; border-radius: 12px; padding: 1rem; }
        h1 { color: #14532d; font-size: 1.3rem; margin-bottom: 1rem; }
        .flash { margin-bottom: 1rem; padding: 0.75rem 0.85rem; border: 1px solid #a7f3d0; border-radius: 10px; background: #ecfdf5; color: #065f46; font-size: 0.88rem; font-weight: 600; }
        .group { margin-bottom: 0.7rem; }
        label { display: block; font-size: 0.84rem; margin-bottom: 0.3rem; font-weight: 600; color: #374151; }
        input, textarea, select { width: 100%; border: 1px solid #cfd8d3; border-radius: 8px; padding: 0.62rem 0.7rem; font-size: 0.9rem; font-family: inherit; }
        textarea { resize: vertical; min-height: 100px; }
        .actions { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.8rem; }
        .btn { border: 1px solid #166534; background: #166534; color: #fff; text-decoration: none; border-radius: 8px; padding: 0.5rem 0.85rem; font-size: 0.84rem; font-weight: 600; cursor: pointer; }
        .btn-light { background: #fff; color: #166534; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>Add Coaching Program</h1>

        <?php if (!empty($flashMessage)): ?>
            <div class="flash"><?= $e($flashMessage) ?></div>
        <?php endif; ?>

        <form method="post" action="index.php?controller=coaching&action=store">
            <div class="group">
                <label>Title *</label>
                <input type="text" name="title" required>
            </div>
            <div class="group">
                <label>Description *</label>
                <textarea name="description" required></textarea>
            </div>
            <div class="group">
                <label>Duration weeks *</label>
                <input type="number" name="duration_weeks" min="1" required>
            </div>
            <div class="group">
                <label>Difficulty *</label>
                <select name="difficulty_level" required>
                    <option value="">Choose</option>
                    <option value="easy">Easy</option>
                    <option value="medium">Medium</option>
                    <option value="hard">Hard</option>
                </select>
            </div>
            <div class="actions">
                <button type="submit" class="btn">Create</button>
                <a href="index.php?controller=coaching&action=index" class="btn btn-light">Back to Coaching Programs</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
