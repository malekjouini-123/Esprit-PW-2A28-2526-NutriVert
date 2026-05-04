<?php
$lang = function_exists('app_current_lang') ? app_current_lang() : ($_SESSION['lang'] ?? 'fr');
$dir = $lang === 'ar' ? 'rtl' : 'ltr';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang); ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'NutriVert'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="<?php echo $lang === 'ar' ? 'rtl' : ''; ?>">
<header class="site-header">
    <div class="container header-inner">
        <nav class="site-nav">
            <a href="test3.htm"><?php echo t('nav_accueil'); ?></a>
            <a href="index.php?page=back_dashboard"><?php echo t('nav_back'); ?></a>
            <a href="index.php?page=front_home"><?php echo t('nav_front'); ?></a>
            <a href="index.php?page=front_favoris"><i class="fas fa-bookmark"></i> <?php echo t('nav_saved'); ?></a>
        </nav>

        <div class="brand-wrap">
            <div class="lang-switcher">
                <a class="<?php echo $lang === 'fr' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(lang_url('fr')); ?>"><?php echo t('lang_fr'); ?></a>
                <a class="<?php echo $lang === 'en' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(lang_url('en')); ?>"><?php echo t('lang_en'); ?></a>
                <a class="<?php echo $lang === 'ar' ? 'active' : ''; ?>" href="<?php echo htmlspecialchars(lang_url('ar')); ?>"><?php echo t('lang_ar'); ?></a>
            </div>
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
