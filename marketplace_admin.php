<?php
declare(strict_types=1);
/**
 * Vue admin marketplace — CRUD formulaires.
 * Validation JS identique au front : ../../js/nv_saisie_marketplace.js (objet global NV_SaisieMarketplace).
 *
 * @var array $categories @var array $produits @var ?array $flash @var ?array $editCat @var ?array $editProd
 * @var array $labelOptions @var array $iconeOptions
 */

if (!function_exists('e')) {
    function e(?string $s): string
    {
        return htmlspecialchars((string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

$ec = is_array($editCat ?? null) ? $editCat : [];
$ep = is_array($editProd ?? null) ? $editProd : [];

$prodLabelsSelected = [];
if ($ep !== [] && !empty($ep['label'])) {
    foreach (array_map('trim', explode(',', (string) $ep['label'])) as $p) {
        if ($p !== '') {
            $prodLabelsSelected[] = $p;
        }
    }
}
$labelIsChecked = static function (string $option, array $selected): bool {
    foreach ($selected as $s) {
        if (strcasecmp($option, (string) $s) === 0) {
            return true;
        }
    }
    return false;
};
?>
<style>
    .adm-flash {
        padding: 0.75rem 1rem;
        border-radius: 0.65rem;
        margin-bottom: 1.25rem;
        font-weight: 600;
        font-size: 0.92rem;
    }
    .adm-flash.ok { background: #e3f2dc; color: #1e4a14; border: 1px solid #a8d49a; }
    .adm-flash.err { background: #fdecea; color: #6b221c; border: 1px solid #e8b4b0; }
    .adm-section {
        background: #fff;
        border: 1px solid rgba(45, 79, 30, 0.1);
        border-radius: 0.75rem;
        padding: 1.25rem 1.35rem;
        margin-bottom: 1.75rem;
        box-shadow: 0 2px 12px rgba(45, 79, 30, 0.04);
    }
    .adm-section h2 {
        font-size: 1.05rem;
        font-weight: 700;
        color: #2d4f1e;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid rgba(45, 79, 30, 0.08);
    }
    .adm-form-grid {
        display: grid;
        gap: 0.75rem;
        max-width: 520px;
    }
    .adm-form-grid label {
        font-size: 0.78rem;
        font-weight: 700;
        color: #2d4f1e;
        display: block;
        margin-bottom: 0.2rem;
    }
    .adm-form-grid input[type="text"],
    .adm-form-grid input[type="number"],
    .adm-form-grid select {
        width: 100%;
        padding: 0.55rem 0.75rem;
        border: 1px solid rgba(45, 79, 30, 0.2);
        border-radius: 0.5rem;
        font-family: inherit;
        font-size: 0.9rem;
        background: #fcfdf7;
        color: #2d4f1e;
    }
    .adm-labels {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem 1rem;
        margin-top: 0.25rem;
    }
    .adm-labels label { font-weight: 500; display: inline-flex; align-items: center; gap: 0.35rem; margin: 0; }
    .adm-actions-row { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 1rem; }
    .btn-adm {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.88rem;
        cursor: pointer;
        border: none;
        font-family: inherit;
    }
    .btn-adm-primary { background: #3d6b2e; color: #fff; }
    .btn-adm-primary:hover { background: #325c25; }
    .btn-adm-muted { background: #eef3e8; color: #2d4f1e; border: 1px solid rgba(45, 79, 30, 0.15); }
    .btn-adm-muted:hover { background: #e4eadc; }
    .btn-adm-danger { background: #a53c32; color: #fff; }
    .btn-adm-danger:hover { background: #8e332a; }
    .adm-table-wrap { overflow-x: auto; margin-top: 0.5rem; }
    table.adm-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.88rem;
    }
    table.adm-table th {
        text-align: left;
        padding: 0.65rem 0.75rem;
        background: #f0f4eb;
        color: #2d4f1e;
        font-weight: 700;
        border-bottom: 2px solid rgba(45, 79, 30, 0.12);
    }
    table.adm-table td {
        padding: 0.6rem 0.75rem;
        border-bottom: 1px solid rgba(45, 79, 30, 0.08);
        vertical-align: middle;
    }
    table.adm-table tr:hover td { background: #fafbf6; }
    .adm-table-actions { display: flex; flex-wrap: wrap; gap: 0.35rem; align-items: center; }
    .adm-table-actions a {
        color: #2d6e1f;
        font-weight: 600;
        text-decoration: none;
        font-size: 0.82rem;
    }
    .adm-table-actions a:hover { text-decoration: underline; }
    .adm-muted { color: rgba(45, 79, 30, 0.55); font-size: 0.85rem; }
    .adm-two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
    @media (max-width: 640px) { .adm-two-col { grid-template-columns: 1fr; } }
    /* Même style d’erreurs que le front (view/nurtvie.php) */
    .adm-form-grid input.field-invalid,
    .adm-form-grid select.field-invalid,
    .adm-labels.field-invalid {
        border-color: #c44a3e !important;
        box-shadow: 0 0 0 2px rgba(196, 74, 62, 0.12);
    }
    .mp-field-error {
        display: block;
        color: #9a2e24;
        font-size: 0.78rem;
        font-weight: 600;
        margin: 0.2rem 0 0;
        min-height: 1.15em;
    }
</style>

<?php if (!empty($flash) && is_array($flash)) : ?>
    <div class="adm-flash <?= !empty($flash['ok']) ? 'ok' : 'err' ?>" role="alert">
        <?= e($flash['message'] ?? '') ?>
    </div>
<?php endif; ?>

<p class="adm-muted" style="margin-bottom:1.25rem;">
    Gestion des <strong>catégories</strong> et <strong>produits</strong> marketplace — mêmes données que la base <strong>NutriVert</strong> (affichage public sur la page Marketplace du site).
</p>

<section class="adm-section" id="categories">
    <h2><i class="fas fa-folder-open"></i> <?= $editCat ? 'Modifier une catégorie' : 'Ajouter une catégorie' ?></h2>
    <form id="admFormCat" method="post" class="adm-form-grid" novalidate>
        <input type="hidden" name="admin_action" value="cat_save">
        <input type="hidden" name="cat_id" value="<?= (int) ($ec['id'] ?? 0) ?>">
        <div>
            <label for="cat_nom">Nom</label>
            <input type="text" id="cat_nom" name="cat_nom" maxlength="120"
                   value="<?= e($ec['nom'] ?? '') ?>" placeholder="Ex. Légumes, Fruits…">
            <span id="cat_nom_err" class="mp-field-error" role="alert"></span>
        </div>
        <div>
            <label for="cat_description">Description (optionnel)</label>
            <input type="text" id="cat_description" name="cat_description" maxlength="255"
                   value="<?= e($ec['description'] ?? '') ?>">
            <span id="cat_description_err" class="mp-field-error" role="alert"></span>
        </div>
        <div class="adm-actions-row">
            <button type="submit" class="btn-adm btn-adm-primary"><?= $editCat ? 'Enregistrer' : 'Ajouter' ?></button>
            <?php if ($editCat) : ?>
                <a href="marketplace.php" class="btn-adm btn-adm-muted" style="display:inline-flex;align-items:center;">Annuler</a>
            <?php endif; ?>
        </div>
    </form>

    <h2 style="margin-top:1.75rem;">Liste des catégories</h2>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($categories === []) : ?>
                    <tr><td colspan="4" class="adm-muted">Aucune catégorie.</td></tr>
                <?php else : ?>
                    <?php foreach ($categories as $c) : ?>
                        <tr>
                            <td><?= (int) $c['id'] ?></td>
                            <td><strong><?= e($c['nom']) ?></strong></td>
                            <td><?= e($c['description'] ?? '') ?></td>
                            <td>
                                <div class="adm-table-actions">
                                    <a href="marketplace.php?edit_cat=<?= (int) $c['id'] ?>#categories">Modifier</a>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer cette catégorie ?');">
                                        <input type="hidden" name="admin_action" value="cat_delete">
                                        <input type="hidden" name="cat_id" value="<?= (int) $c['id'] ?>">
                                        <button type="submit" class="btn-adm btn-adm-danger" style="padding:0.25rem 0.6rem;font-size:0.78rem;">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="adm-section" id="produits">
    <h2><i class="fas fa-carrot"></i> <?= $editProd ? 'Modifier un produit' : 'Ajouter un produit' ?></h2>
    <form id="admFormProd" method="post" class="adm-form-grid" style="max-width:640px;" novalidate>
        <input type="hidden" name="admin_action" value="prod_save">
        <input type="hidden" name="prod_id" value="<?= (int) ($ep['id'] ?? 0) ?>">
        <div>
            <label for="prod_categorie_id">Catégorie</label>
            <select id="prod_categorie_id" name="prod_categorie_id">
                <option value="">— Choisir —</option>
                <?php foreach ($categories as $c) : ?>
                    <option value="<?= (int) $c['id'] ?>"
                        <?= (isset($ep['categorie_id']) && (int) $ep['categorie_id'] === (int) $c['id']) ? ' selected' : '' ?>>
                        <?= e($c['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span id="prod_categorie_err" class="mp-field-error" role="alert"></span>
        </div>
        <div>
            <label for="prod_nom">Nom du produit</label>
            <input type="text" id="prod_nom" name="prod_nom" maxlength="180"
                   value="<?= e($ep['nom'] ?? '') ?>">
            <span id="prod_nom_err" class="mp-field-error" role="alert"></span>
        </div>
        <div>
            <span style="font-size:0.78rem;font-weight:700;color:#2d4f1e;display:block;margin-bottom:0.35rem;">Labels</span>
            <div class="adm-labels" id="adm_prod_labels_wrap">
                <?php foreach ($labelOptions as $lo) : ?>
                    <label>
                        <input type="checkbox" name="prod_labels[]" value="<?= e($lo) ?>"
                            <?= $labelIsChecked($lo, $prodLabelsSelected) ? ' checked' : '' ?>>
                        <?= e($lo) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div>
            <label for="prod_producteur">Producteur / origine</label>
            <input type="text" id="prod_producteur" name="prod_producteur" maxlength="150"
                   value="<?= e($ep['producteur'] ?? '') ?>">
            <span id="prod_producteur_err" class="mp-field-error" role="alert"></span>
        </div>
        <div class="adm-two-col">
            <div>
                <label for="prod_prix">Prix (DT)</label>
                <input type="number" id="prod_prix" name="prod_prix" step="0.01" min="0"
                       value="<?= e(isset($ep['prix']) ? (string) $ep['prix'] : '0') ?>">
                <span id="prod_prix_err" class="mp-field-error" role="alert"></span>
            </div>
            <div>
                <label for="prod_co2">Empreinte CO₂ (kg)</label>
                <input type="number" id="prod_co2" name="prod_co2" step="0.01" min="0"
                       value="<?= e(isset($ep['empreinte_co2']) && $ep['empreinte_co2'] !== null && $ep['empreinte_co2'] !== '' ? (string) $ep['empreinte_co2'] : '') ?>"
                       placeholder="optionnel">
                <span id="prod_co2_err" class="mp-field-error" role="alert"></span>
            </div>
        </div>
        <div>
            <label for="prod_icone">Icône</label>
            <select id="prod_icone" name="prod_icone">
                <?php foreach ($iconeOptions as $val => $lab) : ?>
                    <option value="<?= e($val) ?>"
                        <?= (isset($ep['icone']) && $ep['icone'] === $val) ? ' selected' : '' ?>>
                        <?= e($lab) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="adm-actions-row">
            <button type="submit" class="btn-adm btn-adm-primary"><?= $editProd ? 'Enregistrer' : 'Ajouter' ?></button>
            <?php if ($editProd) : ?>
                <a href="marketplace.php" class="btn-adm btn-adm-muted" style="display:inline-flex;align-items:center;">Annuler</a>
            <?php endif; ?>
        </div>
    </form>

    <h2 style="margin-top:1.75rem;">Liste des produits</h2>
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Catégorie</th>
                    <th>Nom</th>
                    <th>Labels</th>
                    <th>Producteur</th>
                    <th>Prix</th>
                    <th>CO₂</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($produits === []) : ?>
                    <tr><td colspan="8" class="adm-muted">Aucun produit.</td></tr>
                <?php else : ?>
                    <?php foreach ($produits as $p) : ?>
                        <tr>
                            <td><?= (int) $p['id'] ?></td>
                            <td><?= e($p['categorie_nom'] ?? '') ?></td>
                            <td><strong><?= e($p['nom']) ?></strong></td>
                            <td><?= e($p['label'] ?? '—') ?></td>
                            <td><?= e($p['producteur'] ?? '—') ?></td>
                            <td><?= e(number_format((float) $p['prix'], 2, ',', ' ')) ?> DT</td>
                            <td><?= isset($p['empreinte_co2']) && $p['empreinte_co2'] !== null && $p['empreinte_co2'] !== '' ? e((string) $p['empreinte_co2']) : '—' ?></td>
                            <td>
                                <div class="adm-table-actions">
                                    <a href="marketplace.php?edit_prod=<?= (int) $p['id'] ?>#produits">Modifier</a>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer ce produit ?');">
                                        <input type="hidden" name="admin_action" value="prod_delete">
                                        <input type="hidden" name="prod_id" value="<?= (int) $p['id'] ?>">
                                        <button type="submit" class="btn-adm btn-adm-danger" style="padding:0.25rem 0.6rem;font-size:0.78rem;">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<script src="../js/nv_saisie_marketplace.js"></script>
<script>
(function () {
    var NV = window.NV_SaisieMarketplace;
    if (!NV) return;

    var NV_CFG_ADM_CAT = {
        nomId: 'cat_nom',
        descId: 'cat_description',
        errNomId: 'cat_nom_err',
        errDescId: 'cat_description_err'
    };
    var NV_CFG_ADM_PROD = {
        categorieId: 'prod_categorie_id',
        nomId: 'prod_nom',
        producteurId: 'prod_producteur',
        prixId: 'prod_prix',
        co2Id: 'prod_co2',
        errCategorieId: 'prod_categorie_err',
        errNomId: 'prod_nom_err',
        errProducteurId: 'prod_producteur_err',
        errPrixId: 'prod_prix_err',
        errCo2Id: 'prod_co2_err',
        labelsWrapId: 'adm_prod_labels_wrap'
    };

    NV.attachClearListenersCategory('#admFormCat', NV_CFG_ADM_CAT);
    NV.attachClearListenersProduct('#admFormProd', NV_CFG_ADM_PROD);

    document.getElementById('admFormCat')?.addEventListener('submit', function (e) {
        if (!NV.validateCategory(NV_CFG_ADM_CAT)) {
            e.preventDefault();
            document.getElementById('categories')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    document.getElementById('admFormProd')?.addEventListener('submit', function (e) {
        if (!NV.validateProduct(NV_CFG_ADM_PROD)) {
            e.preventDefault();
            document.getElementById('produits')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
})();
</script>
