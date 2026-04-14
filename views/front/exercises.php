<?php
$e = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$currentCoachingId = (int)($filterCoachingId ?? 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriVert | Exercises</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f6f8f7; color: #1f2937; }
        header { background: #fff; border-bottom: 1px solid #d9e2dd; padding: 1rem 1.2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.8rem; }
        .logo { font-weight: 700; color: #14532d; }
        nav { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        nav a { text-decoration: none; color: #374151; font-size: 0.9rem; font-weight: 600; padding: 0.45rem 0.7rem; border-radius: 8px; }
        nav a:hover { background: #edf2ef; }
        .nav-admin { background: #166534; color: #fff; }
        .nav-admin:hover { background: #14532d; color: #fff; }
        .wrapper { max-width: 1120px; margin: 0 auto; padding: 1.1rem; }
        .panel { background: #fff; border: 1px solid #d9e2dd; border-radius: 12px; padding: 1rem; margin-bottom: 1rem; }
        .title { color: #14532d; font-size: 1.1rem; margin-bottom: 0.7rem; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 0.5rem; justify-content: space-between; align-items: center; margin-bottom: 0.8rem; }
        .filter { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.6rem; }
        input, select { width: 100%; border: 1px solid #cfd8d3; border-radius: 8px; padding: 0.6rem 0.7rem; font-size: 0.9rem; font-family: inherit; }
        .btn { border: 1px solid #166534; background: #166534; color: #fff; text-decoration: none; border-radius: 8px; padding: 0.5rem 0.8rem; font-size: 0.84rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.3rem; cursor: pointer; }
        .btn:hover { background: #14532d; }
        .btn-light { background: #fff; color: #166534; }
        .flash { margin-bottom: 1rem; padding: 0.75rem 0.85rem; border: 1px solid #a7f3d0; border-radius: 10px; background: #ecfdf5; color: #065f46; font-size: 0.88rem; font-weight: 600; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 0.8rem; }
        .card { background: #fff; border: 1px solid #d9e2dd; border-radius: 12px; overflow: hidden; }
        .card img { width: 100%; height: 170px; object-fit: cover; }
        .placeholder { width: 100%; height: 170px; display: grid; place-items: center; background: #eef2ef; color: #6b7280; }
        .content { padding: 0.9rem; }
        .content h3 { color: #14532d; font-size: 1rem; margin-bottom: 0.45rem; }
        .content p { color: #4b5563; font-size: 0.86rem; margin-bottom: 0.3rem; }
        .actions { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.5rem; }
    </style>
</head>
<body>
<header>
    <div class="logo">NutriVert</div>
    <nav>
        <a href="index.php?controller=coaching&action=index">Back to Coaching Programs</a>
        <a href="index.php?controller=exercise&action=index">Exercises</a>
        <a class="nav-admin" href="index.php?controller=dashboard&action=index">Admin Dashboard</a>
    </nav>
</header>

<div class="wrapper">
    <?php if (!empty($flashMessage)): ?>
        <div class="flash"><?= $e($flashMessage) ?></div>
    <?php endif; ?>

    <section class="panel">
        <div class="toolbar">
            <h1 class="title">Exercises<?= $currentCoachingId > 0 ? ' - Program #' . $currentCoachingId : '' ?></h1>
            <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                <a class="btn" href="index.php?controller=exercise&action=create<?= $currentCoachingId > 0 ? '&coaching_id=' . $currentCoachingId : '' ?>"><i class="fas fa-plus"></i> Add Exercise</a>
                <a class="btn btn-light" href="index.php?controller=coaching&action=index">Back to Coaching Programs</a>
            </div>
        </div>

        <form method="get" class="filter">
            <input type="hidden" name="page" value="exercises">
            <input type="hidden" name="action" value="index">
            <select name="coaching_id">
                <option value="0">All programs</option>
                <?php foreach ($coachingPrograms as $program): ?>
                    <option value="<?= (int)$program['id'] ?>" <?= $currentCoachingId === (int)$program['id'] ? 'selected' : '' ?>>
                        <?= $e($program['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="keyword" placeholder="Search by exercise" value="<?= $e($keyword ?? '') ?>">
            <select name="sort_column">
                <option value="">Sort by</option>
                <option value="sets" <?= (($sortColumn ?? '') === 'sets') ? 'selected' : '' ?>>Sets</option>
                <option value="reps" <?= (($sortColumn ?? '') === 'reps') ? 'selected' : '' ?>>Reps</option>
                <option value="created_at" <?= (($sortColumn ?? '') === 'created_at') ? 'selected' : '' ?>>Date</option>
            </select>
            <select name="sort_order">
                <option value="asc" <?= (($sortOrder ?? '') === 'asc') ? 'selected' : '' ?>>ASC</option>
                <option value="desc" <?= (($sortOrder ?? 'desc') === 'desc') ? 'selected' : '' ?>>DESC</option>
            </select>
            <button type="submit" class="btn"><i class="fas fa-filter"></i> Filter</button>
            <a class="btn btn-light" href="index.php?controller=exercise&action=index<?= $currentCoachingId > 0 ? '&coaching_id=' . $currentCoachingId : '' ?>">Reset</a>
        </form>
    </section>

    <section class="grid">
        <?php if ($exercises === []): ?>
            <article class="card">
                <div class="content">
                    <h3>No exercises found</h3>
                    <p>Try another filter or add a new exercise.</p>
                </div>
            </article>
        <?php endif; ?>

        <?php foreach ($exercises as $exercise): ?>
            <article class="card">
                <?php if (!empty($exercise['image'])): ?>
                    <img src="<?= $e($exercise['image']) ?>" alt="<?= $e($exercise['name']) ?>">
                <?php else: ?>
                    <div class="placeholder"><i class="fas fa-dumbbell"></i></div>
                <?php endif; ?>
                <div class="content">
                    <h3><?= $e($exercise['name']) ?></h3>
                    <p><strong>Program:</strong> <?= $e($exercise['coaching_title'] ?? '') ?></p>
                    <p><?= nl2br($e($exercise['description'])) ?></p>
                    <p><strong>Sets/Reps:</strong> <?= (int)$exercise['sets'] ?>/<?= (int)$exercise['reps'] ?></p>
                    <div class="actions">
                        <a class="btn btn-light" href="index.php?controller=exercise&action=edit&id=<?= (int)$exercise['id'] ?><?= $currentCoachingId > 0 ? '&coaching_id=' . $currentCoachingId : '' ?>"><i class="fas fa-pen"></i> Edit</a>
                        <a class="btn btn-light" href="index.php?controller=exercise&action=delete&id=<?= (int)$exercise['id'] ?><?= $currentCoachingId > 0 ? '&coaching_id=' . $currentCoachingId : '' ?>" data-confirm><i class="fas fa-trash"></i> Delete</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
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
