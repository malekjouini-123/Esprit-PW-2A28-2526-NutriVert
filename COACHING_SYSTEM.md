# 🏋️ Système de Coaching Interactif - Documentation Complète

## Vue d'ensemble

Ce système implémente un flux de coaching interactif avec les fonctionnalités suivantes:
- ✅ Authentification des utilisateurs (USER, COACH, ADMIN)
- ✅ Dashboard personnalisé pour les utilisateurs
- ✅ Liste des coachings disponibles avec fiches détaillées
- ✅ Lancement de sessions de coaching interactives
- ✅ Timer automatique par exercice avec progression
- ✅ Transition automatique vers l'exercice suivant
- ✅ Message de célébration à la fin du coaching

---

## Architecture

### Controllers
- **UserController**: Gestion de l'authentification (login/register/logout)
- **UserDashboardController**: Dashboard et sessions de coaching pour les USER
- **CoachingController**: CRUD des coachings (admin/coach)
- **ExerciseController**: CRUD des exercices (admin/coach)

### Models
- **User**: Entité utilisateur avec validation
- **Coaching**: Entité programme de coaching
- **Exercise**: Entité exercice avec nouveaux champs `ordre` et `duree_sec`

### Views
- **views/user/dashboard.php**: Page d'accueil avec bouton Coaching
- **views/user/coaching_list.php**: Liste des coachings disponibles
- **views/user/coaching_session.php**: Session interactive avec timer
- **views/user/coaching_complete.php**: Page de completion avec animation confettis

---

## Base de données

### Table: exercises
Les champs importants pour le système de coaching:

```sql
ALTER TABLE exercises ADD COLUMN ordre INT NOT NULL DEFAULT 1;        -- Ordre d'exécution (croissant)
ALTER TABLE exercises ADD COLUMN duree_sec INT NOT NULL DEFAULT 30;  -- Durée de l'exercice en secondes
```

**Auto-migration**: Les colonnes sont automatiquement créées via `config/database.php` s'il en est besoin.

---

## Flow Utilisateur

### 1️⃣ Connexion
```
Utilisateur -> Formulaire LOGIN -> UserController::login()
-> Stockage de $_SESSION['user']
-> Redirection vers dashboard utilisateur
```

### 2️⃣ Dashboard Utilisateur
```
GET index.php?controller=user_dashboard&action=index
-> UserDashboardController::dashboard()
-> Affichage: profil + bouton "Voir tous les Coachings"
```

### 3️⃣ Liste des Coachings
```
GET index.php?controller=user_dashboard&action=coaching_list
-> UserDashboardController::coachingList()
-> Récupère: SELECT ... FROM coaching_programs
-> Affiche: Grille de cartes avec bouton "Commencer"
```

### 4️⃣ Lancement du Coaching
```
GET index.php?controller=user_dashboard&action=start&id={coachingId}
-> UserDashboardController::startCoaching()
-> Récupère: SELECT * FROM exercises WHERE coaching_id = ? ORDER BY ordre ASC
-> Stocke session: $_SESSION['coaching_session']
-> Affiche: coaching_session.php (exercice 1 avec timer)
```

### 5️⃣ Exercice avec Timer
**Fonctionnement du timer**:
- ✅ Démarrage automatique au chargement de la page
- ✅ Compte à rebours de `duree_sec` secondes
- ✅ Mise en pause/Reprise possible
- ✅ Passer au suivant (manuel ou automatique)
- ✅ À 5 secondes: changement de couleur (orange)
- ✅ À 0: son de notification et transition automatique

```javascript
// Timer JavaScript dans coaching_session.php
- startTimer(): Lance le countdown
- pauseTimer(): Pause/reprend le timer
- completeExercise(): Passe au suivant automatiquement
- playSound(): Son de notification (fréquence 800Hz)
```

### 6️⃣ Transition d'Exercice
```
GET index.php?controller=user_dashboard&action=next
-> UserDashboardController::nextExercise()
-> Incrémente: $_SESSION['coaching_session']['current_exercise_index']
-> Si dernier exercice: redirige vers complete
-> Sinon: réaffiche coaching_session.php avec exercice suivant
```

### 7️⃣ Fin du Coaching
```
GET index.php?controller=user_dashboard&action=complete
-> UserDashboardController::completeCoaching()
-> Calcule: durée totale en minutes:secondes
-> Affiche: Page de célébration avec confettis
```

---

## Fichiers Créés/Modifiés

