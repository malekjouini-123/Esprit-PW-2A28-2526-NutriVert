<?php require __DIR__ . '/../partials/header.php'; ?>
<div class="container">
    <section class="hero-panel">
        <h1 class="page-title">Dashboard BackOffice</h1>
        <p class="hero-text">Gestion complète des recettes dans un style visuel aligné sur test3.</p>
    </section>

    <div class="dashboard-grid">
        <a class="dashboard-card" href="index.php?page=back_recette_create_full">
            <div class="card-badge">BackOffice</div>
            <h3><i class="fas fa-plus-circle"></i> Ajouter recette complète</h3>
            <p>Créer une recette avec plusieurs étapes dans la même page.</p>
        </a>
        <a class="dashboard-card" href="index.php?page=back_recettes_full_edit">
            <div class="card-badge">BackOffice</div>
            <h3><i class="fas fa-pen-fancy"></i> Modifier recette complète</h3>
            <p>Choisir une recette et modifier ou supprimer la recette avec ses instructions dans une seule page.</p>
        </a>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
