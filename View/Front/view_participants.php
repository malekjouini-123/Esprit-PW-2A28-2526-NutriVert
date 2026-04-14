<?php
declare(strict_types=1);
/**
 * Vue Participants - Inscription Classique (Ancienne Version).
 * @var Evenement[] $evenements
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S'inscrire | NutriVert</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: #f8fcf6; 
            color: #1a3a1a; 
            scroll-behavior: smooth; 
        }

        /* Glassmorphism */
        .glass-card { 
            background: rgba(255, 255, 250, 0.65); 
            backdrop-filter: blur(14px); 
            border-radius: 2rem; 
            border: 1px solid rgba(100, 180, 70, 0.35); 
            box-shadow: 0 15px 35px -12px rgba(0, 0, 0, 0.08); 
            transition: all 0.35s cubic-bezier(0.2, 0.9, 0.4, 1.1); 
            overflow: hidden;
        }

        /* Header */
        header { 
            background: rgba(255, 255, 245, 0.92); 
            backdrop-filter: blur(20px); 
            padding: 0.9rem 2.5rem; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            flex-wrap: wrap; 
            position: sticky; 
            top: 0; 
            z-index: 1000; 
            border-bottom: 1px solid rgba(90, 160, 60, 0.3); 
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.02); 
        }

        .logo-area { 
            display: flex; 
            align-items: center; 
            gap: 1rem; 
            background: rgba(230, 255, 220, 0.7); 
            padding: 0.4rem 1.2rem 0.4rem 1rem; 
            border-radius: 3rem; 
            transition: all 0.3s ease; 
            border: 1px solid rgba(100, 180, 70, 0.5); 
            text-decoration: none;
        }
        .logo-text { font-size: 1.7rem; font-weight: 800; background: linear-gradient(125deg, #1f5e1a, #4cae4c); -webkit-background-clip: text; background-clip: text; color: transparent; }

        nav { display: flex; align-items: center; gap: 1.2rem; flex-wrap: wrap; }
        nav a { color: #2a5522; text-decoration: none; font-weight: 600; transition: 0.2s; font-size: 0.95rem; padding: 0.4rem 0.2rem; border-bottom: 2px solid transparent; }
        nav a:hover, nav a.active { color: #3c9e2a; border-bottom-color: #6fbf4c; }

        .btn-primary-green { background: linear-gradient(105deg, #4cae4c, #2f8a2b); border: none; padding: 0.6rem 1.4rem; border-radius: 2rem; font-weight: 700; color: white; cursor: pointer; transition: 0.2s; text-decoration: none; display: inline-block; }

        /* Hero */
        .hero { 
            text-align: center; 
            padding: 3.5rem 1.5rem; 
            background: rgba(235, 255, 225, 0.6); 
            backdrop-filter: blur(6px); 
            margin: 1.5rem auto 3rem; 
            max-width: 1200px;
            border-radius: 3rem; 
            border: 1px solid rgba(100, 180, 70, 0.5); 
        }
        .hero h2 { font-size: 2.7rem; font-weight: 800; background: linear-gradient(125deg, #1c6e1a, #60b840); -webkit-background-clip: text; background-clip: text; color: transparent; }

        /* Main Container */
        .container { max-width: 1200px; margin: 0 auto; padding: 0 2rem 4rem; }

        /* Form Layout */
        .main-grid { display: flex; justify-content: center; }
        .form-side { width: 100%; max-width: 800px; padding: 2rem; }
        .register-form { display: grid; gap: 1.5rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .form-field { display: flex; flex-direction: column; gap: 0.5rem; }
        .form-field label { font-size: 0.85rem; font-weight: 700; color: #2c4d24; text-transform: uppercase; }
        .form-field input, .form-field select { padding: 1rem 1.2rem; border: 1px solid rgba(100, 180, 70, 0.2); border-radius: 1.2rem; background: rgba(255,255,255,0.8); font-family: inherit; font-size: 1rem; outline: none; transition: all 0.3s; }
        .form-field input:focus, .form-field select:focus { border-color: #4cae4c; box-shadow: 0 0 0 4px rgba(76, 174, 76, 0.1); }

        .imc-box { background: rgba(200, 230, 181, 0.3); padding: 1.5rem; border-radius: 1.5rem; border: 1px solid rgba(100, 180, 70, 0.3); text-align: center; }
        .imc-val { font-size: 2.2rem; font-weight: 800; color: #1f5e1a; display: block; }
        .imc-lbl { font-size: 0.8rem; font-weight: 700; color: #2c4d24; text-transform: uppercase; letter-spacing: 1px; }

        /* Validation Styles */
        .error-msg { color: #e74c3c; font-size: 0.75rem; font-weight: 700; margin-top: 0.3rem; display: none; }
        .form-field.has-error input, .form-field.has-error select { border-color: #e74c3c; background: #fff5f5; }
        .form-field.has-error .error-msg { display: block; }

        /* Participants Table */
        .participants-section { margin-top: 5rem; }
        .table-container { overflow-x: auto; border-radius: 1.5rem; border: 1px solid rgba(100, 180, 70, 0.3); background: rgba(255, 255, 250, 0.8); }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th { background: rgba(76, 174, 76, 0.1); color: #1f5e1a; font-weight: 800; text-transform: uppercase; font-size: 0.75rem; padding: 1.2rem; text-align: left; }
        td { padding: 1.2rem; border-bottom: 1px solid rgba(100, 180, 70, 0.1); font-size: 0.95rem; color: #2c4d24; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background: rgba(200, 230, 181, 0.1); }

        .action-btns { display: flex; gap: 0.5rem; }
        .btn-mini-edit { background: #f1c40f; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 0.6rem; cursor: pointer; transition: 0.2s; }
        .btn-mini-delete { background: #e74c3c; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 0.6rem; cursor: pointer; transition: 0.2s; }
        .btn-mini-edit:hover { background: #d4ac0d; transform: scale(1.1); }
        .btn-mini-delete:hover { background: #c0392b; transform: scale(1.1); }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 2.5rem;
            border-radius: 2rem;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
            position: relative;
            animation: modalFadeIn 0.3s ease-out;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 2px solid #f0f9ea;
            padding-bottom: 1rem;
        }

        .modal-header h3 {
            font-size: 1.8rem;
            color: #1f5e1a;
            font-weight: 800;
        }

        .close-modal {
            font-size: 1.5rem;
            color: #95a5a6;
            cursor: pointer;
            transition: 0.2s;
        }

        .close-modal:hover { color: #e74c3c; }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .detail-label {
            font-size: 0.75rem;
            font-weight: 800;
            color: #7f8c8d;
            text-transform: uppercase;
        }

        .detail-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .detail-full {
            grid-column: span 2;
        }

        .imc-badge {
            display: inline-block;
            padding: 0.3rem 1rem;
            border-radius: 2rem;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
        }

        footer { text-align: center; padding: 2rem; background: rgba(30, 55, 25, 0.9); color: #e0f0cf; margin-top: 4rem; font-size: 0.9rem; }

        @media (max-width: 900px) { .main-grid, .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <a href="index.php" class="logo-area" style="border: none; background: none;">
            <span class="logo-text">NutriVert</span>
        </a>
        <nav>
            <a href="index.php?sub=events">Événements & Suivi</a>
            <a href="index.php?sub=categories">Catégories</a>
            <a href="index.php?sub=participants" class="active">Participants</a>
            <a href="index.php?sub=participants" class="btn-primary-green">S'inscrire</a>
        </nav>
    </header>

    <div class="container" style="margin-top: 3rem;">
        <?php if ($participant): ?>
            <div class="glass-card" style="padding: 3rem; text-align: center; margin-bottom: 4rem;">
                <h2 style="font-size: 2.5rem; color: #1f5e1a; margin-bottom: 1.5rem;">Bienvenue, <?= htmlspecialchars($participant->prenom) ?> !</h2>
                <p style="font-size: 1.1rem; color: #2c4d24; margin-bottom: 2rem;">Voici vos informations de suivi nutritionnel.</p>
                
                <div class="detail-grid" style="text-align: left; max-width: 800px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div class="detail-item">
                        <span class="detail-label">Nom Complet</span>
                        <span class="detail-value"><?= htmlspecialchars($participant->prenom . ' ' . $participant->nom) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email</span>
                        <span class="detail-value"><?= htmlspecialchars($participant->email) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Téléphone</span>
                        <span class="detail-value"><?= htmlspecialchars($participant->telephone) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Ville</span>
                        <span class="detail-value"><?= htmlspecialchars($participant->lieu) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Poids / Taille</span>
                        <span class="detail-value"><?= $participant->poids ?> kg / <?= $participant->taille ?> cm</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">IMC</span>
                        <span class="detail-value" style="font-weight: 800; color: #27ae60;"><?= $participant->imc ?></span>
                    </div>
                </div>

                <div style="margin-top: 3rem;">
                    <a href="index.php?action=logout" class="btn-primary-green" style="background: #e74c3c;">Se déconnecter</a>
                </div>
            </div>
        <?php endif; ?>

        <div class="glass-card">
            <div class="main-grid">
                <div class="form-side">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                        <h2 style="margin:0;"><i class="fas fa-user-plus"></i> Inscription Participant</h2>
                        <button type="button" onclick="resetForm()" style="background:#95a5a6; color:white; border:none; padding:0.5rem 1rem; border-radius:0.8rem; cursor:pointer; font-weight:700; font-size:0.8rem;">
                            <i class="fas fa-sync-alt"></i> RÉINITIALISER
                        </button>
                    </div>
                    <form action="index.php?action=save_inscription" method="post" class="register-form" id="mainRegisterForm">
                        <div class="form-row">
                            <input type="hidden" id="participantId" name="id" value="0">
                            <div class="form-field">
                                <label for="evenement_id">Événement Choisi</label>
                                <select name="evenement_id" id="evenement_id">
                                    <option value="">-- Sélectionnez un événement --</option>
                                    <?php 
                                    $selectedEventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
                                    foreach ($evenements as $ev): 
                                    ?>
                                        <option value="<?= $ev->id ?>" <?= $ev->id === $selectedEventId ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($ev->titre) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="error-msg">Veuillez choisir un événement.</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-field">
                                <label for="categorie_preferee">Catégorie Préférée</label>
                                <select name="categorie_preferee" id="categorie_preferee">
                                    <option value="">-- Votre préférence --</option>
                                    <option value="Cuisine">Ateliers Cuisine</option>
                                    <option value="Nutrition">Nutrition & Santé</option>
                                    <option value="Sport">Sport & Vitalité</option>
                                    <option value="Bien-être">Bien-être</option>
                                </select>
                                <span class="error-msg">Sélectionnez une préférence.</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-field">
                                <label for="nom">Nom</label>
                                <input type="text" id="nom" name="nom" placeholder="Dupont">
                                <span class="error-msg">Le nom est requis (min 2 car.).</span>
                            </div>
                            <div class="form-field">
                                <label for="prenom">Prénom</label>
                                <input type="text" id="prenom" name="prenom" placeholder="Jean">
                                <span class="error-msg">Le prénom est requis (min 2 car.).</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-field">
                                <label for="date_naissance">Date de Naissance</label>
                                <input type="date" id="date_naissance" name="date_naissance">
                                <span class="error-msg">Min 13 ans requis.</span>
                            </div>
                            <div class="form-field">
                                <label for="telephone">Téléphone</label>
                                <input type="tel" id="telephone" name="telephone" placeholder="06 12 34 56 78">
                                <span class="error-msg">8 chiffres requis.</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-field">
                                <label for="email">Email</label>
                                <input type="text" id="email" name="email" placeholder="jean.dupont@gmail.com">
                                <span class="error-msg">Email invalide.</span>
                            </div>
                            <div class="form-field">
                                <label for="mot_de_passe">Mot de Passe</label>
                                <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="••••••••">
                                <span class="error-msg">Min 6 caractères.</span>
                            </div>
                        </div>

                        <div class="form-field">
                            <label for="lieu">Lieu de Résidence</label>
                            <input type="text" id="lieu" name="lieu" placeholder="Ex: Paris, Lyon...">
                            <span class="error-msg">Le lieu est requis.</span>
                        </div>

                        <div class="form-row">
                            <div class="form-field">
                                <label for="poids">Poids (kg)</label>
                                <input type="text" id="poids" name="poids" placeholder="70" oninput="calculateIMC()">
                                <span class="error-msg">Poids invalide (20-300 kg).</span>
                            </div>
                            <div class="form-field">
                                <label for="taille">Taille (cm)</label>
                                <input type="text" id="taille" name="taille" placeholder="175" oninput="calculateIMC()">
                                <span class="error-msg">Taille invalide (50-250 cm).</span>
                            </div>
                        </div>

                        <div class="imc-box">
                            <input type="hidden" name="imc" id="imc_input" value="0">
                            <span class="imc-lbl">Indice de Masse Corporelle (IMC)</span>
                            <span class="imc-val" id="imc_display">--</span>
                            <span id="imc_status" style="font-size: 0.9rem; font-weight: 700;"></span>
                        </div>

                        <div class="crud-actions" style="display: flex; gap: 0.8rem; margin-top: 1.5rem; align-items: stretch;">
                            <button type="submit" class="btn-primary-green" id="submitBtn" style="flex: 2; padding: 1rem; font-size: 1rem; border-radius: 1rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                                <i class="fas fa-save"></i> ENREGISTRER
                            </button>
                            <button type="button" id="btnUpdate" onclick="handleFormUpdate()" style="flex: 1; padding: 1rem; border-radius: 1rem; font-weight: 700; border: none; cursor: pointer; background: #f1c40f; color: white; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-edit"></i> MODIFIER
                            </button>
                            <button type="button" id="btnDelete" onclick="handleFormDelete()" style="flex: 1; padding: 1rem; border-radius: 1rem; font-weight: 700; border: none; cursor: pointer; background: #e74c3c; color: white; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-trash"></i> SUPPRIMER
                            </button>
                            <button type="button" id="btnShow" onclick="handleFormShow()" style="flex: 1; padding: 1rem; border-radius: 1rem; font-weight: 700; border: none; cursor: pointer; background: #3498db; color: white; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-eye"></i> AFFICHER
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <section class="participants-section" style="margin-top: 5rem;">
            <h2 class="section-title">Liste des Inscriptions</h2>
            <div class="table-container glass-card">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40px;">ID</th>
                            <th>Participant</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Date Naissance</th>
                            <th>Lieu</th>
                            <th>Poids (kg)</th>
                            <th>Taille (cm)</th>
                            <th>IMC</th>
                            <th>Événement</th>
                            <th>Catégorie</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="participantsList">
                        <!-- Les données seront injectées ici -->
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- Modal Détails -->
    <div id="participantModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalName">Détails Participant</h3>
                <span class="close-modal" onclick="closeModal()"><i class="fas fa-times"></i></span>
            </div>
            <div class="detail-grid" id="modalDetails">
                <!-- Détails injectés ici -->
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('mainRegisterForm');
        const participantsContainer = document.getElementById('participantsList');
        const participantIdInput = document.getElementById('participantId');
        const customIdInput = null; // Supprimé
        const submitBtn = document.getElementById('submitBtn');
        const btnUpdate = document.getElementById('btnUpdate');
        const btnDelete = document.getElementById('btnDelete');
        const btnShow = document.getElementById('btnShow');

        // Validation JS
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Reset errors
            document.querySelectorAll('.form-field').forEach(f => f.classList.remove('has-error'));

            // IDs
            if (document.getElementById('evenement_id').value === '') {
                showError('evenement_id');
                isValid = false;
            }
            if (document.getElementById('categorie_preferee').value === '') {
                showError('categorie_preferee');
                isValid = false;
            }

            // Names
            if (document.getElementById('nom').value.trim().length < 2) {
                showError('nom');
                isValid = false;
            }
            if (document.getElementById('prenom').value.trim().length < 2) {
                showError('prenom');
                isValid = false;
            }

            // Email
            const email = document.getElementById('email').value;
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showError('email');
                isValid = false;
            }

            // Password
            let isPasswordRequired = !document.getElementById('mot_de_passe').hasAttribute('data-not-required');
            if (isPasswordRequired && document.getElementById('mot_de_passe').value.length < 6) {
                showError('mot_de_passe');
                isValid = false;
            }

            // Date de naissance (Min 13 years old)
            const dobInput = document.getElementById('date_naissance').value;
            if (dobInput) {
                const dob = new Date(dobInput);
                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                const m = today.getMonth() - dob.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                    age--;
                }
                if (isNaN(dob.getTime()) || age < 13) {
                    showError('date_naissance');
                    isValid = false;
                }
            } else {
                showError('date_naissance');
                isValid = false;
            }

            // Phone
            const tel = document.getElementById('telephone').value.replace(/\s/g, '');
            if (!/^\d{8}$/.test(tel)) {
                showError('telephone');
                isValid = false;
            }

            // Lieu
            if (document.getElementById('lieu').value.trim() === '') {
                showError('lieu');
                isValid = false;
            }

            // Poids & Taille
            const poids = parseFloat(document.getElementById('poids').value);
            if (isNaN(poids) || poids < 20 || poids > 300) {
                showError('poids');
                isValid = false;
            }
            const taille = parseFloat(document.getElementById('taille').value);
            if (isNaN(taille) || taille < 50 || taille > 250) {
                showError('taille');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                alert("Veuillez corriger les erreurs dans le formulaire.");
            }
        });

        function showError(id) {
            document.getElementById(id).closest('.form-field').classList.add('has-error');
        }

        let participantsData = <?php echo json_encode($all_participants); ?>;

        // Initial state
        if (btnUpdate) btnUpdate.disabled = true;
        if (btnDelete) btnDelete.disabled = true;
        if (btnUpdate) btnUpdate.style.opacity = "0.5";
        if (btnDelete) btnDelete.style.opacity = "0.5";

        function scrollToParticipants() {
            const section = document.querySelector('.participants-section');
            if (section) {
                section.scrollIntoView({ behavior: 'smooth' });
            }
        }

        function renderParticipants() {
            participantsContainer.innerHTML = '';
            participantsData.forEach((p, idx) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${p.id}</td>
                    <td>
                        <div style="font-weight:700; color: #1f5e1a; cursor: pointer;" onclick="showParticipantDetails(${idx})">
                            ${p.prenom} ${p.nom}
                        </div>
                    </td>
                    <td>${p.email}</td>
                    <td>${p.telephone}</td>
                    <td>${p.date_naissance}</td>
                    <td>${p.lieu}</td>
                    <td>${p.poids}</td>
                    <td>${p.taille}</td>
                    <td style="font-weight:700;">${p.imc}</td>
                    <td>${p.evenement_id || "Non spécifié"}</td>
                    <td><span class="cat-card-workshop" style="padding:0.2rem 0.8rem; font-size:0.75rem;">${p.categorie_preferee}</span></td>
                    <td>
                        <div class="action-btns">
                            <button class="btn-mini-edit" onclick="editParticipant(${idx})" title="Modifier"><i class="fas fa-edit"></i></button>
                            <button class="btn-mini-delete" onclick="deleteParticipant(${idx})" title="Supprimer"><i class="fas fa-trash"></i></button>
                            <button class="btn-primary-green" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;" onclick="showParticipantDetails(${idx})" title="Afficher"><i class="fas fa-eye"></i></button>
                        </div>
                    </td>
                `;
                participantsContainer.appendChild(tr);
            });
        }

        window.showParticipantDetails = (idx) => {
            const p = participantsData[idx];
            const modal = document.getElementById('participantModal');
            const detailsContainer = document.getElementById('modalDetails');
            
            // Couleur IMC
            let imcColor = "#2ecc71";
            if (p.imc < 18.5) imcColor = "#3498db";
            else if (p.imc >= 25 && p.imc < 30) imcColor = "#f1c40f";
            else if (p.imc >= 30) imcColor = "#e74c3c";

            detailsContainer.innerHTML = `
                <div class="detail-item">
                    <span class="detail-label">ID Participant</span>
                    <span class="detail-value">#${p.id}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Prénom & Nom</span>
                    <span class="detail-value">${p.prenom} ${p.nom}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Date de Naissance</span>
                    <span class="detail-value">${p.date_naissance}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Email</span>
                    <span class="detail-value">${p.email}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Téléphone</span>
                    <span class="detail-value">${p.telephone}</span>
                </div>
                <div class="detail-item detail-full">
                    <span class="detail-label">Lieu de Résidence</span>
                    <span class="detail-value">${p.lieu}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Poids</span>
                    <span class="detail-value">${p.poids} kg</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Taille</span>
                    <span class="detail-value">${p.taille} cm</span>
                </div>
                <div class="detail-item detail-full" style="text-align: center; background: #f8fcf6; padding: 1.5rem; border-radius: 1.5rem;">
                    <span class="detail-label">Indice de Masse Corporelle</span>
                    <div style="margin-top: 0.5rem;">
                        <span class="imc-badge" style="background: ${imcColor}">${p.imc}</span>
                    </div>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Événement ID</span>
                    <span class="detail-value">#${p.evenement_id || "N/A"}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Catégorie</span>
                    <span class="detail-value">${p.categorie_preferee}</span>
                </div>
            `;
            
            modal.style.display = 'flex';
        };

        window.closeModal = () => {
            document.getElementById('participantModal').style.display = 'none';
        };

        // Fermer la modale si on clique en dehors
        window.onclick = (event) => {
            const modal = document.getElementById('participantModal');
            if (event.target == modal) {
                closeModal();
            }
        };

        window.deleteParticipant = (idx) => {
            const p = participantsData[idx];
            if (confirm("Supprimer cette inscription ?")) {
                window.location.href = `index.php?action=delete_participant&id=${p.id}`;
            }
        };

        window.editParticipant = (idx) => {
            const p = participantsData[idx];
            
            // Remplir le formulaire
            form.evenement_id.value = p.evenement_id;
            form.categorie_preferee.value = p.categorie_preferee;
            form.nom.value = p.nom;
            form.prenom.value = p.prenom;
            form.date_naissance.value = p.date_naissance;
            form.telephone.value = p.telephone;
            form.email.value = p.email;
            form.lieu.value = p.lieu;
            form.poids.value = p.poids;
            form.taille.value = p.taille;
            
            // Calculer IMC
            calculateIMC();
            
            // Préparer pour la mise à jour
            participantIdInput.value = p.id;
            submitBtn.innerHTML = "<i class='fas fa-plus'></i> ENREGISTRER (NOUVEAU)";
            submitBtn.style.background = "#27ae60";
            submitBtn.disabled = false;
            submitBtn.style.opacity = "1";
            
            // Le mot de passe n'est plus requis pour la modification
            form.mot_de_passe.setAttribute('data-not-required', 'true');
            form.mot_de_passe.placeholder = "(Inchangé si vide)";
            
            // Activer les boutons CRUD
            if (btnUpdate) { 
                btnUpdate.disabled = false; 
                btnUpdate.style.opacity = "1";
                btnUpdate.innerHTML = "<i class='fas fa-sync'></i> CONFIRMER MODIF";
            }
            if (btnDelete) { btnDelete.disabled = false; btnDelete.style.opacity = "1"; }
            
            // Scroll to form
            window.scrollTo({ top: form.offsetTop - 150, behavior: 'smooth' });
        };

        // Si on clique sur le bouton principal (ENREGISTRER), on remet l'ID à 0 pour créer un nouveau
        form.addEventListener('submit', (e) => {
            // Validation personnalisée (remplace HTML5 required)
            const fields = [
                { id: 'nom', label: 'Nom' },
                { id: 'prenom', label: 'Prénom' },
                { id: 'email', label: 'Email' },
                { id: 'telephone', label: 'Téléphone' }
            ];

            let errors = [];
            fields.forEach(f => {
                const val = document.getElementById(f.id).value.trim();
                if (!val) errors.push(f.label);
            });

            if (errors.length > 0) {
                e.preventDefault();
                alert("Veuillez remplir les champs suivants : " + errors.join(", "));
                return;
            }

            if (e.submitter && e.submitter.id === "submitBtn") {
                participantIdInput.value = "0";
            }
        });

        window.handleFormUpdate = () => {
            const id = parseInt(participantIdInput.value);
            if (id === 0) return;
            
            form.action = "index.php?action=update_participant";
            form.submit();
        };

        window.handleFormDelete = () => {
            const id = parseInt(participantIdInput.value);
            if (id === 0) return;
            if (confirm("Voulez-vous vraiment supprimer ce participant ?")) {
                window.location.href = `index.php?action=delete_participant&id=${id}`;
            }
        };

        window.handleFormShow = () => {
            const id = parseInt(participantIdInput.value);
            if (id === 0) {
                scrollToParticipants();
                return;
            }
            const idx = participantsData.findIndex(p => p.id == id);
            if (idx !== -1) showParticipantDetails(idx);
        };

        window.resetForm = () => {
            form.reset();
            participantIdInput.value = "0";
            submitBtn.innerHTML = "<i class='fas fa-save'></i> ENREGISTRER";
            submitBtn.style.background = "#2ecc71";
            submitBtn.disabled = false;
            submitBtn.style.opacity = "1";
            
            form.mot_de_passe.removeAttribute('data-not-required');
            form.mot_de_passe.placeholder = "••••••••";
            
            if (btnUpdate) { btnUpdate.disabled = true; btnUpdate.style.opacity = "0.5"; btnUpdate.innerHTML = "<i class='fas fa-edit'></i> MODIFIER"; }
            if (btnDelete) { btnDelete.disabled = true; btnDelete.style.opacity = "0.5"; }
            
            document.getElementById('imc_display').textContent = "--";
            document.getElementById('imc_status').textContent = "";
            document.getElementById('imc_input').value = "0";
        };

        function calculateIMC() {
            const poids = parseFloat(document.getElementById('poids').value);
            const taille = parseFloat(document.getElementById('taille').value) / 100;
            const imcDisplay = document.getElementById('imc_display');
            const imcInput = document.getElementById('imc_input');
            const imcStatus = document.getElementById('imc_status');

            if (poids > 0 && taille > 0) {
                const imc = (poids / (taille * taille)).toFixed(1);
                imcDisplay.textContent = imc;
                imcInput.value = imc;

                let status = "";
                let color = "";
                if (imc < 18.5) { status = "Insuffisance pondérale"; color = "#3498db"; }
                else if (imc < 25) { status = "Poids normal"; color = "#2ecc71"; }
                else if (imc < 30) { status = "Surpoids"; color = "#f1c40f"; }
                else { status = "Obésité"; color = "#e74c3c"; }
                
                imcStatus.textContent = status;
                imcStatus.style.color = color;
            } else {
                imcDisplay.textContent = "--";
                imcStatus.textContent = "";
            }
        }

        renderParticipants();

        // Auto-show after update
        const urlParams = new URLSearchParams(window.location.search);
        const lastId = urlParams.get('id');
        if (lastId && (urlParams.get('success') === 'updated' || urlParams.get('success') === 'inscribed')) {
            const idx = participantsData.findIndex(p => p.id == lastId);
            if (idx !== -1) {
                setTimeout(() => showParticipantDetails(idx), 500);
            }
        }
    </script>

    <footer>
        <p>🍃 NutriVert — Mangez bien, Vivez mieux | &copy; 2026</p>
    </footer>
</body>
</html>
