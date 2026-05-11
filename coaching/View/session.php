<?php
// views/coaching/session.php - Session interactive de coaching avec timer
if (session_status() === PHP_SESSION_NONE) session_start();

// Helper function to convert YouTube URLs to embed format
function getEmbedUrl(string $url): string {
    if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }
    return $url; // Return original if not YouTube
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session de Coaching - Nutrivert</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --coral: #FF7E67;
            --mint: #7DCFB6;
            --sage: #4A6B4A;
            --light-bg: #f4f8f4;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: var(--light-bg);
            color: var(--sage);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-top {
            background: white;
            padding: 15px 0;
            border-bottom: 2px solid var(--mint);
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .navbar-top .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--coral);
        }

        .user-info {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .btn-logout {
            background: var(--coral);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: background 0.3s;
        }

        .btn-logout:hover {
            background: #e56a55;
            color: white;
        }

        .progress-bar-custom {
            background: linear-gradient(90deg, var(--mint), var(--sage));
            height: 6px;
            margin-bottom: 30px;
            border-radius: 3px;
        }

        .container-coaching {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .exercise-card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            text-align: center;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .exercise-number {
            display: inline-block;
            background: linear-gradient(135deg, var(--mint), var(--sage));
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .exercise-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--sage);
            margin-bottom: 15px;
        }

        .exercise-description {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .exercise-details {
            background: linear-gradient(135deg, rgba(125, 207, 182, 0.1), rgba(74, 107, 74, 0.1));
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 20px;
        }

        .detail-item {
            text-align: center;
        }

        .detail-label {
            font-size: 0.85rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--coral);
        }

        .timer-container {
            margin: 40px 0;
        }

        .timer-circle {
            width: 200px;
            height: 200px;
            margin: 0 auto 30px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--mint), var(--sage));
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 10px 40px rgba(74, 107, 74, 0.2);
            animation: pulse 1s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 10px 40px rgba(74, 107, 74, 0.2); }
            50% { box-shadow: 0 10px 50px rgba(74, 107, 74, 0.4); }
        }

        .timer-display {
            color: white;
            text-align: center;
            z-index: 2;
        }

        .timer-time {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 5px;
        }

        .timer-label {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
        }

        .exercise-image {
            max-width: 100%;
            height: auto;
            max-height: 300px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .video-container {
            position: relative;
            margin-bottom: 20px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .video-container iframe {
            width: 100%;
            height: 400px;
            border: none;
        }

        .progress-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 0.95rem;
            color: #666;
        }

        .progress-text-left { font-weight: 600; }
        .progress-text-right { font-weight: 600; }

        .btn-actions {
            margin-top: 40px;
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn-back {
            background: #ccc;
            color: var(--sage);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 1rem;
        }

        .btn-back:hover {
            background: #bbb;
        }

        .status-message {
            font-size: 0.9rem;
            color: var(--mint);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .timer-active {
            color: var(--coral);
        }

        .timer-warning {
            color: #ff9800;
        }

        .timer-ready {
            color: var(--mint);
        }

        @media (max-width: 768px) {
            .exercise-card { padding: 25px 20px; }
            .exercise-title { font-size: 1.5rem; }
            .exercise-details { grid-template-columns: 1fr 1fr; }
            .timer-circle { width: 150px; height: 150px; }
            .timer-time { font-size: 2.5rem; }
        }
    </style>
</head>
<body>

<div class="navbar-top">
    <div class="container">
        <div class="logo">🏋️ Coaching Session</div>
        <div class="user-info">
            <span><?= htmlspecialchars($user['nom'] ?? '') ?></span>
            <a href="index.php?controller=user&action=logout" class="btn btn-logout">Déconnexion</a>
        </div>
    </div>
</div>

<div class="container-coaching">

    <!-- Progress Bar -->
    <div class="progress-info">
        <span class="progress-text-left">Exercice <?= $currentExerciseIndex + 1 ?> sur <?= $totalExercises ?></span>
        <span class="progress-text-right">
            <?= number_format((($currentExerciseIndex + 1) / $totalExercises) * 100, 0) ?>%
        </span>
    </div>
    <div class="progress-bar-custom" style="width: <?= (($currentExerciseIndex + 1) / $totalExercises) * 100 ?>%;"></div>

    <!-- Exercise Card -->
    <div class="exercise-card">

        <div class="status-message">
            <i class="fas fa-dumbbell"></i> Exercice en cours
        </div>

        <div class="exercise-number"><?= $currentExerciseIndex + 1 ?></div>

        <h1 class="exercise-title"><?= htmlspecialchars($currentExercise['name'] ?? '') ?></h1>

        <?php if (!empty($currentExercise['description'])): ?>
            <p class="exercise-description">
                <?= htmlspecialchars($currentExercise['description'] ?? '') ?>
            </p>
        <?php endif; ?>

        <!-- Image -->
        <?php if (!empty($currentExercise['image'])): ?>
            <img src="<?= htmlspecialchars($currentExercise['image']) ?>"
                 alt="<?= htmlspecialchars($currentExercise['name'] ?? '') ?>"
                 class="exercise-image">
        <?php endif; ?>

        <!-- Video -->
        <?php if (!empty($currentExercise['video_url'])): ?>
            <div class="video-container">
                <iframe src="<?= htmlspecialchars(getEmbedUrl($currentExercise['video_url'])) ?>"
                        allowfullscreen
                        allow="autoplay; encrypted-media">
                </iframe>
            </div>
        <?php endif; ?>

        <!-- Exercise Details -->
        <div class="exercise-details">
            <div class="detail-item">
                <div class="detail-label">Séries</div>
                <div class="detail-value"><?= $currentExercise['sets'] ?? 0 ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Répétitions</div>
                <div class="detail-value"><?= $currentExercise['reps'] ?? 0 ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Repos</div>
                <div class="detail-value"><?= htmlspecialchars($currentExercise['rest_time'] ?? '') ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Durée</div>
                <div class="detail-value" id="duration-display">
                    <?= $currentExercise['duree_sec'] ?? 30 ?>s
                </div>
            </div>
        </div>

        <!-- Timer Container -->
        <div class="timer-container">
            <div class="timer-circle">
                <div class="timer-display">
                    <div class="timer-time" id="timer-value">
                        <?= str_pad($currentExercise['duree_sec'] ?? 30, 2, '0', STR_PAD_LEFT) ?>
                    </div>
                    <div class="timer-label">secondes</div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="btn-actions">
            <button class="btn-back" onclick="pauseTimer()">
                <i class="fas fa-pause"></i> Pause
            </button>
            <?php if ($currentExerciseIndex + 1 < $totalExercises): ?>
                <button class="btn-back" style="background: var(--mint); color: white;" onclick="skipToNext()">
                    <i class="fas fa-forward"></i> Passer au suivant
                </button>
            <?php else: ?>
                <button class="btn-back" style="background: var(--coral); color: white;" onclick="completeCoaching()">
                    <i class="fas fa-check"></i> Terminer le coaching
                </button>
            <?php endif; ?>
        </div>

    </div>

</div>

<script>
    let timerInterval = null;
    let timeRemaining = <?= $currentExercise['duree_sec'] ?? 30 ?>;
    let isPaused = false;
    let coachingId = <?= $_SESSION['coaching_session']['coaching_id'] ?? 0 ?>;
    let currentIndex = <?= $currentExerciseIndex ?>;
    let totalExercises = <?= $totalExercises ?>;

    // Démarrer le timer automatiquement
    function startTimer() {
        if (timerInterval !== null) return;

        timerInterval = setInterval(() => {
            if (!isPaused) {
                timeRemaining--;

                // Mise à jour du display
                document.getElementById('timer-value').textContent =
                    String(timeRemaining).padStart(2, '0');

                // Classe pour le style du timer
                const timerElement = document.querySelector('.timer-circle');
                if (timeRemaining <= 5 && timeRemaining > 0) {
                    timerElement.style.background = 'linear-gradient(135deg, #ff9800, #ff6b6b)';
                    document.querySelector('.timer-time').classList.add('timer-warning');
                } else if (timeRemaining <= 0) {
                    document.querySelector('.timer-time').classList.remove('timer-warning');
                }

                // Quand le timer arrive à 0
                if (timeRemaining <= 0) {
                    clearInterval(timerInterval);
                    timerInterval = null;
                    completeExercise();
                }
            }
        }, 1000);
    }

    // Mettre en pause
    function pauseTimer() {
        isPaused = !isPaused;
        const btn = event.target.closest('button');
        if (isPaused) {
            btn.innerHTML = '<i class="fas fa-play"></i> Reprendre';
        } else {
            btn.innerHTML = '<i class="fas fa-pause"></i> Pause';
        }
    }

    // Passer au prochain exercice
    function skipToNext() {
        clearInterval(timerInterval);
        if (currentIndex + 1 < totalExercises) {
            window.location.href = 'index.php?controller=user_dashboard&action=next';
        } else {
            completeCoaching();
        }
    }

    // Compléter l'exercice
    function completeExercise() {
        // Son de notification (optionnel)
        playSound();

        // Passage automatique au suivant sans popup
        skipToNext();
    }

    // Terminer le coaching
    function completeCoaching() {
        clearInterval(timerInterval);
        // Add quit=1 to indicate manual completion (for partial progress email)
        window.location.href = 'index.php?controller=user_dashboard&action=complete&quit=1';
    }

    // Son de notification
    function playSound() {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gain = audioContext.createGain();

        oscillator.connect(gain);
        gain.connect(audioContext.destination);

        oscillator.frequency.value = 800;
        oscillator.type = 'sine';

        gain.gain.setValueAtTime(0.3, audioContext.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.5);
    }

    // Démarrer le timer au chargement
    window.addEventListener('DOMContentLoaded', startTimer);
</script>

</body>
</html>
