# 🚀 GUIDE DE TEST ET DÉPLOIEMENT FINAL

## 1️⃣ CONFIGURATION INITIALE

### Base de données
```bash
# Accéder à MySQL
mysql -u root

# Créer la base
CREATE DATABASE IF NOT EXISTS nutrivert;
USE nutrivert;

# Importer la migration
source migration_20260427.sql;
```

### Auto-migration
Les champs suivants sont auto-créés au premier accès:
- `exercises.ordre` (INT, DEFAULT 1)
- `exercises.duree_sec` (INT, DEFAULT 30)
- `coaching_programs.image` (VARCHAR)

---

## 2️⃣ DONNÉES DE TEST

### Créer un utilisateur ADMIN
```sql
INSERT INTO users (nom, email, password, role) VALUES
('Admin Nutrivert', 'admin@nutrivert.fr', '$2y$10$YourHashedPassword', 'admin');
```

### Créer un utilisateur USER
```sql
INSERT INTO users (nom, email, password, role, poids, taille, age, sexe, objectif, imc, calories) VALUES
('Jean Dupont', 'jean@example.com', '$2y$10$YourHashedPassword', 'user', 75, 180, 30, 'homme', 'perte', 23.1, 2200);
```

### Créer un coaching de test
```sql
INSERT INTO coaching_programs (title, description, image, duration_weeks, difficulty_level) VALUES
('Cardio 10min', 'Un entraînement cardio court et efficace', 'uploads/cardio.jpg', 1, 'medium');
```

### Créer des exercices de test
```sql
INSERT INTO exercises (coaching_id, name, description, sets, reps, rest_time, ordre, duree_sec) VALUES
(1, 'Échauffement', 'Trottiner sur place pendant 30 secondes', 1, 1, '0 sec', 1, 30),
(1, 'Burpees', 'Saut + pompe + saut', 3, 10, '30 sec', 2, 45),
(1, 'High Knees', 'Monter les genoux haut en courant', 3, 20, '30 sec', 3, 45),
(1, 'Récupération', 'Marcher lentement et respirer', 1, 1, '0 sec', 4, 30);
```

---

## 3️⃣ CHECKLIST DE TEST

### 🔐 Authentification
- [ ] Se connecter avec USER
  - Email: jean@example.com
  - Redirection → Dashboard USER
- [ ] Se connecter avec ADMIN  
  - Email: admin@nutrivert.fr
  - Redirection → Backoffice ADMIN
- [ ] Déconnexion fonctionne
- [ ] Password reset/recovery (si implémenté)

### 👤 Dashboard USER
- [ ] Header affiche le nom de l'utilisateur
- [ ] Profil card affiche: poids, taille, IMC, calories
- [ ] Bouton "Voir tous les Coachings" visible et cliquable
- [ ] Click → Liste des coachings

### 🧠 Liste des Coachings (USER)
- [ ] Affiche tous les coachings
- [ ] Affiche carte avec:
  - [x] Titre
  - [x] Description (truncate à 150 chars)
  - [x] Image (si présente)
  - [x] Difficulté (badge coloré)
  - [x] Durée
  - [x] Nombre d'exercices
  - [x] Bouton "Commencer"
- [ ] Click "Commencer" → Session coaching

### ⏱️ Session de Coaching
- [ ] Affiche exercice 1 au chargement
- [ ] Timer démarre automatiquement
- [ ] Affiche: nom, description, sets/reps, repos, durée
- [ ] Barre de progression: "Exercice 1 sur 4"
- [ ] Boutons:
  - [ ] Pause / Reprendre
  - [ ] Passer au suivant
- [ ] À 5 secondes: changement couleur timer (orange)
- [ ] À 0: son notification + passage automatique
- [ ] Exercice 2: titre change, timer reset
- [ ] Répète pour tous les exercices
- [ ] Dernier exercice: bouton "Terminer"

### 🎉 Fin du Coaching
- [ ] Message "Coaching terminé 🎉"
- [ ] Animation confettis
- [ ] Stats: durée totale, nombre exercices
- [ ] Boutons:
  - [ ] "Autre coaching"
  - [ ] "Retour au dashboard"

### 👨‍💼 Backoffice ADMIN
- [ ] Accès avec email admin
- [ ] Dashboard admin visible
- [ ] Sidebar avec menus
- [ ] Lien "Programmes"
- [ ] Lien "Exercices"

### ➕ Créer Coaching (ADMIN)
- [ ] Accès au formulaire
- [ ] Champs:
  - [ ] Titre
  - [ ] Description
  - [ ] Image (nouveau!)
  - [ ] Durée (semaines)
  - [ ] Difficulté
- [ ] Submit crée le coaching
- [ ] Redirection vers liste

### ✏️ Éditer Coaching (ADMIN)
- [ ] Accès au formulaire
- [ ] Pré-remplissage des champs
- [ ] Modification de l'image
- [ ] Submit met à jour
- [ ] Vérification en BDD

