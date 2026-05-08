# 🏋️ Système de Coaching Interactif - Nutrivert

## ✅ Implémentation Complète

Vous avez maintenant un **système de coaching interactif complet** en PHP MVC avec les fonctionnalités suivantes:

### 🎯 Fonctionnalités Principales

1. **✅ Authentification Utilisateur**
   - Connexion/Inscription avec rôles (USER, COACH, ADMIN)
   - Session sécurisée avec password hashing

2. **✅ Dashboard Utilisateur**
   - Page d'accueil personnalisée après connexion
   - Affichage des données de profil (poids, taille, IMC, calories)
   - Bouton principal "Voir tous les Coachings"

3. **✅ Liste des Coachings**
   - Affichage en grille avec cartes détaillées
   - Informations: titre, description, difficulté, durée, nombre d'exercices
   - Bouton "Commencer" pour lancer une session

4. **✅ Session Interactive de Coaching**
   - Affichage de l'exercice courant avec ordre de progression
   - **TIMER AUTOMATIQUE** avec:
     - Démarrage automatique au chargement
     - Pause/Reprise manuelle
     - Changement de couleur à 5 secondes (alerte)
     - Son de notification à 0
     - Transition automatique vers l'exercice suivant
   - Affichage: vidéo, image, description, sets/reps, repos
   - Barre de progression

5. **✅ Exercices Ordonnés**
   - Champ `ordre` en base de données
   - Récupération: `ORDER BY ordre ASC, id ASC`
   - Progression garantie dans le bon ordre

6. **✅ Flow Complet**
   - Exercice 1 (30s) → Timer → Exercice 2 (30s) → Timer → ... → Dernier
   - À la fin: Message "Coaching Terminé 🎉"
   - Page de résultat avec:
     - Animation confettis
     - Durée totale
     - Boutons pour continuer

### 📊 Architecture MVC

```
📁 controllers/
  ├── UserController.php              (Login/Register/Logout)
  ├── UserDashboardController.php     (NEW - Dashboard & Sessions)
  ├── CoachingController.php          (CRUD Coachings)
  └── ExerciseController.php          (CRUD Exercises)

📁 models/
  ├── User.php                        (Entité utilisateur)
  ├── Coaching.php                    (Entité coaching)
  └── Exercise.php                    (UPDATED - ajout ordre, duree_sec)

📁 views/
  ├── user/
  │   ├── dashboard.php               (Accueil utilisateur)
  │   ├── coaching_list.php           (Liste des coachings)
  │   ├── coaching_session.php        (NEW - Session avec timer)
  │   └── coaching_complete.php       (NEW - Résultat final)
  ├── front/                          (Vues publiques)
  └── back/                           (Vues admin/coach)

📁 config/
  └── database.php                    (UPDATED - auto-migration)
```

---

## 🚀 Installation & Démarrage

### 1. Base de données
```sql
-- Exécuter les migrations (automatiques via config/database.php)
-- OU importer manuellement:
mysql -u root nutrivert < migration_20260427.sql

-- Pour les données de test:
mysql -u root nutrivert < TEST_DATA.sql
```

### 2. Démarrage de l'application
```bash
cd c:\xampp\htdocs\malek avant final\malek 001
php -S localhost:8000
# Ouvrir http://localhost:8000
```

### 3. Première utilisation
1. Inscription: `http://localhost:8000/?view=register`
   - Rôle: USER
   - Remplir poids/taille/age
2. Connexion: `http://localhost:8000/?view=login`
3. Dashboard: Voir les données personnelles
4. Cliquer "Voir tous les Coachings"
5. Lancer un coaching et profiter!

---

## 📋 Fichiers Créés/Modifiés

### ✨ NOUVEAUX FICHIERS:
- `views/user/coaching_session.php` - Session avec timer interactif
- `views/user/coaching_complete.php` - Page de résultat final
- `COACHING_SYSTEM.md` - Documentation technique complète
- `TEST_DATA.sql` - Données d'exemple pour les tests

### 🔄 FICHIERS MODIFIÉS:
- `migration_20260427.sql` - Ajout champs `ordre` et `duree_sec`
- `models/Exercise.php` - Getters/setters pour nouveaux champs
- `config/database.php` - Auto-migration pour `duree_sec`
- `index.php` - Support du `UserDashboardController`

---

## 🎮 Guide Utilisateur Quick-Start

