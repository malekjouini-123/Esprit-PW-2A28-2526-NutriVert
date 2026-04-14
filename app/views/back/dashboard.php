<?php require __DIR__ . '/../partials/header.php'; ?>
<div class="container">
    <h1 class="page-title">Dashboard BackOffice</h1>
    <div class="dashboard-grid">
        <a class="dashboard-card" href="index.php?page=back_recettes">
            <h3>Gestion des recettes</h3>
            <p>Afficher, ajouter, modifier et supprimer les recettes.</p>
        </a>
        <a class="dashboard-card" href="index.php?page=back_instructions">
            <h3>Gestion des instructions</h3>
            <p>Afficher, ajouter, modifier et supprimer les instructions.</p>
        </a>
        <a class="dashboard-card" href="index.php?page=back_recette_create_full">
            <h3>Ajouter recette complète</h3>
            <p>Créer une recette avec plusieurs étapes dans la même page.</p>
        </a>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
