<?php
declare(strict_types=1);
/**
 * Vue Offres - Offer Builder, Suivi Fiche & Login Suivi.
 * @var Evenement[] $evenements
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Événements & Suivi | NutriVert</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(145deg, #f0f9ea 0%, #e2f3db 50%, #d4ecce 100%); 
            background-attachment: fixed; 
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

        /* Main Container */
        .container { max-width: 1400px; margin: 0 auto; padding: 2rem; }

        /* Builder Layout */
        .builder-grid { display: flex; justify-content: center; margin-bottom: 5rem; }
        
        .form-box { background: rgba(255, 255, 250, 0.8); padding: 2.5rem; border-radius: 2.5rem; border: 1px solid rgba(100, 180, 70, 0.3); width: 100%; max-width: 800px; }
        .form-box h2 { font-size: 1.5rem; margin-bottom: 1.5rem; color: #1f5e1a; display: flex; align-items: center; gap: 10px; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; font-weight: 700; margin-bottom: 0.4rem; color: #2c4d24; font-size: 0.8rem; text-transform: uppercase; }
        .form-control { width: 100%; padding: 0.8rem 1rem; border-radius: 1rem; border: 1px solid rgba(100, 180, 70, 0.2); background: rgba(255, 255, 255, 0.9); outline: none; transition: all 0.3s; font-family: inherit; }
        .form-control:focus { border-color: #4cae4c; box-shadow: 0 0 0 4px rgba(76, 174, 76, 0.1); }

        /* Offers Grid */
        .section-title { font-size: 2.2rem; font-weight: 700; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.7rem; background: linear-gradient(120deg, #1f631a, #58b83a); -webkit-background-clip: text; background-clip: text; color: transparent; border-left: 7px solid #7ac85a; padding-left: 1rem; }
        .offers-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 2rem; margin-bottom: 6rem; }
        .offer-card { display: flex; flex-direction: column; gap: 0rem; overflow: hidden; height: 100%; }
        .offer-img-wrapper { height: 200px; width: 100%; position: relative; overflow: hidden; }
        .offer-img-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
        .offer-card:hover .offer-img-wrapper img { transform: scale(1.1); }
        .offer-content { padding: 1.5rem; flex-grow: 1; display: flex; flex-direction: column; gap: 1rem; }
        .offer-price { font-size: 2rem; font-weight: 800; color: #1f5e1a; margin-top: auto; }

        .crud-btns { display: flex; gap: 0.5rem; margin-top: 1rem; }
        .btn-edit { background: #f1c40f; color: #fff; border: none; padding: 0.6rem; border-radius: 0.8rem; cursor: pointer; flex: 1; font-weight: 700; transition: 0.2s; }
        .btn-delete { background: #e74c3c; color: #fff; border: none; padding: 0.6rem; border-radius: 0.8rem; cursor: pointer; flex: 1; font-weight: 700; transition: 0.2s; }
        .btn-edit:hover { background: #d4ac0d; }
        .btn-delete:hover { background: #c0392b; }

        /* Subscription Section */
        .subs-section { margin-bottom: 5rem; }
        .subs-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 2rem; }
        .subs-card { 
            padding: 3rem 2rem; 
            text-align: center; 
            position: relative; 
            border: 2px solid transparent;
            transition: all 0.4s ease;
        }
        .subs-card.popular { 
            border-color: #4cae4c; 
            transform: scale(1.05); 
            background: rgba(240, 255, 235, 0.8);
            z-index: 2;
        }
        .subs-badge { 
            position: absolute; 
            top: 1.5rem; 
            right: -1rem; 
            background: #e67e22; 
            color: white; 
            padding: 0.4rem 1.5rem; 
            border-radius: 0.5rem; 
            font-size: 0.8rem; 
            font-weight: 800; 
            transform: rotate(15deg);
        }
        .subs-name { font-size: 1.8rem; font-weight: 800; color: #1f5e1a; margin-bottom: 0.5rem; }
        .subs-price { font-size: 3rem; font-weight: 800; color: #2c4d24; margin-bottom: 1.5rem; }
        .subs-price span { font-size: 1rem; opacity: 0.6; }
        .subs-features { list-style: none; margin-bottom: 2rem; text-align: left; }
        .subs-features li { margin-bottom: 0.8rem; display: flex; align-items: center; gap: 10px; color: #2a5522; font-weight: 600; }
        .subs-features li i { color: #4cae4c; }

        /* Image Selector in Form */
        .image-selector { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 0.5rem; }
        .img-option { 
            height: 60px; 
            border-radius: 10px; 
            cursor: pointer; 
            border: 3px solid transparent; 
            overflow: hidden; 
            transition: 0.2s; 
        }
        .img-option.selected { border-color: #4cae4c; transform: scale(0.95); }
        .img-option img { width: 100%; height: 100%; object-fit: cover; }

        /* Login Suivi Section */
        .login-suivi-section { background: rgba(30, 55, 25, 0.9); border-radius: 3rem; padding: 4rem 2rem; text-align: center; color: #e0f0cf; margin-top: 4rem; }
        .login-form { max-width: 500px; margin: 2rem auto 0; display: flex; flex-direction: column; gap: 1rem; }
        .login-input { padding: 1rem 1.5rem; border-radius: 3rem; border: none; background: rgba(255,255,255,0.1); color: #fff; font-size: 1rem; outline: none; }
        .login-input::placeholder { color: rgba(255,255,255,0.4); }

        footer { text-align: center; padding: 2rem; background: rgba(30, 55, 25, 0.9); color: #e0f0cf; margin-top: 4rem; font-size: 0.9rem; }

        @media (max-width: 1000px) { .builder-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header>
        <a href="index.php" class="logo-area">
            <div style="height: 40px; width: 40px; background: #4cae4c; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white;"><i class="fas fa-leaf"></i></div>
            <span class="logo-text">NutriVert</span>
        </a>
        <nav>
            <a href="index.php?sub=events" class="active">Événements & Suivi</a>
            <a href="index.php?sub=categories">Catégories</a>
            <a href="index.php?sub=participants">Participants</a>
            <a href="index.php?sub=participants" class="btn-primary-green">S'inscrire</a>
        </nav>
    </header>

    <div class="container">
        <!-- Subscription Offers Section -->
        <section class="subs-section">
            <h2 class="section-title"><i class="fas fa-crown"></i> Choisissez votre Abonnement</h2>
            <div class="subs-grid">
                <!-- Plan 1 -->
                <div class="glass-card subs-card">
                    <div class="subs-name">Essentiel</div>
                    <div class="subs-price">29<span>€/mois</span></div>
                    <ul class="subs-features">
                        <li><i class="fas fa-check-circle"></i> Accès à 2 ateliers/mois</li>
                        <li><i class="fas fa-check-circle"></i> Fiche de suivi standard</li>
                        <li><i class="fas fa-check-circle"></i> Support par email</li>
                    </ul>
                    <button class="btn-primary-green" style="width: 100%;">S'abonner</button>
                </div>
                <!-- Plan 2 -->
                <div class="glass-card subs-card popular">
                    <div class="subs-badge">RECOMMANDÉ</div>
                    <div class="subs-name">Premium</div>
                    <div class="subs-price">49<span>€/mois</span></div>
                    <ul class="subs-features">
                        <li><i class="fas fa-check-circle"></i> Ateliers illimités</li>
                        <li><i class="fas fa-check-circle"></i> Coaching personnalisé</li>
                        <li><i class="fas fa-check-circle"></i> Accès au builder d'offres</li>
                        <li><i class="fas fa-check-circle"></i> Support Prioritaire 24/7</li>
                    </ul>
                    <button class="btn-primary-green" style="width: 100%; background: #2f8a2b;">S'abonner maintenant</button>
                </div>
                <!-- Plan 3 -->
                <div class="glass-card subs-card">
                    <div class="subs-name">Elite</div>
                    <div class="subs-price">89<span>€/mois</span></div>
                    <ul class="subs-features">
                        <li><i class="fas fa-check-circle"></i> Tout du plan Premium</li>
                        <li><i class="fas fa-check-circle"></i> Bilan nutritionnel complet</li>
                        <li><i class="fas fa-check-circle"></i> Accès VIP événements</li>
                    </ul>
                    <button class="btn-primary-green" style="width: 100%;">S'abonner</button>
                </div>
            </div>
        </section>

        <!-- Builder & Fiche side by side -->
        <div class="builder-grid">
            <div class="form-box glass-card" <?php if ($participant) echo 'style="opacity: 0.5; pointer-events: none;"'; ?> style="grid-column: span 2; max-width: 800px; margin: 0 auto; width: 100%;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                    <h2 style="margin:0;"><i class="fas fa-plus-circle"></i> <span id="formTitle">Créer un Événement</span></h2>
                    <button type="button" onclick="resetEventForm()" style="background:#95a5a6; color:white; border:none; padding:0.5rem 1rem; border-radius:0.8rem; cursor:pointer; font-weight:700; font-size:0.8rem;">
                        <i class="fas fa-sync-alt"></i> RÉINITIALISER
                    </button>
                </div>
                <?php if ($participant): ?>
                    <p style="font-size: 0.85rem; color: #7f8c8d; margin-bottom: 1rem;"><i class="fas fa-info-circle"></i> Le mode création est désactivé car vous êtes connecté à votre suivi.</p>
                <?php endif; ?>
                <form id="offerForm" action="index.php?action=save_event" method="post">
                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.2rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="event_custom_id">ID Événement (Custom)</label>
                            <input type="text" id="event_custom_id" name="event_custom_id" class="form-control" placeholder="Ex: EV-2026-01">
                            <input type="hidden" id="eventId" name="id" value="0">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="offerTitle">Titre de l'Événement</label>
                            <input type="text" id="offerTitle" name="titre" class="form-control" placeholder="Ex: Coaching Premium...">
                        </div>
                    </div>
                    
                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.2rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="offerCat">Catégorie</label>
                            <select id="offerCat" name="categorie" class="form-control">
                                <option value="Cuisine">Cuisine</option>
                                <option value="Nutrition">Nutrition</option>
                                <option value="Sport">Sport</option>
                                <option value="Bien-être">Bien-être</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="offerCatId">ID Catégorie</label>
                            <input type="text" id="offerCatId" name="categorie_id" class="form-control" placeholder="Ex: CAT-001">
                        </div>
                    </div>

                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.2rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="offerDate">Date de l'Événement</label>
                            <input type="datetime-local" id="offerDate" name="date_evenement" class="form-control">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="offerLieu">Lieu</label>
                            <input type="text" id="offerLieu" name="lieu" class="form-control" placeholder="Ex: Paris">
                        </div>
                    </div>

                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.2rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="offerPrice">Prix Mensuel (DT)</label>
                            <input type="number" id="offerPrice" name="prix" class="form-control" placeholder="Ex: 49">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="offerCap">Capacité Max</label>
                            <input type="number" id="offerCap" name="capacite" class="form-control" placeholder="Ex: 50">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="offerStatut">Statut</label>
                        <select id="offerStatut" name="statut" class="form-control">
                            <option value="Actif">Actif</option>
                            <option value="En attente">En attente</option>
                            <option value="Terminé">Terminé</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="offerDesc">Description de l'Événement</label>
                        <textarea id="offerDesc" name="description" class="form-control" style="min-height: 80px;" placeholder="Détails du programme..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Image de l'Événement (Choix)</label>
                        <div class="image-selector" id="imgSelector">
                            <div class="img-option selected" data-img="https://images.unsplash.com/photo-1490818387583-1baba5e638af?auto=format&fit=crop&w=800">
                                <img src="https://images.unsplash.com/photo-1490818387583-1baba5e638af?auto=format&fit=crop&w=200" alt="Option 1">
                            </div>
                            <div class="img-option" data-img="https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=800">
                                <img src="https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=200" alt="Option 2">
                            </div>
                            <div class="img-option" data-img="https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=800">
                                <img src="https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=200" alt="Option 3">
                            </div>
                            <div class="img-option" data-img="https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=800">
                                <img src="https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=200" alt="Option 4">
                            </div>
                        </div>
                        <input type="hidden" id="offerImg" name="image_url" value="https://images.unsplash.com/photo-1490818387583-1baba5e638af?auto=format&fit=crop&w=800">
                    </div>
                    
                    <div class="crud-actions" style="display: flex; gap: 0.8rem; margin-top: 1.5rem; align-items: stretch;">
                        <button type="submit" class="btn-primary-green" id="submitBtn" style="flex: 2; padding: 1rem; font-size: 1rem; border-radius: 1rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                            <i class="fas fa-save"></i> ENREGISTRER
                        </button>
                        <button type="button" id="btnUpdate" onclick="handleEventUpdate()" style="flex: 1; padding: 1rem; border-radius: 1rem; font-weight: 700; border: none; cursor: pointer; background: #f1c40f; color: white; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-edit"></i> MODIFIER
                        </button>
                        <button type="button" id="btnDelete" onclick="handleEventDelete()" style="flex: 1; padding: 1rem; border-radius: 1rem; font-weight: 700; border: none; cursor: pointer; background: #e74c3c; color: white; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-trash"></i> SUPPRIMER
                        </button>
                        <button type="button" id="btnShow" onclick="scrollToEvents()" style="flex: 1; padding: 1rem; border-radius: 1rem; font-weight: 700; border: none; cursor: pointer; background: #3498db; color: white; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-eye"></i> AFFICHER
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Offres en bas -->
        <h2 class="section-title"><i class="fas fa-calendar-alt"></i> Nos Événements Actuels</h2>
        <div class="offers-grid">
            <?php 
            $sampleImages = [
                "https://images.unsplash.com/photo-1490818387583-1baba5e638af?auto=format&fit=crop&w=800",
                "https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=800",
                "https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&w=800",
                "https://images.unsplash.com/photo-1512621776951-a57141f2eefd?auto=format&fit=crop&w=800"
            ];
            $idx = 0;
            foreach ($evenements as $ev): 
                $img = $sampleImages[$idx % 4];
                $idx++;
            ?>
                <div class="glass-card offer-card">
                    <div class="offer-img-wrapper">
                        <img src="<?= $img ?>" alt="Événement">
                    </div>
                    <div class="offer-content">
                        <h3 style="font-size: 1.5rem; color: #1f5e1a;"><?= htmlspecialchars($ev->titre) ?></h3>
                        <p style="color: #2c4d24; font-size: 0.95rem; line-height: 1.6;"><?= htmlspecialchars($ev->description) ?></p>
                        <div class="offer-price"><?= $ev->prix_participation ?? '49' ?><span> DT/mois</span></div>
                        <div class="crud-btns">
                            <button class="btn-edit" onclick="editOffer(<?= $ev->id ?>)"><i class="fas fa-edit"></i></button>
                            <button class="btn-delete" onclick="deleteOffer(<?= $ev->id ?>)"><i class="fas fa-trash"></i></button>
                            <button class="btn-show" onclick="showEventDetails(<?= $ev->id ?>)" style="background: #3498db; color: white; border: none; padding: 0.5rem; border-radius: 8px; cursor: pointer; margin-left: 5px;"><i class="fas fa-eye"></i></button>
                        </div>
                        <a href="index.php?sub=participants&event_id=<?= $ev->id ?>" class="btn-primary-green" style="padding: 0.8rem; text-align: center; margin-top: 0.5rem;">S'inscrire</a>
                    </div>
                </div>
<?php endforeach; ?>
        </div>

        <!-- Login Suivi -->
        <?php if (!$participant): ?>
            <section class="login-suivi-section">
                <h2 style="font-size: 2rem; margin-bottom: 1rem;">Accéder à Mon Suivi</h2>
                <p style="opacity: 0.8;">Entrez vos identifiants pour consulter votre fiche de progression</p>
                <?php if (isset($_GET['error']) && $_GET['error'] === 'auth_failed'): ?>
                    <p style="color: #ff7675; font-weight: 700; margin-top: 1rem;">Email ou mot de passe incorrect.</p>
                <?php endif; ?>
                <form class="login-form" action="index.php?action=login_suivi" method="post">
                    <input type="email" name="email" class="login-input" placeholder="Votre Email Gmail..." required>
                    <input type="password" name="password" class="login-input" placeholder="Votre Mot de Passe..." required>
                    <button type="submit" class="btn-primary-green" style="padding: 1.2rem; margin-top: 1rem;">SE CONNECTER</button>
                </form>
            </section>
        <?php endif; ?>
    </div>

    <footer>
        <p>🍃 NutriVert — Mangez bien, Vivez mieux | &copy; 2026</p>
    </footer>

    <!-- Modal Détails Événement -->
    <div id="eventModal" class="modal" style="display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); align-items: center; justify-content: center;">
        <div class="modal-content" style="background: white; padding: 2.5rem; border-radius: 2rem; width: 90%; max-width: 600px; position: relative;">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 2px solid #f0f9ea; padding-bottom: 1rem;">
                <h3 id="modalEventTitle" style="font-size: 1.8rem; color: #1f5e1a; font-weight: 800;">Détails Événement</h3>
                <span class="close-modal" onclick="closeEventModal()" style="font-size: 1.5rem; color: #95a5a6; cursor: pointer;"><i class="fas fa-times"></i></span>
            </div>
            <div id="modalEventDetails" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <!-- Détails injectés ici -->
            </div>
        </div>
    </div>

    <script>
        const offerForm = document.getElementById('offerForm');
        const offerTitle = document.getElementById('offerTitle');
        const offerCat = document.getElementById('offerCat');
        const offerCatId = document.getElementById('offerCatId');
        const offerDate = document.getElementById('offerDate');
        const offerLieu = document.getElementById('offerLieu');
        const offerPrice = document.getElementById('offerPrice');
        const offerCap = document.getElementById('offerCap');
        const offerStatut = document.getElementById('offerStatut');
        const offerDesc = document.getElementById('offerDesc');
        const offerImg = document.getElementById('offerImg');
        const eventIdInput = document.getElementById('eventId');
        const customEventIdInput = document.getElementById('event_custom_id');
        const formTitle = document.getElementById('formTitle');
        const submitBtn = document.getElementById('submitBtn');
        const btnUpdate = document.getElementById('btnUpdate');
        const btnDelete = document.getElementById('btnDelete');
        const btnShow = document.getElementById('btnShow');

        const imgOptions = document.querySelectorAll('.img-option');

        // Initial state
        if (btnUpdate) btnUpdate.disabled = true;
        if (btnDelete) btnDelete.disabled = true;
        if (btnUpdate) btnUpdate.style.opacity = "0.5";
        if (btnDelete) btnDelete.style.opacity = "0.5";

        // Image selector logic
        imgOptions.forEach(opt => {
            opt.addEventListener('click', () => {
                imgOptions.forEach(o => o.classList.remove('selected'));
                opt.classList.add('selected');
                const selectedImg = opt.getAttribute('data-img');
                offerImg.value = selectedImg;
            });
        });

        // LocalStorage logic replaced by PHP/DB logic
        let localOffers = <?php echo json_encode($evenements); ?>;

        const offersContainer = document.querySelector('.offers-grid');
        function renderLocalOffers() {
            // Only dynamic offers from DB are rendered by PHP above
        }

        offerForm.addEventListener('submit', (e) => {
            // form submits to index.php?action=save_event
        });

        window.deleteOffer = (id) => {
            if (confirm("Voulez-vous vraiment supprimer cet événement ?")) {
                window.location.href = `index.php?action=delete_event&id=${id}`;
            }
        };

        window.editOffer = (id) => {
            const off = localOffers.find(o => o.id == id);
            if (!off) return;

            offerTitle.value = off.titre;
            offerDesc.value = off.description;
            offerDate.value = off.date_evenement.replace(' ', 'T');
            offerLieu.value = off.lieu;
            offerImg.value = off.image_url;
            eventIdInput.value = off.id;
            customEventIdInput.value = off.event_custom_id;
            
            formTitle.textContent = "MODIFICATION EN COURS";
            
            // Activer les boutons CRUD
            if (btnUpdate) { btnUpdate.disabled = false; btnUpdate.style.opacity = "1"; }
            if (btnDelete) { btnDelete.disabled = false; btnDelete.style.opacity = "1"; }
            
            // Laisser le bouton principal pour permettre une NOUVELLE création
            submitBtn.innerHTML = "<i class='fas fa-plus'></i> ENREGISTRER (NOUVEAU)";
            submitBtn.disabled = false;
            submitBtn.style.opacity = "1";
            submitBtn.style.background = "#27ae60";

            window.scrollTo({ top: offerForm.offsetTop - 150, behavior: 'smooth' });
        };

        // Si on clique sur le bouton principal (ENREGISTRER), on remet l'ID à 0 pour créer un nouveau
        offerForm.addEventListener('submit', (e) => {
            // Validation personnalisée (remplace HTML5 required)
            const fields = [
                { id: 'offerTitle', label: 'Titre' },
                { id: 'offerDate', label: 'Date' },
                { id: 'offerLieu', label: 'Lieu' }
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

            // Si on clique sur le bouton submit (ENREGISTRER), on s'assure que c'est une nouvelle création
            // Sauf si on a appelé explicitement handleEventUpdate()
            if (e.submitter && e.submitter.id === "submitBtn") {
                eventIdInput.value = "0";
            }
        });

        window.resetEventForm = () => {
            offerForm.reset();
            eventIdInput.value = "0";
            formTitle.textContent = "Créer un Événement";
            submitBtn.innerHTML = "<i class='fas fa-save'></i> ENREGISTRER";
            submitBtn.style.background = "#2ecc71";
            submitBtn.disabled = false;
            submitBtn.style.opacity = "1";
            
            if (btnUpdate) { btnUpdate.disabled = true; btnUpdate.style.opacity = "0.5"; }
            if (btnDelete) { btnDelete.disabled = true; btnDelete.style.opacity = "0.5"; }
            
            imgOptions.forEach(o => o.classList.remove('selected'));
            imgOptions[0].classList.add('selected');
            offerImg.value = imgOptions[0].getAttribute('data-img');
        };

        window.handleEventUpdate = () => {
            const id = parseInt(eventIdInput.value);
            if (id === 0) return;
            offerForm.submit();
        };

        window.handleEventDelete = () => {
            const id = parseInt(eventIdInput.value);
            if (id === 0) return;
            window.deleteOffer(id);
        };

        window.showEventDetails = (id) => {
            const ev = localOffers.find(o => o.id == id);
            if (!ev) return;

            const modal = document.getElementById('eventModal');
            const details = document.getElementById('modalEventDetails');
            
            details.innerHTML = `
                <div style="display:flex; flex-direction:column; gap:5px;">
                    <span style="font-size:0.8rem; font-weight:800; color:#7f8c8d; text-transform:uppercase;">ID Événement</span>
                    <span style="font-size:1.1rem; font-weight:700; color:#2c3e50;">#${ev.id}</span>
                </div>
                <div style="display:flex; flex-direction:column; gap:5px;">
                    <span style="font-size:0.8rem; font-weight:800; color:#7f8c8d; text-transform:uppercase;">Titre</span>
                    <span style="font-size:1.1rem; font-weight:700; color:#2c3e50;">${ev.titre}</span>
                </div>
                <div style="display:flex; flex-direction:column; gap:5px;">
                    <span style="font-size:0.8rem; font-weight:800; color:#7f8c8d; text-transform:uppercase;">Catégorie</span>
                    <span style="font-size:1.1rem; font-weight:700; color:#2c3e50;">${ev.categorie || "N/A"}</span>
                </div>
                <div style="display:flex; flex-direction:column; gap:5px;">
                    <span style="font-size:0.8rem; font-weight:800; color:#7f8c8d; text-transform:uppercase;">ID Catégorie</span>
                    <span style="font-size:1.1rem; font-weight:700; color:#2c3e50;">${ev.categorie_id || "N/A"}</span>
                </div>
                <div style="display:flex; flex-direction:column; gap:5px;">
                    <span style="font-size:0.8rem; font-weight:800; color:#7f8c8d; text-transform:uppercase;">Date</span>
                    <span style="font-size:1.1rem; font-weight:700; color:#2c3e50;">${ev.date_evenement}</span>
                </div>
                <div style="display:flex; flex-direction:column; gap:5px;">
                    <span style="font-size:0.8rem; font-weight:800; color:#7f8c8d; text-transform:uppercase;">Lieu</span>
                    <span style="font-size:1.1rem; font-weight:700; color:#2c3e50;">${ev.lieu}</span>
                </div>
                <div style="display:flex; flex-direction:column; gap:5px;">
                    <span style="font-size:0.8rem; font-weight:800; color:#7f8c8d; text-transform:uppercase;">Prix</span>
                    <span style="font-size:1.1rem; font-weight:700; color:#2c3e50;">${ev.prix_participation} DT</span>
                </div>
                <div style="display:flex; flex-direction:column; gap:5px;">
                    <span style="font-size:0.8rem; font-weight:800; color:#7f8c8d; text-transform:uppercase;">Capacité</span>
                    <span style="font-size:1.1rem; font-weight:700; color:#2c3e50;">${ev.capacite_max} personnes</span>
                </div>
                <div style="display:flex; flex-direction:column; gap:5px;">
                    <span style="font-size:0.8rem; font-weight:800; color:#7f8c8d; text-transform:uppercase;">Statut</span>
                    <span style="font-size:1.1rem; font-weight:700; color:#27ae60;">${ev.statut}</span>
                </div>
                <div style="grid-column:span 2; display:flex; flex-direction:column; gap:5px; background:#f8fcf6; padding:1rem; border-radius:1rem;">
                    <span style="font-size:0.8rem; font-weight:800; color:#7f8c8d; text-transform:uppercase;">Description</span>
                    <span style="font-size:1rem; line-height:1.6; color:#2c3e50;">${ev.description}</span>
                </div>
            `;

            modal.style.display = 'flex';
        };

        window.closeEventModal = () => {
            document.getElementById('eventModal').style.display = 'none';
        };

        window.onclick = (event) => {
            const modal = document.getElementById('eventModal');
            if (event.target == modal) {
                closeEventModal();
            }
        };

        window.scrollToEvents = () => {
            const section = document.querySelector('.offers-grid');
            if (section) {
                section.scrollIntoView({ behavior: 'smooth' });
            }
        };

        // Auto-show after update
        const urlParams = new URLSearchParams(window.location.search);
        const lastId = urlParams.get('id');
        if (lastId && urlParams.get('success') === 'saved') {
            setTimeout(() => showEventDetails(lastId), 500);
        }

        renderLocalOffers();
    </script>
</body>
</html>
