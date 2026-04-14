<?php require __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <h1 class="page-title">Gestion des recettes</h1>

    <p>
        <a class="btn-green" href="index.php?page=back_recette_create_full">Ajouter une recette complète</a>
    </p>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Objectif</th>
                <th>Régime</th>
                <th>Durée</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($recettes)) : ?>
                <?php foreach ($recettes as $recette) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($recette['id_recette']); ?></td>
                        <td><?php echo htmlspecialchars($recette['titre']); ?></td>
                        <td><?php echo htmlspecialchars($recette['objectif']); ?></td>
                        <td><?php echo htmlspecialchars($recette['regime']); ?></td>
                        <td><?php echo htmlspecialchars($recette['duree']); ?> min</td>
                        <td>
                            <a class="btn-green" href="index.php?page=back_recette_edit&id=<?php echo $recette['id_recette']; ?>">Modifier</a>

                            <button
                                type="button"
                                class="btn-confirm open-delete-modal"
                                data-url="index.php?page=back_recette_delete&id=<?php echo $recette['id_recette']; ?>">
                                Supprimer
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6">Aucune recette trouvée.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../../partials/footer.php'; ?>