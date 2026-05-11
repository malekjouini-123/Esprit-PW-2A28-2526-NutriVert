<?php
declare(strict_types=1);

return [
    // Utiliser 'ollama_api' pour appeler Ollama via son endpoint local (ou changez en 'ollama' pour exécuter binaire)
    // Valeurs possibles: 'ollama_api', 'ollama', 'openai', 'local'
    'provider' => 'ollama_api',
    'openai_api_key' => '',
    'ollama_api_url' => 'http://127.0.0.1:11434/api/generate',
    'ollama_path' => 'C:\\Users\\user\\AppData\\Local\\Programs\\Ollama\\ollama.exe',
    'ollama_model' => 'llama2:latest',
    'model' => 'gpt-3.5-turbo',
    'temperature' => 0.7,
    'max_tokens' => 800,
];
