<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'NutriVert'); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="brand" style="display:flex; align-items:center;">
        <img 
            src="assets/images/logo.png" 
            alt="Logo NutriVert"
            style="width:120px; height:auto; max-height:45px; display:block;"
        >
        <span style="font-size:22px; font-weight:bold; color:#2f8a2b;">
            NutriVert
        </span>
    </div>

    <nav class="site-nav">
        <a href="index.php?page=front_home">FrontOffice</a>
        <a href="index.php?page=back_dashboard">BackOffice</a>
        <a href="index.php?page=back_recette_create_full">Ajouter recette complète</a>
        <a href="index.php?page=back_recettes_full_edit">Modifier recette complète</a>
    </nav>
</header>
<main>
