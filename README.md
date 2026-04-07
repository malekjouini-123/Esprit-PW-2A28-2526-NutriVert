# NutriVert 🌿

> Plateforme d'alimentation intelligente et de nutrition durable — développée par l'équipe **Cybernova**.

---

## Description

**NutriVert** est une application web qui combine intelligence artificielle, nutrition personnalisée et commerce local pour offrir une expérience alimentaire complète et durable. La plateforme permet à chaque utilisateur de gérer son alimentation selon ses objectifs de santé, de réduire le gaspillage alimentaire, de commander des produits manquants via une marketplace et de bénéficier d'un suivi personnalisé avec un **nutritionniste** ou un **coach** choisi parmi une liste de professionnels.

---

## Table des Matières

- [Fonctionnalités](#fonctionnalités)
- [Rôles utilisateurs](#rôles-utilisateurs)
- [Architecture du projet](#architecture-du-projet)
- [Technologies utilisées](#technologies-utilisées)
- [Installation](#installation)
- [Utilisation](#utilisation)
- [Contributions](#contributions)
- [Licence](#licence)

---

## Fonctionnalités

### 🧬 Profil Nutritionnel
Chaque utilisateur crée un profil personnalisé contenant :
- Informations personnelles (nom, prénom, email, mot de passe)
- Données physiques : poids, taille
- Régime alimentaire : sans gluten, végétarien, vegan, etc.
- Objectifs : perte de poids, gain musculaire, alimentation équilibrée, etc.

### 🤖 Recettes AI
- Saisie des ingrédients disponibles à la maison
- Analyse du profil nutritionnel de l'utilisateur
- Génération de recettes personnalisées anti-gaspillage
- Recommandations adaptées aux objectifs, régime, poids et taille

### 🛒 Marketplace
En cas d'ingrédients manquants, accès à une marketplace intégrant :
- **Grandes surfaces** partenaires
- **Producteurs locaux** proposant des produits bio et de proximité
- Système de commande intégré

### 🥗 Coaching & Nutritionnistes
- Consultation de la liste des nutritionnistes et coachs disponibles (filtrables par spécialité et expérience)
- Sélection d'un nutritionniste selon son profil
- Soumission d'une demande personnalisée incluant :
  - Description du régime alimentaire actuel
  - Objectifs de santé
  - Données physiques
- Le nutritionniste consulte la demande et génère un **programme alimentaire personnalisé** adapté à l'utilisateur

### 📅 Gestion des Événements
- Organisation et consultation d'événements liés à la nutrition et au bien-être

---

## Rôles Utilisateurs

| Rôle | Description |
|------|-------------|
| 👤 Utilisateur | Crée son profil, accède aux recettes AI, marketplace, coaching et nutritionnistes |
| 🥗 Nutritionniste / Coach | Reçoit les demandes des utilisateurs et leur propose un programme personnalisé |
| 🏪 Fournisseur | Grande surface ou producteur local, gère ses produits sur la marketplace |
| 🔧 Administrateur | Gère la plateforme, les utilisateurs et les contenus |

---

## Architecture du projet

Le projet suit une architecture **MVC (Modèle - Vue - Contrôleur)** :

```
NutriVert/
│
└── projet web/
    ├── view/          → Fichiers HTML : interfaces visibles par l'utilisateur
    ├── model/         → Logique métier et accès à la base de données (PHP)
    └── controller/    → Gestion des requêtes et lien entre Model et View (PHP)
```

- **View** : Pages HTML/CSS/JS affichées à l'utilisateur (formulaires, dashboards, recettes, marketplace…)
- **Model** : Classes PHP qui interagissent avec la base de données MySQL
- **Controller** : Scripts PHP qui traitent les actions utilisateur et appellent les modèles correspondants

---

## Technologies utilisées

| Technologie | Rôle |
|-------------|------|
| HTML | Structure des pages |
| CSS| Mise en forme et design |
| JavaScript | Interactivité côté client |
| PHP | Logique serveur (architecture MVC) |
| MySQL | Base de données |
| Apache 2.4 | Serveur Web via XAMPP |

---

## Installation

### Prérequis

-  XAMPP installé
- PHP 
- MySQL 
- phpMyAdmin

### 1. Cloner le repository

```bash
git clone https://github.com/malekjouini-123/Esprit-PW-2A28-2526-NutriVert.git
cd Esprit-PW-2A28-2526-NutriVert
```

### 2. Placer le projet dans le serveur local

- Copiez le dossier dans `projet web **(WAMP)** ou `htdocs/` **(XAMPP)**
- Démarrez **Apache** et **MySQL** depuis l'interface WAMP/XAMPP

### 3. Créer la base de données

- Ouvrez **phpMyAdmin** : [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
- Créez une nouvelle base de données nommée `nutrivert`
- Importez le fichier SQL :

```bash
mysql -u root -p nutrivert < database/nutrivert.sql
```

### 4. Configurer la connexion

Modifiez le fichier de configuration dans `model/` pour y renseigner vos informations de connexion :

```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'nutrivert');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 5. Accéder au projet

```
http://localhost/Esprit-PW-2A28-2526-NutriVert/projet web/view/
```

---

## Utilisation

### Créer un compte utilisateur

1. Accédez à la page d'inscription
2. Remplissez vos informations personnelles
3. Complétez votre profil nutritionnel (poids, taille, régime, objectifs)
4. Validez pour accéder au tableau de bord

### Générer une recette AI

1. Cliquez sur l'icône **Recettes**
2. Entrez vos ingrédients disponibles
3. Le système analyse votre profil et génère une recette personnalisée adaptée à vos objectifs

### Commander un ingrédient manquant

1. Depuis la suggestion de recette, cliquez sur **Voir dans la Marketplace**
2. Choisissez votre fournisseur (grande surface ou producteur local)
3. Ajoutez au panier et passez commande

### Choisir un nutritionniste

1. Cliquez sur **Coaching / Nutritionnistes**
2. Parcourez la liste des professionnels filtrables par spécialité et expérience
3. Sélectionnez un nutritionniste et soumettez votre demande avec votre profil et vos objectifs
4. Le nutritionniste consulte votre dossier et vous envoie un **programme alimentaire personnalisé**

---

## Contributions

Nous remercions tous ceux qui contribuent à ce projet !

### Comment contribuer ?

1. **Forkez le projet** depuis GitHub

2. **Clonez votre fork** :

```bash
git clone https://github.com/votre-utilisateur/Esprit-PW-2A28-2526-NutriVert.git
cd Esprit-PW-2A28-2526-NutriVert
```

3. **Créez une branche** pour votre fonctionnalité :

```bash
git checkout -b feature/ma-fonctionnalite
```

4. **Commitez vos modifications** :

```bash
git add .
git commit -m "Ajout : description de la fonctionnalité"
```

5. **Poussez et soumettez une Pull Request** :

```bash
git push origin feature/ma-fonctionnalite
```

---

## Licence

Ce projet est sous la licence **MIT**. Pour plus de détails, consultez le fichier [LICENSE](./LICENSE).

---

<p align="center">
  Fait avec ❤️ par l'équipe <strong>Cybernova</strong> — Esprit · Année universitaire 2025-2026
</p>
