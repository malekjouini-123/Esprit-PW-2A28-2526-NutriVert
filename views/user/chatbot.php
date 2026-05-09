<?php
// views/user/chatbot.php - Chatbot spécialisé en coaching nutrition
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Coaching - Nutrivert</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --coral: #FF7E67;
            --mint: #7DCFB6;
            --sage: #4A6B4A;
            --light-bg: #f4f8f4;
        }
        body {
            background: var(--light-bg);
            color: var(--sage);
        }
        .navbar-top {
            background: white;
            padding: 15px 0;
            border-bottom: 2px solid var(--mint);
            margin-bottom: 30px;
        }
        .navbar-top .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--coral);
        }
        .user-info {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .btn-back {
            background: var(--mint);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-back:hover {
            background: #6ab89d;
            color: white;
        }
        .btn-logout {
            background: var(--coral);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-logout:hover {
            background: #e56a55;
            color: white;
        }
        .chat-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .chat-header {
            background: linear-gradient(135deg, var(--mint), var(--sage));
            color: white;
            padding: 20px;
            text-align: center;
        }
        .chat-messages {
            height: 500px;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }
        .message {
            margin-bottom: 15px;
            padding: 12px 16px;
            border-radius: 18px;
            max-width: 70%;
            word-wrap: break-word;
        }
        .message.user {
            background: var(--coral);
            color: white;
            margin-left: auto;
            text-align: right;
        }
        .message.bot {
            background: white;
            color: var(--sage);
            border: 1px solid #e9ecef;
        }
        .chat-input-container {
            padding: 20px;
            background: white;
            border-top: 1px solid #e9ecef;
        }
        .chat-input {
            display: flex;
            gap: 10px;
        }
        .chat-input input {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 25px;
            outline: none;
        }
        .chat-input input:focus {
            border-color: var(--mint);
        }
        .chat-input button {
            background: var(--coral);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
        }
        .chat-input button:hover {
            background: #e56a55;
        }
        .typing-indicator {
            display: none;
            padding: 12px 16px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="navbar-top">
    <div class="container">
        <div class="logo">
            <i class="fas fa-leaf"></i> Nutrivert
        </div>
        <div class="user-info">
            <a href="index.php?controller=user_dashboard&action=index" class="btn-back">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <a href="index.php?controller=user&action=logout" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="chat-container">
        <div class="chat-header">
            <h2><i class="fas fa-robot"></i> Coach Nutrition Chatbot</h2>
            <p>Je suis spécialisé dans la création de programmes de coaching nutrition et d'exercices liés à la nutrition.</p>
        </div>

        <div class="chat-messages" id="chatMessages">
            <div class="message bot">
                <strong>Coach Nutrition:</strong> Bonjour ! Je suis votre coach spécialisé en nutrition. Je peux vous aider à créer des programmes de coaching personnalisés et vous proposer des exercices liés à la nutrition. Que souhaitez-vous savoir ?
            </div>
        </div>

        <div class="typing-indicator" id="typingIndicator">
            <i class="fas fa-circle"></i> Le coach est en train d'écrire...
        </div>

        <div class="chat-input-container">
            <div class="chat-input">
                <input type="text" id="messageInput" placeholder="Posez votre question sur la nutrition..." maxlength="500">
                <button onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i> Envoyer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const currentUser = {
    name: <?= json_encode($user['nom'] ?? 'Utilisateur') ?>,
    objectif: <?= json_encode($user['objectif'] ?? null) ?>,
    poids: <?= json_encode($user['poids'] ?? null) ?>,
    taille: <?= json_encode($user['taille'] ?? null) ?>,
    imc: <?= json_encode($user['imc'] ?? null) ?>,
    calories: <?= json_encode($user['calories'] ?? null) ?>
};

const nutritionKeywords = [
    'nutrition', 'alimentation', 'repas', 'calories', 'protéines', 'proteines', 'glucides', 'lipides',
    'vitamines', 'minéraux', 'mineraux', 'eau', 'hydratation', 'régime', 'regime', 'diète', 'diete', 'poids', 'imc',
    'exercice', 'sport', 'fitness', 'muscle', 'cardio', 'entraînement', 'entrainement', 'coaching',
    'programme', 'plan', 'objectif', 'perte de poids', 'prise de poids', 'santé', 'sante', 'bien-être', 'bien etre',
    'équilibre', 'equilibre', 'menu', 'recette', 'complément', 'complement', 'supplément', 'suplément', 'métabolisme', 'metabolisme',
    'végétarien', 'vegetarien', 'végétalien', 'vegetalien', 'sans gluten', 'sans lactose'
];

const intentKeywords = {
    program: ['programme', 'coaching', 'plan', 'menu', 'nutritionnel', 'nutritionnelle', 'alimentation', 'repas'],
    calories: ['calorie', 'calories', 'besoin', 'métabolisme', 'metabolisme', 'apport', 'kcal'],
    protein: ['protéine', 'proteine', 'protéines', 'proteines', 'muscle', 'muscles', 'protéines'],
    hydration: ['eau', 'hydratation', 'boire', 'liquide'],
    exercise: ['exercice', 'sport', 'entraînement', 'entrainement', 'fitness', 'cardio', 'séance', 'seance'],
    diet: ['végétarien', 'vegetarien', 'végétalien', 'vegetalien', 'sans gluten', 'sans lactose', 'paleo', 'céto', 'keto']
};

const levels = ['débutant', 'debutant', 'intermédiaire', 'intermediaire', 'avancé', 'avance', 'avancé', 'avancée', 'avancée'];
const goals = ['perte', 'perte de poids', 'maintien', 'maintenir', 'muscle', 'prise de muscle', 'tonification'];

const botState = {
    intent: null,
    pendingQuestion: null,
    profile: {
        objectif: null,
        niveau: null,
        regime: null
    }
};

function normalize(text) {
    return text.toLowerCase().trim();
}

function containsAny(text, list) {
    return list.some(keyword => text.includes(keyword));
}

function isNutritionRelated(message) {
    const normalized = normalize(message);
    return botState.pendingQuestion !== null || containsAny(normalized, nutritionKeywords);
}

function detectIntent(message) {
    const text = normalize(message);
    if (containsAny(text, intentKeywords.program)) return 'program';
    if (containsAny(text, intentKeywords.calories)) return 'calories';
    if (containsAny(text, intentKeywords.protein)) return 'protein';
    if (containsAny(text, intentKeywords.hydration)) return 'hydration';
    if (containsAny(text, intentKeywords.exercise)) return 'exercise';
    if (containsAny(text, intentKeywords.diet)) return 'diet';
    return null;
}

function parseGoal(message) {
    const text = normalize(message);
    if (text.includes('perte')) return 'perte de poids';
    if (text.includes('muscle') || text.includes('prise')) return 'prise de muscle';
    if (text.includes('maintien') || text.includes('maintenir') || text.includes('maintenir')) return 'maintien';
    return null;
}

function parseLevel(message) {
    const text = normalize(message);
    if (text.includes('début')) return 'débutant';
    if (text.includes('interm')) return 'intermédiaire';
    if (text.includes('avancé') || text.includes('avance')) return 'avancé';
    return null;
}

function parseDiet(message) {
    const text = normalize(message);
    if (text.includes('végétar') || text.includes('vegetar')) return 'végétarien';
    if (text.includes('végétalien') || text.includes('vegetalien') || text.includes('végan') || text.includes('vegan')) return 'végétalien';
    if (text.includes('sans gluten')) return 'sans gluten';
    if (text.includes('sans lactose')) return 'sans lactose';
    if (text.includes('paleo')) return 'paléo';
    if (text.includes('céto') || text.includes('keto')) return 'cétogène';
    return 'omnivore';
}

function askNextProgramQuestion() {
    if (!botState.profile.objectif) {
        botState.pendingQuestion = 'goal';
        return 'Quel est votre objectif principal ? Perte de poids, maintien ou prise de muscle ?';
    }
    if (!botState.profile.niveau) {
        botState.pendingQuestion = 'level';
        return 'Quel est votre niveau actuel ? Débutant, intermédiaire ou avancé ?';
    }
    if (!botState.profile.regime) {
        botState.pendingQuestion = 'diet';
        return 'Avez-vous une préférence alimentaire ? Par exemple : végétarien, végétalien, sans gluten, sans lactose, ou rien de particulier.';
    }
    botState.pendingQuestion = null;
    return generateNutritionProgram();
}

function generateNutritionProgram() {
    const objectif = botState.profile.objectif || currentUser.objectif || 'maintien';
    const niveau = botState.profile.niveau || 'intermédiaire';
    const regime = botState.profile.regime || 'omnivore';

    const intro = `Voici un exemple de programme nutritionnel pour un objectif de ${objectif} avec un niveau ${niveau} en ${regime}.`; 
    const structure = [
        `Petit-déjeuner : bol de flocons d'avoine avec fruits frais, une source de protéines comme du yaourt grec ou des œufs, et une boisson hydratante.`,
        `Déjeuner : une source de protéines maigres (poisson, poulet, légumineuses), une portion de légumes verts, une céréale complète ou une patate douce, et une salade colorée.`,
        `Goûter : smoothie protéiné, fruits, ou une poignée de noix et graines.`,
        `Dîner : portion de protéines, légumes cuits ou vapeur, et une petite portion de glucides complexes selon votre objectif.`
    ];

    const detail = [];
    if (objectif.includes('perte')) {
        detail.push('Réduisez légèrement les glucides raffinés et favorisez les fibres pour rester rassasié.');
    }
    if (objectif.includes('muscle')) {
        detail.push('Augmentez les protéines à chaque repas et gardez des collations riches en protéines après l’entraînement.');
    }
    if (objectif.includes('maintien')) {
        detail.push('Maintenez un bon équilibre entre protéines, glucides complexes et légumes, et surveillez les portions.');
    }

    if (regime === 'végétarien' || regime === 'végétalien') {
        detail.push('Pensez aux sources végétales de protéines : légumineuses, tofu, tempeh, quinoa, noix et graines.');
    }
    if (regime === 'sans gluten') {
        detail.push('Utilisez des céréales sans gluten comme le riz, le quinoa, le sarrasin et les patates douces.');
    }
    if (regime === 'sans lactose') {
        detail.push('Choisissez des alternatives sans lactose et concentrez-vous sur des sources de calcium végétales.');
    }

    const footer = 'N’oubliez pas de boire au moins 1,5 à 2 litres d’eau par jour et de répartir les repas pour rester énergique.';

    botState.intent = 'program_complete';
    return `${intro} ${structure.join(' ')} ${detail.join(' ')} ${footer}`;
}

function handleProgramAnswer(message) {
    const text = normalize(message);

    if (botState.pendingQuestion === 'goal') {
        const parsedGoal = parseGoal(text);
        const parsedLevel = parseLevel(text);
        if (parsedGoal) {
            botState.profile.objectif = parsedGoal;
            return askNextProgramQuestion();
        }
        if (parsedLevel) {
            botState.profile.niveau = parsedLevel;
            botState.pendingQuestion = 'goal';
            return 'Je comprends que vous êtes ' + parsedLevel + '. Quel est votre objectif principal : perte de poids, maintien ou prise de muscle ?';
        }
        return 'Je n’ai pas bien compris votre objectif. Préférez-vous la perte de poids, le maintien du poids, ou la prise de muscle ?';
    }

    if (botState.pendingQuestion === 'level') {
        const parsedLevel = parseLevel(text);
        const parsedGoal = parseGoal(text);
        if (parsedLevel) {
            botState.profile.niveau = parsedLevel;
            return askNextProgramQuestion();
        }
        if (parsedGoal) {
            botState.profile.objectif = parsedGoal;
            botState.pendingQuestion = 'level';
            return 'Très bien. Quel est votre niveau actuel ? Débutant, intermédiaire ou avancé ?';
        }
        return 'Je n’ai pas bien compris votre niveau. Êtes-vous débutant, intermédiaire ou avancé ?';
    }

    if (botState.pendingQuestion === 'diet') {
        const parsedGoal = parseGoal(text);
        if (parsedGoal) {
            botState.profile.objectif = parsedGoal;
            return 'D’accord, votre objectif est ' + parsedGoal + '. Maintenant, avez-vous une préférence alimentaire ? Par exemple : végétarien, végétalien, sans gluten, sans lactose, ou rien de particulier.';
        }

        botState.profile.regime = parseDiet(text);
        if (botState.profile.regime === 'omnivore' && !containsAny(text, intentKeywords.diet)) {
            return 'Je n’ai pas détecté de préférence alimentaire spécifique. Si vous avez un régime végétarien, végétalien, sans gluten ou sans lactose, dites-le-moi, sinon écrivez "rien de particulier".';
        }
        return askNextProgramQuestion();
    }

    return askNextProgramQuestion();
}

function getNutritionResponse(message) {
    const text = normalize(message);
    const intent = detectIntent(message);

    if (botState.intent === 'program' || botState.pendingQuestion) {
        return handleProgramAnswer(message);
    }

    if (intent === 'program') {
        botState.intent = 'program';
        return askNextProgramQuestion();
    }

    if (intent === 'calories') {
        return 'Pour estimer vos besoins caloriques, je peux utiliser votre poids, taille, âge et niveau d’activité. Si vous voulez, donnez-moi ces informations et je vous proposerai un apport quotidien adapté.';
    }

    if (intent === 'protein') {
        return 'Les protéines sont essentielles pour la réparation musculaire et pour la sensation de satiété. Pour un coach en nutrition, je recommande 1.6 à 2.2 g de protéines par kg de poids corporel si votre objectif est de prendre du muscle.';
    }

    if (intent === 'hydration') {
        return 'L’hydratation est importante : buvez au moins 1.5 à 2 litres d’eau par jour, et augmentez si vous faites du sport ou s’il fait chaud.';
    }

    if (intent === 'exercise') {
        return 'Pour un programme nutritionnel efficace, associez des séances régulières et des repas équilibrés. Dites-moi votre type d’activité pour que je vous conseille un bon équilibre nutrition-exercice.';
    }

    if (intent === 'diet') {
        const regime = parseDiet(text);
        return `Très bien, je peux adapter un programme à un régime ${regime}. Voulez-vous construire un programme de nutrition personnalisé maintenant ?`;
    }

    return 'Je suis un coach nutrition spécialisé. Parlez-moi de votre objectif, de votre niveau, et de vos préférences alimentaires pour que je vous donne un programme précis.';
}

function sendMessage() {
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    if (!message) return;

    addMessage(message, 'user');
    input.value = '';
    document.getElementById('typingIndicator').style.display = 'block';

    setTimeout(() => {
        document.getElementById('typingIndicator').style.display = 'none';
        if (!isNutritionRelated(message)) {
            addMessage('Désolé, je suis spécialisé uniquement dans les programmes de coaching et les exercices liés à la nutrition. Je ne peux pas répondre à d\'autres sujets.', 'bot');
            return;
        }
        const response = getNutritionResponse(message);
        addMessage(response, 'bot');
    }, 800 + Math.random() * 900);
}

function addMessage(text, sender) {
    const messagesDiv = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${sender}`;
    messageDiv.innerHTML = sender === 'bot' ? `<strong>Coach Nutrition:</strong> ${text}` : text;
    messagesDiv.appendChild(messageDiv);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

document.getElementById('messageInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
});
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>