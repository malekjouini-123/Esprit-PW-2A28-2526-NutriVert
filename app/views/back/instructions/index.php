<?php require __DIR__ . '/../../partials/header.php'; ?>

<div class="container">
    <h1 class="page-title">Gestion des instructions</h1>

    <p>
        <a class="btn-green" href="index.php?page=back_instruction_create">Ajouter une instruction</a>
    </p>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>ID Recette</th>
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
                    <td colspan="5">Aucune instruction trouvée.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/../../partials/footer.php'; ?>