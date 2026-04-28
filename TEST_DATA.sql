-- ============================================================================
-- Données de test pour le système de coaching interactif
-- À insérer après l'exécution de la migration_20260427.sql
-- ============================================================================

-- ✅ UTILISATEURS DE TEST
-- Passwords:
-- - USER (jean): password123 => $2y$10$e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e
-- - ADMIN (admin): admin123 => $2y$10$abcdefghijklmnopqrstuvwxyz123456789012345678901234567
-- IMPORTANT: Remplacer les hashes par les vrais hashes bcrypt en production!

-- Insérer un utilisateur ADMIN de test
INSERT INTO users (nom, email, password, role, poids, taille, age, sexe, objectif, imc, calories, specialite, bio)
VALUES ('Admin Nutrivert', 'admin@nutrivert.fr', '$2y$10$abcdefghijklmnopqrstuvwxyz123456789012345678901234567890', 'admin', 75, 180, 35, 'homme', 'maintenance', 23.1, 2200, NULL, 'Administrateur du système');

-- Insérer un utilisateur USER de test
INSERT INTO users (nom, email, password, role, poids, taille, age, sexe, objectif, imc, calories, specialite, bio)
VALUES ('Jean Dupont', 'jean@nutrivert.fr', '$2y$10$e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e', 'user', 75, 180, 30, 'homme', 'perte', 23.1, 2200, NULL, NULL);

-- Insérer un utilisateur USER supplémentaire (femme)
INSERT INTO users (nom, email, password, role, poids, taille, age, sexe, objectif, imc, calories, specialite, bio)
VALUES ('Marie Martin', 'marie@nutrivert.fr', '$2y$10$e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e3e', 'user', 65, 170, 28, 'femme', 'prise', 22.4, 1900, NULL, NULL);

-- Insérer des coachings d'exemple
INSERT INTO coaching_programs (title, description, duration_weeks, difficulty_level) VALUES
('Full Body 5 Min', 'Un entraînement complet du corps en 5 minutes. Parfait pour débuter!', 1, 'easy'),
('HIIT Intense 10 Min', 'Entraînement à haute intensité pour brûler des calories rapidement.', 2, 'hard'),
('Yoga Relaxation', 'Session de yoga douce pour la flexibilité et la relaxation.', 1, 'easy');

-- Insérer les exercices du coaching "Full Body 5 Min" (ID 1)
INSERT INTO exercises (coaching_id, name, description, sets, reps, rest_time, video_url, image, ordre, duree_sec) VALUES
(1, '🚀 Pompes', 'Mains écartées à la largeur des épaules. Descendre lentement, remonter en force.', 3, 10, '30 secondes', 'https://www.youtube.com/embed/IODxDxX7oi4', 'uploads/exercise-pushups.jpg', 1, 30),
(1, '🦵 Squats', 'Pieds écartés à la largeur du bassin. Descendre en pliant les genoux, remonter.', 3, 15, '30 secondes', 'https://www.youtube.com/embed/x0FauvHNqwo', 'uploads/exercise-squats.jpg', 2, 30),
(1, '⚡ Fentes', 'Alterner jambe gauche et jambe droite. Descendre jusqu\'à 90 degrés.', 3, 10, '45 secondes', 'https://www.youtube.com/embed/aV-zrPpEtYM', 'uploads/exercise-lunges.jpg', 3, 30),
(1, '🎯 Gainage', 'Position de pompes, rester en suspension sur les avant-bras. Respirer calmement.', 3, 1, '30 secondes', 'https://www.youtube.com/embed/pSHjTRCQxIw', 'uploads/exercise-plank.jpg', 4, 30),
(1, '💥 Burpees', 'Sauter, mains au sol, pompe, sauter, remonter. Mouvement complet et dynamique.', 3, 5, '45 secondes', 'https://www.youtube.com/embed/JZRF7uo9NgI', 'uploads/exercise-burpees.jpg', 5, 30);

-- Insérer les exercices du coaching "HIIT Intense 10 Min" (ID 2)
INSERT INTO exercises (coaching_id, name, description, sets, reps, rest_time, video_url, image, ordre, duree_sec) VALUES
(2, '🔥 Mountain Climbers', 'Position de pompes. Ramener les genoux alternativement vers la poitrine rapidement.', 4, 20, '30 secondes', 'https://www.youtube.com/embed/nmwgiRUNu7c', 'uploads/exercise-climbers.jpg', 1, 45),
(2, '⚡ Jump Squats', 'Squat classique + saut explosif. Sauter aussi haut que possible.', 4, 12, '45 secondes', 'https://www.youtube.com/embed/ZWXcMIGqwKs', 'uploads/exercise-jumpsquats.jpg', 2, 45),
(2, '🎪 Corde à sauter', 'Sauter avec une corde (réelle ou imaginaire). Maintenir un rythme rapide et régulier.', 4, 100, '60 secondes', 'https://www.youtube.com/embed/CjZEeB9mZxI', 'uploads/exercise-rope.jpg', 3, 45),
(2, '🚀 Pompes explosives', 'Pompes classiques mais sauter au sommet de chaque répétition.', 4, 8, '45 secondes', 'https://www.youtube.com/embed/7E0CKH9dkWs', 'uploads/exercise-explosive.jpg', 4, 45);

-- Insérer les exercices du coaching "Yoga Relaxation" (ID 3)
INSERT INTO exercises (coaching_id, name, description, sets, reps, rest_time, video_url, image, ordre, duree_sec) VALUES
(3, '🧘 Posture de l\'enfant', 'Agenouillé, penchez-vous en avant en étirant les bras. Respirez profondément.', 1, 1, '30 secondes', 'https://www.youtube.com/embed/DYoKsVhPAEE', 'uploads/yoga-child.jpg', 1, 45),
(3, '🦋 Posture du papillon', 'Assis, pieds rapprochés. Penchez-vous légèrement en avant. Stretching doux des jambes.', 1, 1, '30 secondes', 'https://www.youtube.com/embed/kNX8TALFjNw', 'uploads/yoga-butterfly.jpg', 2, 45),
(3, '🌙 Chat-Vache', 'Alternez entre la position du chat (dos rond) et de la vache (poitrine en avant).', 2, 8, '15 secondes', 'https://www.youtube.com/embed/VHgXhWMVu-M', 'uploads/yoga-cat-cow.jpg', 3, 45),
(3, '🧘‍♂️ Méditation assise', 'Assis confortablement. Fermez les yeux, inspirez par le nez, expirez par la bouche.', 1, 1, '60 secondes', 'https://www.youtube.com/embed/ssss7V1811k', 'uploads/yoga-meditation.jpg', 4, 60);

-- ============================================================================
-- Notes:
-- - L'ordre des exercices est défini par la colonne "ordre"
-- - La durée en secondes est dans "duree_sec"
-- - Les URLs YouTube sont des exemples (à adapter ou modifier)
-- - Les images peuvent être optionnelles (laisser vide si pas d'upload)
-- - Le champ "rest_time" est pour information utilisateur (pas utilisé par le timer)
-- ============================================================================
