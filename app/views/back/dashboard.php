<?php require __DIR__ . '/../partials/header.php'; ?>
<div class="container">
    <section class="hero-panel">
        <h1 class="page-title"><?php echo t('dashboard_title'); ?></h1>
        <p class="hero-text"><?php echo t('dashboard_text'); ?></p>
    </section>

    <div class="dashboard-grid">
        <a class="dashboard-card" href="index.php?page=back_recette_create_full">
            <div class="card-badge">BackOffice</div>
            <h3><i class="fas fa-plus-circle"></i> <?php echo t('add_full'); ?></h3>
            <p><?php echo t('add_full_text'); ?></p>
        </a>
        <a class="dashboard-card" href="index.php?page=back_recettes_full_edit">
            <div class="card-badge">BackOffice</div>
            <h3><i class="fas fa-pen-fancy"></i> <?php echo t('edit_full'); ?></h3>
            <p><?php echo t('edit_full_text'); ?></p>
        </a>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
