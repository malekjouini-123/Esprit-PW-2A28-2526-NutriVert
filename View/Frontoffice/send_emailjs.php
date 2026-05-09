<?php
/**
 * Envoi d'email via l'API EmailJS (PHP)
 * 
 * Paramètres attendus (POST ou GET) :
 * - email   : destinataire
 * - link    : lien personnalisé (ex: reset password)
 * 
 * Ou utilisez un tableau $templateParams personnalisé.
 */

// Configuration EmailJS
$serviceId  = 'service_zwzm9wd';      // votre Service ID
$templateId = 'template_j5artrm';      // votre Template ID
$userId     = 'VOTRE_USER_ID_EMAILJS'; // votre User ID (trouvable dans Account > API Keys)

// Récupérer les données (exemple pour un formulaire)
$destinataire = $_POST['email'] ?? $_GET['email'] ?? '';
$lien         = $_POST['link'] ?? $_GET['link'] ?? '';

if (empty($destinataire) || empty($lien)) {
    http_response_code(400);
    die(json_encode(['error' => 'email et link sont requis']));
}

// Paramètres du template (doivent correspondre aux variables de votre template EmailJS)
$templateParams = [
    'email' => $destinataire,
    'link'  => $lien,
    // Vous pouvez ajouter d'autres champs comme 'message', 'nom', etc.
];

// Appel à l'API EmailJS
$url = 'https://api.emailjs.com/api/v1.0/email/send';

$payload = [
    'service_id'  => $serviceId,
    'template_id' => $templateId,
    'user_id'     => $userId,
    'template_params' => $templateParams
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // à true en production

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo json_encode(['success' => true, 'message' => 'Email envoyé avec succès']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $response]);
}
?>