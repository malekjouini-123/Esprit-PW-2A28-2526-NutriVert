<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'NutriVert'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <nav class="site-nav">
            <a href="test3.htm">Accueil</a>
            <a href="index.php?page=back_dashboard">BackOffice</a>
            <a href="index.php?page=front_home">FrontOffice</a>
            <a href="index.php?page=front_favoris"><i class="fas fa-bookmark"></i> Mes recettes enregistrées</a>
        </nav>

        <div class="brand-wrap">
            <a href="index.php?page=front_home" class="brand">
                <span class="brand-text">Nutrivert</span>
                <span class="brand-logo-shell">
                    <img src="assets/images/logo.png" alt="Logo Nutrivert" class="brand-logo-image">
                </span>
            </a>
        </div>
    </div>
</header>
<main>