### Pour l'Admin/Coach:
1. Dashboard → Créer Coaching
2. Remplir: titre, description, difficulté, durée
3. Créer Exercices avec:
   - Nom, description, sets/reps
   - **IMPORTANT**: Ordre (1, 2, 3, etc.)
   - **IMPORTANT**: Durée en secondes (30, 45, 60...)
   - Optionnel: vidéo YouTube, image

### Pour l'Utilisateur:
1. Se connecter (USER)
2. Cliquer "Voir tous les Coachings"
3. Choisir un coaching → "Commencer"
4. Faire les exercices avec le timer
5. À la fin: Célébration! 🎉

---

## ⚙️ Caractéristiques Techniques

### Timer JavaScript
```javascript
// Démarre automatiquement au chargement
startTimer() 
  ├── Compte à rebours chaque seconde
  ├── Mise à jour visuelle du display
  ├── À 5 secondes: changement de couleur
  ├── À 0: son de notification
  └── Transition automatique

// Gestion du timer
pauseTimer()        // Pause/Reprendre
skipToNext()        // Passer manuellement
completeCoaching()  // Terminer
```

### Récupération des Exercices (Ordre Garanti)
```php
$stmt = $pdo->prepare(
    'SELECT * FROM exercises 
     WHERE coaching_id = :coaching_id 
     ORDER BY ordre ASC, id ASC'
);
```

### Gestion de Session
```php
$_SESSION['coaching_session'] = [
    'coaching_id' => 1,
    'current_exercise_index' => 0,
    'started_at' => time(),
    'total_exercises' => 5,
];
```

### Styles CSS
- ✅ Design moderne (Mint + Sage + Coral)
- ✅ Animations fluides (slideIn, pulse, bounce)
- ✅ Responsive mobile/desktop
- ✅ Accessibilité optimisée

---

## 🔍 Testing

### Scénario de Test Complet:
1. **Créer un coaching test**
   - Titre: "Test 2min"
   - 3 exercices de 30 secondes chacun
   - Ordre: 1, 2, 3

2. **Se connecter en tant que USER**
   - Email: alice@nutrivert.fr
   - Vérifier le dashboard

3. **Lancer le coaching**
   - Vérifier l'ordre des exercices
   - Vérifier que le timer fonctionne
   - Tester pause/reprise
   - Écouter le son à 0
   - Vérifier la transition auto

4. **Résultat final**
   - Vérifier la page de célébration
   - Vérifier l'animation confettis
   - Vérifier le calcul de durée

---

## 🐛 Dépannage Rapide

| Problème | Solution |
|----------|----------|
| Timer ne démarre pas | Vérifier `duree_sec` en BDD + console JavaScript |
| Mauvais ordre d'exercices | Vérifier colonne `ordre` et requête SQL |
| Session perdue | Vérifier `session_start()` au début des vues |
| Pas de migration auto | Vérifier `config/database.php` |
| Pas d'audio | Vérifier les permissions du navigateur |

---

## 📚 Documentation Complète

Pour une documentation technique approfondie, consultez: **COACHING_SYSTEM.md**

Contient:
- Flow utilisateur détaillé
- Endpoints et routes
- Schéma base de données
- Exemples d'utilisation
- Extensions futures

---

## 🎉 Résumé des Livérables

✅ **1. Dashboard utilisateur** avec bouton Coaching
✅ **2. Liste des coachings** avec fiches détaillées
✅ **3. Session interactive** avec timer par exercice
✅ **4. Progression automatique** vers l'exercice suivant
✅ **5. Ordre garanti** via champ `ordre` en base
✅ **6. Message de célébration** à la fin
✅ **7. Architecture MVC propre** et maintenable
✅ **8. Auto-migration** des colonnes nécessaires
✅ **9. Design moderne** et responsive
✅ **10. Documentation complète** et exemples

---

## 🚀 Prochaines Étapes Recommandées

1. **Tester avec les données de test** (`TEST_DATA.sql`)
2. **Créer vos propres coachings** via l'admin
3. **Personnaliser le design** (couleurs, fonts)
4. **Ajouter un système de badges** ou achievements
5. **Implémenter historique** des sessions
6. **Ajouter statistiques utilisateur** (calories brûlées, etc.)

---

## 📞 Support

Pour les questions ou améliorations, consultez:
- Code source bien commenté
- Documentation COACHING_SYSTEM.md
- Fichier TEST_DATA.sql pour des exemples

**Bon entraînement! 💪**
