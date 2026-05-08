# 🚀 DÉMARRAGE IMMÉDIAT - 2 MINUTES

**Vous êtes prêt à tester le système!**

## ⏱️ ÉTAPE 1 (30 secondes)

Ouvrir un terminal MySQL:
```bash
mysql -u root
CREATE DATABASE IF NOT EXISTS nutrivert;
USE nutrivert;
SOURCE migration_20260427.sql;
```

## ⏱️ ÉTAPE 2 (30 secondes)

Importer les données de test:
```bash
USE nutrivert;
SOURCE TEST_DATA.sql;
```

## ⏱️ ÉTAPE 3 (1 minute)

Accéder au site:
```
http://localhost/coaching/
```

## 🎯 TESTER AVEC

### Utilisateur Normal
```
Email:    jean@nutrivert.fr
Password: password123
```

### Administrateur
```
Email:    admin@nutrivert.fr
Password: admin123
```

## ✨ CE QUE VOUS VERREZ

1. **Login** → Se connecter avec email/password
2. **Dashboard** → Profil utilisateur
3. **Coachings** → Bouton "Voir tous les Coachings"
4. **Liste** → Cartes avec titre, image, difficulté
5. **Session** → Timer démarre automatiquement
6. **Exercices** → Progression en ordre (1, 2, 3...)
7. **Durée** → Timer adapté par exercice (30, 45, 60 sec)
8. **Fin** → Message "Coaching terminé 🎉" avec confettis

## 📚 DOCUMENTATION

Lire ensuite:
- [QUICK_START.md](QUICK_START.md) - Guide 5 min
- [README_COACHING.md](README_COACHING.md) - Comprendre
- [TEST_GUIDE.md](TEST_GUIDE.md) - Tester complètement
- [INDEX.md](INDEX.md) - Chercher de l'aide

## 🎉 C'EST PRÊT!

Le système est **100% complet et fonctionnel**. Pas de modifications à faire. Juste à tester et utiliser!

---

**Status**: ✅ PRODUCTION READY 🚀
