<?php
return [
    'db_host' => 'localhost',
    'db_name' => 'nutrivert',
    'db_user' => 'root',
    'db_pass' => '',

    // Pour activer la vraie IA OpenAI :
    // Windows CMD temporaire : set OPENAI_API_KEY=sk-...
    // Ou écris ta clé directement ici (déconseillé si tu pushes sur GitHub).
    'openai_api_key' => getenv('OPENAI_API_KEY') ?: '',
    'openai_model' => 'gpt-4o-mini',
];
