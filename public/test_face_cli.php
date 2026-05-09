<?php
// Endpoint de diagnostic pour vérifier que PHP peut invoquer le CLI Python
require_once __DIR__ . '/../config/env.php';

$python = getenv('PYTHON_BIN') ?: 'python';
$script = realpath(__DIR__ . '/../scripts/nutrivert_face_id.py');
$image = 'C:\\nope.jpg';

$parts = [$python, $script, 'encode', '--image', $image];
$cmd = implode(' ', array_map(function($p){ return escapeshellarg((string)$p); }, $parts)) . ' 2>&1';

exec($cmd, $output, $exitCode);

header('Content-Type: text/plain; charset=utf-8');
echo "CMD: $cmd\n";
echo "EXIT: $exitCode\n";
echo "OUTPUT:\n";
echo implode("\n", $output);

// Also show relevant PHP settings for troubleshooting
echo "\n\nPHP disable_functions: " . ini_get('disable_functions') . "\n";
echo "PYTHON_BIN (env): " . ($python ?? '<none>') . "\n";
echo "SCRIPT PATH: " . ($script ?: '<not found>') . "\n";
echo "STORAGE PATH: " . realpath(__DIR__ . '/../storage/face-id') . "\n";

?>