### Nouveaux fichiers:
```
views/user/coaching_session.php    (session interactive avec timer)
views/user/coaching_complete.php   (page de completion)
```

### Fichiers modifiés:
```
models/Exercise.php                (ajout: ordre, duree_sec)
config/database.php                (auto-migration pour duree_sec)
             (schéma avec nouveaux champs)
index.php                          (ajout: UserDashboardController)
```

---

## Guide d'Utilisation

### Pour l'Admin/Coach: Créer un Coaching

1. Aller au Back-office: `index.php?controller=dashboard`
2. Créer un coaching: "Nouveau programme"
3. Créer des exercices avec:
   - **Nom**: "Pompes" / "Squats" / etc.
   - **Description**: Instructions pour l'utilisateur
   - **Sets/Reps**: Nombre de séries et répétitions
   - **Repos**: "30 secondes" / "1 minute" / etc.
   - **Vidéo**: URL YouTube (optionnel)
   - **Image**: Chemin vers upload/ (optionnel)
   - **Ordre**: 1, 2, 3... (IMPORTANT pour le flow)
   - **Durée (sec)**: 30, 45, 60... secondes par exercice

**Exemple de coaching "Full Body 5min"**:
```
1. Pompes         - 30 secondes - Ordre: 1
2. Squats         - 30 secondes - Ordre: 2
3. Fentes         - 30 secondes - Ordre: 3
4. Gainage        - 30 secondes - Ordre: 4
5. Burpees        - 30 secondes - Ordre: 5
= Total: ~5 minutes avec transitions
```

### Pour l'Utilisateur: Démarrer un Coaching

1. Se connecter avec rôle "USER"
2. Dashboard utilisateur → Bouton "Voir tous les Coachings"
3. Cliquer "Commencer" sur un coaching
4. Exécuter les exercices avec le timer
5. À la fin: Message "Coaching terminé 🎉"
6. Retour à la liste ou au dashboard

---

## Caractéristiques Techniques

### Session Management
```php
$_SESSION['coaching_session'] = [
    'coaching_id' => 1,                    // ID du coaching
    'current_exercise_index' => 0,        // Indice de l'exercice actuel (0-based)
    'started_at' => time(),               // Timestamp de début
    'total_exercises' => 5,               // Nombre total d'exercices
];
```

### Récupération des Exercices (Ordre Garantie)
```sql
SELECT * FROM exercises 
WHERE coaching_id = ? 
ORDER BY ordre ASC, id ASC
```

### Timer JavaScript
- ✅ **Précision**: Basé sur setInterval (toutes les 1000ms)
- ✅ **Pause/Reprise**: Flag `isPaused`
- ✅ **Audio**: Synthèse sonore Web Audio API (800Hz sine wave)
- ✅ **Responsive**: Adapté mobile/desktop

### Styles CSS
- ✅ **Gradient moderne**: Mint + Sage + Coral
- ✅ **Animations**: slideIn, pulse, bounce, fall (confettis)
- ✅ **Responsive**: Media queries pour mobile
- ✅ **Accessibilité**: Bonne contrast, icônes Font Awesome

---

## Dépannage

### Le timer ne démarre pas
- ✅ Vérifier que `duree_sec` est présent dans la base de données
- ✅ Vérifier la console JavaScript pour les erreurs
- ✅ Vérifier que le navigateur autorise l'audio Web API

### Les exercices sont dans le mauvais ordre
- ✅ Vérifier que `ordre` est correct pour chaque exercice
- ✅ Query: `SELECT * FROM exercises WHERE coaching_id = ? ORDER BY ordre ASC`

### La session se perd
- ✅ Vérifier que `session_start()` est appelé au début de chaque vue
- ✅ Vérifier que `$_SESSION['coaching_session']` est bien stocké

### Pas d'auto-migration des colonnes
- ✅ Vérifier que `config/database.php::runAutoMigration()` est exécutée
- ✅ Vérifier les logs PHP pour les erreurs PDO

---

## Extensions Futures

- 📊 Historique des sessions complétées
- 🏆 Statistiques utilisateur (calories brûlées, durée totale, etc.)
- 📱 Application mobile (React Native / Flutter)
- 🎖️ Système de badges et achievements
- 👥 Mode groupe (coachings collectifs)
- 💬 Chat avec le coach
- 📹 Enregistrement automatique des sessions
- 🔔 Notifications et rappels

---

## Licence & Support

Système développé pour **Nutrivert** - Application de coaching fitness

Pour les questions ou bugs, merci de consulter la documentation ou contacter le support.