### ➕ Créer Exercice (ADMIN)
- [ ] Accès au formulaire
- [ ] Champs:
  - [ ] Programme associé
  - [ ] Nom
  - [ ] Description
  - [ ] Sets/Reps
  - [ ] Repos
  - [ ] **Ordre (nouveau!)**
  - [ ] **Durée en sec (nouveau!)**
  - [ ] Image
  - [ ] Vidéo
- [ ] Submit crée l'exercice

### ✏️ Éditer Exercice (ADMIN)
- [ ] Accès au formulaire
- [ ] Pré-remplissage y compris **ordre** et **duree_sec**
- [ ] Modification possible
- [ ] Submit met à jour

### 🔀 Ordre des Exercices (USER)
- [ ] Créer 3 exercices avec ordre: 3, 1, 2
- [ ] Afficher coaching
- [ ] Vérifier que l'ordre d'exécution est: 1, 2, 3
- [ ] Pas: 3, 1, 2

### ⏱️ Timer par Exercice (USER)
- [ ] Exercice 1: duree_sec = 10 → Timer affiche 10
- [ ] Exercice 2: duree_sec = 20 → Timer affiche 20
- [ ] Vérifier que durée change bien

---

## 4️⃣ COMMANDES UTILES

### Réinitialiser la base de données
```bash
mysql -u root nutrivert < migration_20260427.sql
```

### Vider tous les coachings et exercices
```sql
DELETE FROM exercises;
DELETE FROM coaching_programs;
ALTER TABLE exercises AUTO_INCREMENT = 1;
ALTER TABLE coaching_programs AUTO_INCREMENT = 1;
```

### Vérifier les colonnes exercices
```sql
SHOW COLUMNS FROM exercises;
```

### Vérifier les colonnes coaching_programs
```sql
SHOW COLUMNS FROM coaching_programs;
```

---

## 5️⃣ RÉSOLUTION DE PROBLÈMES

### Timer ne fonctionne pas
1. Vérifier que `duree_sec` existe en BDD
2. Vérifier la console JavaScript (F12)
3. Vérifier que `coaching_session.php` est bien chargée
4. Test: accéder à `?controller=user_dashboard&action=start&id=1`

### Mauvais ordre d'exercices
1. Vérifier colonne `ordre` en BDD
2. Vérifier requête: `ORDER BY ordre ASC, id ASC`
3. Éditer les exercices pour corriger l'ordre

### Image ne s'affiche pas
1. Vérifier le chemin existe
2. Vérifier le dossier `uploads/` a les bonnes permissions
3. Vérifier que le chemin est correct dans la BDD

### Session perdue
1. Vérifier que `session_start()` est appelé
2. Vérifier que `$_SESSION['coaching_session']` est bien stocké
3. Vérifier les cookies sont activés

### Redirection vers mauvaise page après login
1. Vérifier `UserController::login()` ligne ~155
2. Vérifier la logique: `if ($role === 'admin')` ...
3. Tester avec les rôles user et admin

---

## 6️⃣ DÉPLOIEMENT

### Fichiers à vérifier avant déploiement
- [ ] `config/database.php` - Credentials MySQL
- [ ] `index.php` - Routing complet
- [ ] `controllers/*` - Logique métier
- [ ] `views/*` - Templates
- [ ] `models/*` - Entités avec nouveaux champs
- [ ] `assets/` - CSS et JS

### Permissions
```bash
chmod -R 755 .
chmod -R 777 uploads/  # Dossier images
chmod 600 config/database.php  # Sécurité credentials
```

### Sécurité
- [ ] Vérifier HTTPS en production
- [ ] Vérifier les validations des inputs
- [ ] Vérifier les protections CSRF
- [ ] Vérifier les sessions sécurisées
- [ ] Vérifier les permissions des fichiers

---

## 7️⃣ POINTS CLÉS

✅ **Système fonctionne si:**
1. USER se connecte → Dashboard utilisateur ✓
2. ADMIN se connecte → Backoffice admin ✓
3. USER clique "Voir Coachings" → Liste affichée ✓
4. USER clique "Commencer" → Session avec timer ✓
5. Timer compte à rebours automatique ✓
6. À zéro → Exercice suivant automatique ✓
7. Tous les exercices en ordre correct ✓
8. À la fin → Message "Coaching terminé 🎉" ✓
9. ADMIN peut créer/éditer coachings et exercices ✓
10. Nouveaux champs (image, ordre, duree_sec) fonctionnent ✓

---

## 📞 CONTACT & SUPPORT

Si un test échoue:
1. Vérifier la BDD (`SHOW TABLES`, `SHOW COLUMNS`)
2. Vérifier les logs PHP (`error_log`)
3. Vérifier la console navigateur (F12)
4. Consulter les fichiers README_COACHING.md et COACHING_SYSTEM.md
