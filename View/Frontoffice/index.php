<?php
$loggedIn = isLoggedIn();
$userName = $loggedIn ? $_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom'] : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>NutriVert | Mangez intelligemment, vivez durablement</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(145deg, #f0f9ea 0%, #e2f3db 50%, #d4ecce 100%); background-attachment: fixed; color: #1a3a1a; scroll-behavior: smooth; }
        .glass-card { background: rgba(255, 255, 250, 0.65); backdrop-filter: blur(14px); border-radius: 2rem; border: 1px solid rgba(100, 180, 70, 0.35); box-shadow: 0 15px 35px -12px rgba(0, 0, 0, 0.08); transition: all 0.35s cubic-bezier(0.2, 0.9, 0.4, 1.1); }
        .glass-card:hover { transform: translateY(-8px); box-shadow: 0 25px 35px -12px rgba(60, 110, 30, 0.2); background: rgba(255, 255, 250, 0.85); border-color: #7ec850; }
        header { background: rgba(255, 255, 245, 0.92); backdrop-filter: blur(20px); padding: 0.9rem 2.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; position: sticky; top: 0; z-index: 1000; border-bottom: 1px solid rgba(90, 160, 60, 0.3); box-shadow: 0 4px 18px rgba(0, 0, 0, 0.02); }
        .logo-area { display: flex; align-items: center; gap: 1rem; background: rgba(230, 255, 220, 0.7); padding: 0.4rem 1.2rem 0.4rem 1rem; border-radius: 3rem; transition: all 0.3s ease; border: 1px solid rgba(100, 180, 70, 0.5); box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
        .logo-area:hover { background: rgba(255, 255, 240, 0.95); transform: scale(1.01); }
        .logo-image { height: 48px; width: auto; max-width: 120px; object-fit: contain; border-radius: 12px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.08)); transition: transform 0.2s ease; }
        .logo-image:hover { transform: scale(1.02); }
        .logo-text { font-size: 1.7rem; font-weight: 800; background: linear-gradient(125deg, #1f5e1a, #4cae4c); -webkit-background-clip: text; background-clip: text; color: transparent; letter-spacing: -0.3px; }
        nav { display: flex; align-items: center; gap: 1.2rem; flex-wrap: wrap; }
        nav a { color: #2a5522; text-decoration: none; font-weight: 600; transition: 0.2s; font-size: 0.95rem; padding: 0.4rem 0.2rem; border-bottom: 2px solid transparent; }
        nav a:hover { color: #3c9e2a; border-bottom-color: #6fbf4c; transform: translateY(-1px); }
        .btn-outline-light { background: transparent; border: 1.5px solid #6fbf4c; padding: 0.45rem 1.2rem; border-radius: 2rem; font-weight: 600; color: #2a6e1f; cursor: pointer; transition: 0.2s; text-decoration: none; display: inline-block; }
        .btn-outline-light:hover { background: #6fbf4c20; transform: scale(0.97); }
        .btn-primary-green { background: linear-gradient(105deg, #4cae4c, #2f8a2b); border: none; padding: 0.5rem 1.4rem; border-radius: 2rem; font-weight: 700; color: white; cursor: pointer; box-shadow: 0 6px 12px rgba(60, 130, 30, 0.25); transition: 0.2s; text-decoration: none; display: inline-block; }
        .btn-primary-green:hover { background: linear-gradient(105deg, #5fc25a, #3a9e32); transform: scale(0.98); }
        .hero { text-align: center; padding: 3.5rem 1.5rem; background: rgba(235, 255, 225, 0.6); backdrop-filter: blur(6px); margin: 1.5rem 2rem; border-radius: 3rem; border: 1px solid rgba(100, 180, 70, 0.5); animation: floatUp 0.9s ease-out; }
        .hero h2 { font-size: 2.7rem; font-weight: 800; background: linear-gradient(125deg, #1c6e1a, #60b840); -webkit-background-clip: text; background-clip: text; color: transparent; margin-bottom: 0.5rem; }
        .slogan { font-size: 1.6rem; font-weight: 600; color: #2a6b1f; letter-spacing: -0.3px; margin: 0.75rem 0; font-style: italic; }
        .hero p { font-size: 1.1rem; max-width: 650px; margin: 1rem auto; color: #2c4d24; }
        section { padding: 2rem 2rem 2.5rem; max-width: 1400px; margin: 0 auto; }
        .section-title { font-size: 2.2rem; font-weight: 700; margin-bottom: 1.8rem; display: flex; align-items: center; gap: 0.7rem; background: linear-gradient(120deg, #1f631a, #58b83a); -webkit-background-clip: text; background-clip: text; color: transparent; border-left: 7px solid #7ac85a; padding-left: 1rem; }
        .cards-grid { display: flex; flex-wrap: wrap; gap: 2rem; margin-top: 1rem; }
        .service-card, .event-card, .coach-card { flex: 1 1 260px; background: rgba(255, 255, 245, 0.7); backdrop-filter: blur(12px); padding: 1.7rem; border-radius: 2rem; transition: all 0.3s; border: 1px solid rgba(100, 170, 70, 0.4); text-align: center; }
        .service-card i, .event-card i, .coach-card i { font-size: 2.5rem; color: #3b8b2c; margin-bottom: 1rem; }
        .service-card h3, .event-card h3 { font-size: 1.5rem; margin: 0.5rem 0; }
        .btn-card { background: #2f7822cc; border: none; padding: 0.6rem 1.3rem; border-radius: 2rem; font-weight: 500; color: white; margin-top: 1rem; cursor: pointer; transition: 0.2s; }
        .btn-card:hover { background: #389e26; transform: translateY(-3px); }
        .events-grid { display: flex; flex-wrap: wrap; gap: 1.8rem; }
        .coaching-flex { display: flex; flex-wrap: wrap; gap: 2rem; }
        .ai-recipe-box { background: rgba(255, 255, 240, 0.75); border-radius: 2rem; padding: 1.5rem; margin-top: 1rem; }
        .recipe-generator { display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; }
        .recipe-generator input { flex: 2; padding: 1rem; border-radius: 3rem; border: 1px solid #b3dc9a; background: rgba(255, 255, 255, 0.85); font-size: 1rem; }
        footer { text-align: center; padding: 2rem; background: rgba(30, 55, 25, 0.9); backdrop-filter: blur(6px); color: #e0f0cf; margin-top: 2rem; font-size: 0.9rem; }
        @keyframes floatUp { 0% { opacity: 0; transform: translateY(35px);} 100% { opacity: 1; transform: translateY(0);} }
        @keyframes gentlePulse { 0% { box-shadow: 0 0 0 0 rgba(100, 200, 80, 0.4);} 70% { box-shadow: 0 0 0 10px rgba(100, 200, 80, 0);} 100% { box-shadow: 0 0 0 0 rgba(100, 200, 80, 0);} }
        .highlight { animation: gentlePulse 0.8s ease; }
        @media (max-width: 780px) { header { flex-direction: column; gap: 1rem; } .hero h2 { font-size: 1.9rem; } .slogan { font-size: 1.2rem; } section { padding: 1.2rem; } .logo-image { height: 38px; } .logo-text { font-size: 1.3rem; } }
        .badge-event { background: #c8e6b5; border-radius: 2rem; padding: 0.2rem 1rem; font-size: 0.75rem; display: inline-block; }
    </style>
</head>
<body>

<header>
    <div class="logo-area">
        <img src="logo web.png" alt="NutriVert - Nutrition intelligente & écologique" class="logo-image" onerror="this.onerror=null; this.src='https://placehold.co/48x48?text=🌿';">
        <span class="logo-text">NutriVert</span>
    </div>
    <nav>
        <a href="#accueil">Accueil</a>
        <a href="#recettes-ai">Recettes AI</a>
        <a href="#marketplace">Marketplace</a>
        <a href="#coaching">Coaching</a>
        <a href="#evenements">Événements</a>
        <?php if ($loggedIn): ?>
            <span style="color:#2a6e1f;">👋 <?= htmlspecialchars($userName) ?></span>
            <a href="index.php?action=profile">Mon profil</a>
            <a href="index.php?action=logout">Déconnexion</a>
            <?php if (isAdmin()): ?>
                <a href="index.php?action=admin">Admin</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="index.php?action=register" class="btn-outline-light">S'inscrire</a>
            <a href="index.php?action=login" class="btn-primary-green">Se connecter</a>
        <?php endif; ?>
    </nav>
</header>

<section id="accueil">
    <div class="hero">
        <h2>🌱 NutriVert</h2>
        <div class="slogan">« Mangez intelligemment, vivez durablement »</div>
        <p>L'écosystème qui réunit nutrition personnalisée, IA green, marketplace éthique et communauté engagée.</p>
        <button class="btn-primary-green" id="exploreBtn" style="padding:0.8rem 2rem; font-size:1rem;">✨ Découvrir nos services</button>
    </div>
</section>

<section id="services-overview">
    <h2 class="section-title"><i class="fas fa-star-of-life"></i> Nos services clés</h2>
    <div class="cards-grid">
        <div class="service-card glass-card">
            <i class="fas fa-robot"></i>
            <h3>Recettes AI</h3>
            <p>Générez des recettes zéro gaspi selon vos ingrédients. Anti-gaspillage intelligent.</p>
            <button class="btn-card" id="navRecettes">🍽️ Essayer</button>
        </div>
        <div class="service-card glass-card">
            <i class="fas fa-store"></i>
            <h3>Marketplace éco</h3>
            <p>Comparez l'empreinte carbone, prix local vs industriel. Achetez responsable.</p>
            <button class="btn-card" id="navMarket">🛒 Explorer</button>
        </div>
        <div class="service-card glass-card">
            <i class="fas fa-chalkboard-user"></i>
            <h3>Coaching durable</h3>
            <p>Coach certifiés : perte de poids, vegan, réduction déchets. Suivi personnalisé.</p>
            <button class="btn-card" id="navCoaching">🧑‍🏫 Réserver</button>
        </div>
        <div class="service-card glass-card">
            <i class="fas fa-calendar-alt"></i>
            <h3>Événements green</h3>
            <p>Ateliers zéro déchet, conférences nutrition & planète. Rejoignez le mouvement.</p>
            <button class="btn-card" id="navEvents">📅 Voir agenda</button>
        </div>
    </div>
</section>

<section id="recettes-ai">
    <h2 class="section-title"><i class="fas fa-microchip"></i> 🤖 Générateur de recettes IA</h2>
    <div class="glass-card ai-recipe-box">
        <div class="recipe-generator">
            <input type="text" id="ingredientInput" placeholder="Ex: courgettes, riz, pois chiches, tomate..." value="courgettes, riz, tomate">
            <button id="generateRecipeBtn" class="btn-primary-green"><i class="fas fa-magic"></i> Générer une recette</button>
        </div>
        <div id="dynamicRecipeDisplay" style="margin-top: 0.8rem;">
            <div class="recette-card" style="background:rgba(250,255,240,0.8); border-radius:1.5rem; padding:1.2rem;">
                <h3>🌿 Riz aux courgettes parfumé</h3>
                <p>🔥 310 kcal | Protéines 9g | Zéro gaspi</p>
                <p><span class="badge-event">💡 Astuce : ajoutez fromage de chèvre local</span></p>
                <button class="btn-card missing-market-link" data-produit="fromage">Voir alternative bio</button>
            </div>
        </div>
    </div>
</section>

<section id="marketplace">
    <h2 class="section-title"><i class="fas fa-chart-simple"></i> 🌍 Marketplace comparateur éco-responsable</h2>
    <div class="cards-grid" id="marketplaceGrid">
        <div class="service-card glass-card">
            <i class="fas fa-seedling"></i>
            <h3>Fromage de chèvre bio</h3>
            <div>🌱 <strong>Ferme des Vallées</strong> — 5.2 DT</div>
            <div>✅ Empreinte carbone : 0.7 kg CO₂</div>
            <button class="btn-card addToList" data-produit="Fromage bio local">➕ Liste éco</button>
        </div>
        <div class="service-card glass-card">
            <i class="fas fa-truck"></i>
            <h3>Fromage standard</h3>
            <div>🏭 Industriel — 3.1 DT</div>
            <div>⚠️ 2.6 kg CO₂</div>
            <button class="btn-card addToList" data-produit="Fromage industriel">➕ Liste</button>
        </div>
        <div class="service-card glass-card">
            <i class="fas fa-apple-alt"></i>
            <h3>Tomates anciennes bio</h3>
            <div>🚜 Maraîcher local — 4.2 DT</div>
            <div>✅ zéro plastique, circuit court</div>
            <button class="btn-card addToList" data-produit="Tomates bio locales">➕ Liste</button>
        </div>
        <div class="service-card glass-card">
            <i class="fas fa-leaf"></i>
            <h3>Légumes variés (panier)</h3>
            <div>🌿 AMAP GreenWay — 9 DT</div>
            <div>⭐ Saison, local & durable</div>
            <button class="btn-card addToList" data-produit="Panier AMAP">➕ Liste</button>
        </div>
    </div>
    <div id="shoppingToast" style="margin-top: 1.2rem; font-weight: 500; background: #daf5ce; border-radius: 2rem; padding: 0.4rem 1rem; display: inline-block;"></div>
</section>

<section id="coaching">
    <h2 class="section-title"><i class="fas fa-heartbeat"></i> 🧘 Coaching sur-mesure</h2>
    <div class="coaching-flex cards-grid">
        <div class="coach-card glass-card">
            <i class="fas fa-user-graduate"></i>
            <h3>Coach Ahmed</h3>
            <p>🏆 Perte de poids & rééquilibrage</p>
            <p>🌿 +200 transformations durables</p>
            <button class="btn-card selectCoach" data-coach="Ahmed">Choisir ce coach</button>
        </div>
        <div class="coach-card glass-card">
            <i class="fas fa-female"></i>
            <h3>Coach Sara</h3>
            <p>🥑 Nutrition sportive & vegan</p>
            <p>⚡ Coaching IA + humain</p>
            <button class="btn-card selectCoach" data-coach="Sara">Choisir ce coach</button>
        </div>
        <div class="coach-card glass-card">
            <i class="fas fa-globe"></i>
            <h3>Coach Lina</h3>
            <p>♻️ Zéro déchet & alimentation responsable</p>
            <p>🍃 Certifiée éco-nutrition</p>
            <button class="btn-card selectCoach" data-coach="Lina">Choisir ce coach</button>
        </div>
    </div>
</section>

<section id="evenements">
    <h2 class="section-title"><i class="fas fa-calendar-check"></i> 📅 Événements & ateliers</h2>
    <div class="events-grid cards-grid">
        <div class="event-card glass-card">
            <i class="fas fa-leaf"></i>
            <h3>Atelier « Zéro gaspi maison »</h3>
            <p>📆 12 avril 2026 - 14h</p>
            <p>Apprenez à cuisiner les épluchures et restes alimentaires.</p>
            <span class="badge-event">Inscription gratuite</span>
            <button class="btn-card eventJoin">Je participe</button>
        </div>
        <div class="event-card glass-card">
            <i class="fas fa-chalkboard"></i>
            <h3>Conférence : IA & Nutrition durable</h3>
            <p>📆 25 avril 2026 - 18h30</p>
            <p>Comment la tech peut réduire l'empreinte alimentaire.</p>
            <button class="btn-card eventJoin">Réserver</button>
        </div>
        <div class="event-card glass-card">
            <i class="fas fa-hand-sparkles"></i>
            <h3>Marché éphémère local</h3>
            <p>📆 3 mai 2026 - 9h>13h</p>
            <p>Producteurs bio, ateliers dégustation.</p>
            <button class="btn-card eventJoin">S'inscrire</button>
        </div>
    </div>
</section>

<footer>
    <p>🌱 © 2026 NutriVert — « Mangez intelligemment, vivez durablement » — Tech au service de la planète</p>
</footer>

<script>
    (function() {
        const recipesDB = [
            { name: "Courgettes farcies au riz & tomates", calories: "320 kcal", proteines: "11g", missing: "fromage de chèvre", link: "fromage", astuce: "fromage bio local" },
            { name: "Buddha bowl pois chiches & avocat", calories: "540 kcal", proteines: "19g", missing: "citron", link: "citron", astuce: "jus de citron bio" },
            { name: "Soupe verte éco-responsable", calories: "120 kcal", proteines: "4g", missing: "graines de courge", link: "graines", astuce: "graines locales" },
            { name: "Omelette aux légumes du marché", calories: "265 kcal", proteines: "17g", missing: "oignon nouveau", link: "oignon", astuce: "oignon bio" }
        ];

        const ingredientInput = document.getElementById('ingredientInput');
        const generateBtn = document.getElementById('generateRecipeBtn');
        const recipeDisplay = document.getElementById('dynamicRecipeDisplay');

        function getRecipeByInput(text) {
            const lower = text.toLowerCase();
            if (lower.includes("courgette") || lower.includes("riz")) return recipesDB[0];
            if (lower.includes("pois chiche") || lower.includes("avocat")) return recipesDB[1];
            return recipesDB[Math.floor(Math.random() * recipesDB.length)];
        }

        function renderRecipe(recipe) {
            recipeDisplay.innerHTML = `
                <div class="recette-card" style="background:rgba(255,255,245,0.9); border-radius:1.5rem; padding:1.2rem;">
                    <h3>🌱 ${recipe.name}</h3>
                    <p>🔥 ${recipe.calories} | Protéines ${recipe.proteines}</p>
                    <p><span class="badge-event">🥗 Ingrédient suggéré : ${recipe.missing}</span></p>
                    <button class="btn-card missing-market-link" data-produit="${recipe.link}">🔍 Voir alternative dans Marketplace</button>
                </div>
            `;
            const newBtn = recipeDisplay.querySelector('.missing-market-link');
            if(newBtn) {
                newBtn.addEventListener('click', () => {
                    document.getElementById('marketplace').scrollIntoView({ behavior: 'smooth' });
                    highlightMarketItem(newBtn.getAttribute('data-produit'));
                });
            }
        }

        generateBtn.addEventListener('click', () => {
            let val = ingredientInput.value.trim();
            if(!val) { ingredientInput.placeholder = "Ajoutez des ingrédients !"; return; }
            const recipe = getRecipeByInput(val);
            renderRecipe(recipe);
        });
        renderRecipe(recipesDB[0]);

        function highlightMarketItem(query) {
            const items = document.querySelectorAll('#marketplaceGrid .service-card');
            items.forEach(card => {
                const title = card.querySelector('h3')?.innerText.toLowerCase() || '';
                if(title.includes(query.toLowerCase()) || (query === 'fromage' && title.includes('fromage'))) {
                    card.classList.add('highlight');
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(() => card.classList.remove('highlight'), 1000);
                }
            });
        }

        let shopping = [];
        const toastDiv = document.getElementById('shoppingToast');
        function updateToastList() {
            if(shopping.length === 0) toastDiv.innerHTML = '🛒 Votre liste éco est vide. Ajoutez des produits responsables !';
            else toastDiv.innerHTML = `🌱 Ma liste durable : ${shopping.join(', ')}  ✅`;
        }
        document.querySelectorAll('.addToList').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const prod = btn.getAttribute('data-produit');
                if(!shopping.includes(prod)) {
                    shopping.push(prod);
                    updateToastList();
                    btn.innerText = '✓ Ajouté';
                    setTimeout(() => btn.innerText = '➕ Liste éco', 1200);
                } else {
                    toastDiv.innerHTML = `⚠️ ${prod} déjà présent`;
                    setTimeout(() => updateToastList(), 1500);
                }
            });
        });

        document.querySelectorAll('.selectCoach').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const coach = btn.getAttribute('data-coach');
                alert(`✨ Félicitations ! Vous avez choisi ${coach} comme coach. Un email de bienvenue vous sera envoyé pour démarrer votre programme durable.`);
            });
        });

        document.querySelectorAll('.eventJoin').forEach(btn => {
            btn.addEventListener('click', () => {
                alert("🎉 Merci ! Vous êtes inscrit à cet événement. Un rappel sera envoyé. Ensemble pour une alimentation intelligente !");
            });
        });

        document.getElementById('navRecettes')?.addEventListener('click', () => document.getElementById('recettes-ai').scrollIntoView({ behavior: 'smooth' }));
        document.getElementById('navMarket')?.addEventListener('click', () => document.getElementById('marketplace').scrollIntoView({ behavior: 'smooth' }));
        document.getElementById('navCoaching')?.addEventListener('click', () => document.getElementById('coaching').scrollIntoView({ behavior: 'smooth' }));
        document.getElementById('navEvents')?.addEventListener('click', () => document.getElementById('evenements').scrollIntoView({ behavior: 'smooth' }));
        document.getElementById('exploreBtn')?.addEventListener('click', () => document.getElementById('services-overview').scrollIntoView({ behavior: 'smooth' }));

        const logoImg = document.querySelector('.logo-image');
        if(logoImg) {
            logoImg.addEventListener('error', function() {
                this.onerror = null;
                this.src = 'https://placehold.co/48x48?text=🌿';
                this.style.borderRadius = '50%';
            });
        }
    })();
</script>
</body>
</html>