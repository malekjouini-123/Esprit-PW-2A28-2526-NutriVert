# 🚀 GUIDE DE DÉMARRAGE RAPIDE

## ⏱️ 5 MINUTES POUR TESTER

### 1️⃣ Préparation (1 min)
```bash
# Vérifier que MySQL est en cours d'exécution
# Vérifier que PHP est installé (7.4+)
# Accéder à: http://localhost/coaching/
```

### 2️⃣ Base de données (1 min)
```bash
# Importer la migration
mysql -u root nutrivert < migration_20260427.sql

# Importer les données de test (OPTIONNEL)
mysql -u root nutrivert < TEST_DATA.sql
```

### 3️⃣ Premier test (3 min)

#### Teste le flux utilisateur:
1. Accéder à: `http://localhost/coaching/`
2. Cliquer "Se connecter"
3. Utiliser les identifiants de test:
   - **Email**: jean@nutrivert.fr
   - **Password**: password123
4. Voir le dashboard utilisateur
5. Cliquer "Voir tous les Coachings"
6. Cliquer "Commencer" sur un coaching
7. Voir le timer démarrer automatiquement
8. Attendre la fin → Message "Coaching terminé 🎉"

#### Test Backoffice ADMIN:
1. Se déconnecter
2. Se connecter avec:
   - **Email**: admin@nutrivert.fr
   - **Password**: admin123
3. Voir le dashboard admin
4. Cliquer "Programmes" ou "Exercices"
5. Essayer de créer/éditer

---

## 📋 CHECKLIST DE CONFIGURATION

- [ ] MySQL en cours d'exécution
- [ ] Base de données créée: `nutrivert`
- [ ] Tables créées (migration exécutée)
- [ ] Dossier `uploads/` existe et est accessible
- [ ] Fichier `config/database.php` configuré
- [ ] `index.php` accessible en HTTP

---

## 🔑 IDENTIFIANTS DE TEST

### Utilisateur (USER)
```
Email: jean@nutrivert.fr
Password: password123
Role: user
```

### Administrateur (ADMIN)
```
Email: admin@nutrivert.fr
Password: admin123
Role: admin
```

### Coach
```
Email: coach@nutrivert.fr
Password: coach123
Role: coach
```

---

## 🎯 POINTS À VÉRIFIER

### ✅ Authentification
- [ ] Login USER fonctionne
- [ ] Login ADMIN fonctionne
- [ ] Redirection correcte (USER → dashboard, ADMIN → backoffice)

### ✅ Utilisateur
- [ ] Dashboard affiche le profil
- [ ] Bouton "Voir Coachings" visible

### ✅ Coachings
- [ ] Liste des coachings s'affiche
- [ ] Images visibles (si présentes)
- [ ] Bouton "Commencer" cliquable

### ✅ Timer
- [ ] Timer démarre automatiquement
- [ ] Compte à rebours correct
- [ ] Pause/Reprendre marche
- [ ] Passage auto à l'exercice suivant

### ✅ Fin
- [ ] Message "Coaching terminé 🎉" s'affiche
- [ ] Confettis animés

### ✅ Admin
- [ ] Créer coaching possible
- [ ] Ajouter image coaching possible
- [ ] Créer exercice avec ordre et duree_sec possible
- [ ] Éditer/Supprimer possible

---

## 🐛 DÉBOGAGE

### Problème: Page blanche
```php
// Vérifier:
// 1. error_log du serveur Apache
// 2. config/database.php - credentials corrects?
// 3. MySQL accessible?

// Log PHP:
error_log("Debug: Message");
```

### Problème: Login ne fonctionne pas
```sql
-- Vérifier les utilisateurs:
SELECT * FROM users;

-- Vérifier les permissions:
SHOW GRANTS FOR 'nutrivert'@'localhost';
```

### Problème: Images ne s'affichent pas
```bash
# Vérifier le dossier:
ls -la uploads/

# Donner les permissions:
chmod 777 uploads/
```

### Problème: Timer ne démarre pas
```javascript
// Vérifier la console navigateur (F12):
// - Erreurs JS?
// - duree_sec en BDD?
// - coaching_session.php chargée?
```

---

## 📁 FICHIERS IMPORTANTS

| Fichier | Rôle | Action |
|---------|------|--------|
| `index.php` | Routeur principal | À ne pas modifier |
| `config/database.php` | Connexion BDD | Vérifier credentials |
| `migration_20260427.sql` | Schéma BDD | Exécuter une fois |
| `TEST_DATA.sql` | Données test | Optionnel |
| `controllers/UserController.php` | Auth | Ne pas modifier |
| `views/user/coaching_session.php` | Timer | Ne pas modifier |
| `assets/css/style.css` | Design | Personnalisable |

---

## 🚀 PROCHAINS PAS

### Après Vérification
1. ✅ Vérifier tous les points de la checklist
2. ✅ Tester le flow complet utilisateur
3. ✅ Tester le backoffice admin
4. ✅ Créer du contenu réel (coachings, exercices)
5. ✅ Inviter les premiers utilisateurs
6. ✅ Collecter les retours
7. ✅ Faire les ajustements

### Déploiement
1. Vérifier la sécurité (HTTPS, etc.)
2. Backup la base de données
3. Transférer les fichiers
4. Reconfigurer config/database.php
5. Tester en production

---

## 📞 SUPPORT

**Documentation**: Voir les fichiers `*.md` du projet  
**Erreurs**: Vérifier `error_log` du serveur  
**Console**: F12 dans le navigateur  
**PHP**: `error_reporting(E_ALL); ini_set('display_errors', 1);`

---

## ✨ TIPS & TRICKS

### Réinitialiser la BDD rapidement
```bash
mysql -u root nutrivert < migration_20260427.sql
mysql -u root nutrivert < TEST_DATA.sql
```

### Voir les requêtes SQL exécutées
```php
// Dans config/database.php, ajouter:
error_log($sql);
```

### Désactiver le cache CSS/JS
```html
<!-- Dans les liens, ajouter: -->
<link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
```

### Vérifier les sessions
```php
// Au début d'une page:
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";
```

---

## 🎯 RÉSUMÉ

**Le système est prêt!**
1. Importer la migration SQL
2. Accéder à `http://localhost/...`
3. Se connecter (USER ou ADMIN)
4. Tester les fonctionnalités
5. C'est tout! 🎉

---

**Version**: 1.0  
**Date**: 27 Avril 2026  
**Status**: ✅ Production Ready
