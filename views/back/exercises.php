<?php
$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$coachingId = (int)$coachingId;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | Exercises</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f6f8f7; color: #1f2937; }
        .wrap { max-width: 1100px; margin: 2rem auto; padding: 0 1rem; }
        .panel { background: #fff; border: 1px solid #d9e2dd; border-radius: 12px; padding: 1rem; }
        .toolbar { display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.8rem; }
        h1 { color: #14532d; font-size: 1.2rem; }
        .flash { margin-bottom: 0.9rem; padding: 0.75rem 0.85rem; border: 1px solid #a7f3d0; border-radius: 10px; background: #ecfdf5; color: #065f46; font-size: 0.88rem; font-weight: 600; }
        .btn { border: 1px solid #166534; background: #166534; color: #fff; text-decoration: none; border-radius: 8px; padding: 0.5rem 0.8rem; font-size: 0.84rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.3rem; }
        .btn-light { background: #fff; color: #166534; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #edf2ef; padding: 0.72rem 0.6rem; text-align: left; font-size: 0.88rem; vertical-align: top; }
        th { background: #f4faf6; color: #14532d; font-size: 0.8rem; text-transform: uppercase; }
        .actions { display: flex; gap: 0.35rem; flex-wrap: wrap; }
    </style>
</head>
<body>
<div class="wrap">
    <?php if (!empty($flashMessage)): ?>
        <div class="flash"><?= $e($flashMessage) ?></div>
    <?php endif; ?>

    <section class="panel">
        <div class="toolbar">
            <h1>Exercises - <?= $e($selectedProgram['title'] ?? ('Program #' . $coachingId)) ?></h1>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                <a class="btn" href="index.php?controller=dashboard&action=index&view=exercises_create&coaching_id=<?= $coachingId ?>">Add Exercise</a>
                <a class="btn btn-light" href="index.php?controller=dashboard&action=index">Back to Coaching Programs</a>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Sets</th>
                    <th>Reps</th>
                    <th>Rest Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($exercises === []): ?>
                    <tr><td colspan="5">No exercises found for this coaching program.</td></tr>
                <?php endif; ?>

                <?php foreach ($exercises as $exercise): ?>
                    <tr>
                        <td><?= $e($exercise['name']) ?></td>
                        <td><?= (int)$exercise['sets'] ?></td>
                        <td><?= (int)$exercise['reps'] ?></td>
                        <td><?= $e($exercise['rest_time']) ?></td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-light" href="index.php?controller=dashboard&action=index&view=exercises_edit&id=<?= (int)$exercise['id'] ?>">Edit</a>
                                <a class="btn btn-light" href="index.php?controller=exercise&action=delete&id=<?= (int)$exercise['id'] ?>&redirect=dashboard&coaching_id=<?= $coachingId ?>" data-confirm>Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>

<script>
    document.querySelectorAll('[data-confirm]').forEach(link => {
        link.addEventListener('click', event => {
            if (!confirm('Confirm delete?')) {
                event.preventDefault();
            }
        });
    });
</script>
</body>
</html>
