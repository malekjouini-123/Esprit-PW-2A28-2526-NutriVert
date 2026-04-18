<?php require __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <h1 class="page-title">Gestion des instructions</h1>

    <p>
        <a class="btn-green" href="index.php?page=back_instruction_create">Ajouter une instruction</a>
    </p>

    <form method="GET" action="index.php" class="search-form">
        <input type="hidden" name="page" value="back_instructions">

        <label for="id_recette">Recherche par ID recette</label>
        <input
            type="text"
            id="id_recette"
            name="id_recette"
            value="<?php echo htmlspecialchars($searchIdRecette ?? ''); ?>"
            placeholder="Entrez l'ID de la recette"
        >

        <button type="submit" class="btn-green">Rechercher</button>
        <a href="index.php?page=back_instructions" class="btn-ghost">Réinitialiser</a>
    </form>

    <?php if (!empty($errorSearch)): ?>
        <div class="error-banner"><?php echo htmlspecialchars($errorSearch); ?></div>
    <?php endif; ?>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>ID Recette</th>
                <th>Titre recette</th>
                <th>Étape</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($instructions)) : ?>
                <?php foreach ($instructions as $instruction) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($instruction['id_instruction']); ?></td>
                        <td><?php echo htmlspecialchars($instruction['id_recette']); ?></td>
                        <td><?php echo htmlspecialchars($instruction['recette_titre'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($instruction['etape']); ?></td>
                        <td><?php echo htmlspecialchars($instruction['description']); ?></td>
                        <td>
                            <a class="btn-green" href="index.php?page=back_instruction_edit&id=<?php echo $instruction['id_instruction']; ?>">Modifier</a>

                            <button
                                type="button"
                                class="btn-confirm open-delete-modal"
                                data-url="index.php?page=back_instruction_delete&id=<?php echo $instruction['id_instruction']; ?>">
                                Supprimer
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="6">Aucune instruction trouvée.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../../partials/footer.php'; ?>