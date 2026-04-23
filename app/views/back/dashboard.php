<?php require __DIR__ . '/../partials/header.php'; ?>
<div class="container">
    <h1 class="page-title">Dashboard BackOffice</h1>
    <div class="dashboard-grid">
        <a class="dashboard-card" href="index.php?page=back_recette_create_full">
            <h3>Ajouter recette complète</h3>
            <p>Créer une recette avec plusieurs étapes dans la même page.</p>
        </a>
        <a class="dashboard-card" href="index.php?page=back_recettes_full_edit">
            <h3>Modifier recette complète</h3>
            <p>Choisir une recette et modifier ou supprimer la recette avec ses instructions dans une seule page.</p>
        </a>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
