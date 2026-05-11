<?php require_once __DIR__ . '/../layout/header_admin.php'; ?>
<?php
$defaultSubject = $_POST['subject'] ?? ('Message pour ' . ($participant['prenom'] ?? '') . ' ' . ($participant['nom'] ?? ''));
$defaultMessage = $_POST['message'] ?? '';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <h1 style="color: var(--deep); font-weight: 700; margin-bottom: 6px;">
            Envoyer un Gmail
        </h1>
        <p style="color: #8A9A86; margin: 0;">
            Destinataire : <?= htmlspecialchars((string)($participant['email'] ?? '')) ?>
        </p>
    </div>
    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="admin.php?action=show_participant&id=<?= (int)$participant['id'] ?>" class="edit-btn" style="background: #eee; text-decoration: none;">Retour</a>
    </div>
</div>

<div class="section-card" style="max-width: 900px;">
    <div style="margin-bottom: 20px; padding: 16px; border-radius: 14px; background: #FFF5F2; border-left: 5px solid #D14836;">
        <div style="font-weight: 700; color: #8A4A3B; margin-bottom: 6px;">Configuration Gmail</div>
        <div style="color: #8A4A3B; line-height: 1.5;">
            Vérifiez que `config.email.php` contient vos identifiants Gmail SMTP et que `composer install` a bien chargé PHPMailer.
        </div>
    </div>

    <form action="admin.php?action=email_participant&id=<?= (int)$participant['id'] ?>" method="POST" class="ingredient-group">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; width: 100%; margin-bottom: 20px;">
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Participant</label>
                <input type="text" value="<?= htmlspecialchars(trim((string)($participant['prenom'] ?? '') . ' ' . (string)($participant['nom'] ?? ''))) ?>" readonly style="background: #f5f5f5;">
            </div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label style="font-weight: 600; color: var(--deep);">Email</label>
                <input type="email" value="<?= htmlspecialchars((string)($participant['email'] ?? '')) ?>" readonly style="background: #f5f5f5;">
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px; width: 100%; margin-bottom: 20px;">
            <label style="font-weight: 600; color: var(--deep);">Sujet</label>
            <input type="text" name="subject" value="<?= htmlspecialchars((string)$defaultSubject) ?>" required>
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px; width: 100%;">
            <label style="font-weight: 600; color: var(--deep);">Message</label>
            <textarea name="message" rows="10" required><?= htmlspecialchars((string)$defaultMessage) ?></textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 15px; width: 100%; margin-top: 30px;">
            <a href="admin.php?action=show_participant&id=<?= (int)$participant['id'] ?>" class="edit-btn" style="background: #eee;">Annuler</a>
            <button type="submit" class="btn-primary" style="background: #D14836;">
                <i class="bi bi-google"></i> Envoyer le Gmail
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layout/footer_admin.php'; ?>
