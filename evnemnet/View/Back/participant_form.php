<?php require_once __DIR__ . '/../layout/header_admin.php'; ?>

<div style="margin-bottom: 40px;">
    <h1 style="color: var(--deep); font-weight: 700;">Modifier un Participant</h1>
    <p style="color: #8A9A86;">Mettez à jour les informations ci-dessous.</p>
</div>

<div class="section-card">
    <form action="admin.php?action=edit_participant&id=<?= (int)$participant['id'] ?>" method="POST" class="ingredient-group">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; width: 100%;">
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Nom</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($participant['nom']) ?>" required>
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Prénom</label>
                <input type="text" name="prenom" value="<?= htmlspecialchars($participant['prenom']) ?>" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; width: 100%; margin-top: 20px;">
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($participant['email']) ?>" required>
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Téléphone</label>
                <input type="text" name="telephone" value="<?= htmlspecialchars($participant['telephone'] ?? '') ?>" placeholder="Optionnel">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; width: 100%; margin-top: 20px;">
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Poids (kg)</label>
                <input type="number" step="0.1" name="poids" id="participant_poids" value="<?= htmlspecialchars((string)($participant['poids'] ?? 0)) ?>">
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Taille (cm)</label>
                <input type="number" step="0.1" name="taille" id="participant_taille" value="<?= htmlspecialchars((string)($participant['taille'] ?? 0)) ?>">
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">IMC <small style="font-weight:400;color:#8A9A86;">(auto)</small></label>
                <input type="number" step="0.01" name="imc" id="participant_imc" value="<?= isset($participant['imc_display']) && $participant['imc_display'] !== null ? htmlspecialchars((string)$participant['imc_display']) : '' ?>" readonly style="background:#f5f5f5;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; width: 100%; margin-top: 20px;">
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Lieu</label>
                <input type="text" name="lieu" value="<?= htmlspecialchars($participant['lieu'] ?? '') ?>">
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Objectif</label>
                <select name="objectif" style="padding: 15px; border-radius: 20px; border: 2px solid #FFD5C2; font-family: 'Quicksand';">
                    <?php $objectif = $participant['objectif'] ?? 'maintien'; ?>
                    <option value="perte" <?= $objectif === 'perte' ? 'selected' : '' ?>>Perte de poids</option>
                    <option value="maintien" <?= $objectif === 'maintien' ? 'selected' : '' ?>>Maintien</option>
                    <option value="prise" <?= $objectif === 'prise' ? 'selected' : '' ?>>Prise de masse</option>
                </select>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px; width: 100%; margin-top: 20px;">
            <label style="font-weight: 600; color: var(--deep);">Mot de passe (laisser vide pour ne pas changer)</label>
            <input type="password" name="mot_de_passe" value="" placeholder="••••••••">
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 15px; width: 100%; margin-top: 30px;">
            <a href="admin.php?action=participants" class="edit-btn" style="background: #eee;">Annuler</a>
            <button type="submit" class="btn-primary">Mettre à jour</button>
        </div>
    </form>
</div>

<script>
(function () {
    var p = document.getElementById('participant_poids');
    var t = document.getElementById('participant_taille');
    var i = document.getElementById('participant_imc');
    if (!p || !t || !i) return;
    function calc() {
        var po = parseFloat(p.value, 10);
        var ta = parseFloat(t.value, 10);
        if (po > 0 && ta > 0) {
            var m = ta / 100;
            i.value = (po / (m * m)).toFixed(2);
        } else {
            i.value = '';
        }
    }
    p.addEventListener('input', calc);
    t.addEventListener('input', calc);
    calc();
})();
</script>

<?php require_once __DIR__ . '/../layout/footer_admin.php'; ?>
