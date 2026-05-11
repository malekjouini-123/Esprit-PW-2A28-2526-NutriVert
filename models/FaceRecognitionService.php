<?php
declare(strict_types=1);

if (class_exists('FaceRecognitionService')) return;

class FaceRecognitionService {
    private string $scriptPath;
    private string $tempDir;
    private int $maxImageSize = 5242880;

    public function __construct() {
        $envPath = __DIR__ . '/../config/env.php';
        if (is_file($envPath)) {
            require_once $envPath;
        }

        $this->scriptPath = realpath(__DIR__ . '/../scripts/nutrivert_face_id.py')
            ?: __DIR__ . '/../scripts/nutrivert_face_id.py';
        $this->tempDir = __DIR__ . '/../storage/face-id';
    }

    public function encodeCapturedImage(string $dataUrl, int $userId): array {
        $image = $this->saveBase64Image($dataUrl, 'profile_' . $userId);
        if (!$image['success']) {
            return $image;
        }

        $result = $this->runPython(['encode', '--image', $image['path']]);
        @unlink($image['path']);

        return $result;
    }

    public function compareCapturedImage(string $dataUrl, $storedEncoding, int $userId): array {
        $image = $this->saveBase64Image($dataUrl, 'login_' . $userId);
        if (!$image['success']) {
            return $image;
        }

        if (!$storedEncoding) {
            @unlink($image['path']);
            return ['success' => false, 'data' => ['match' => false, 'error' => "Aucun Face ID n'est configuré pour cet email."]];
        }

        $encodingPath = $this->writeTempEncoding($storedEncoding);

        $result = $this->runPython([
            'compare',
            '--image',       $image['path'],
            '--encoding-file', $encodingPath,
            '--user-id',     $userId,
        ]);

        @unlink($image['path']);
        @unlink($encodingPath);

        return $result;
    }

    public function identifyCapturedImage(string $dataUrl, array $knownFaces): array {
        $image = $this->saveBase64Image($dataUrl);
        if (!$image['success']) {
            return $image;
        }

        if (empty($knownFaces)) {
            @unlink($image['path']);
            return ['success' => false, 'data' => ['match' => false, 'error' => "Aucun Face ID n'est configuré."]];
        }

        $this->ensureTempDir();
        $knownFacesPath = $this->tempDir . DIRECTORY_SEPARATOR . 'known_' . bin2hex(random_bytes(8)) . '.json';
        file_put_contents($knownFacesPath, json_encode($knownFaces));

        $result = $this->runPython([
            'identify',
            '--image',       $image['path'],
            '--known-faces', $knownFacesPath,
        ]);

        @unlink($image['path']);
        @unlink($knownFacesPath);

        return $result;
    }

    private function saveBase64Image(string $dataUrl, string $prefix = 'capture'): array {
        if (!preg_match('/^data:(image\/(?:jpeg|png|webp));base64,(.+)$/', $dataUrl, $matches)) {
            return ['success' => false, 'data' => ['error' => 'Capture webcam invalide.']];
        }

        $extension = $this->extensionForMime($matches[1]);
        if (!$extension) {
            return ['success' => false, 'data' => ['error' => 'Format de capture non autorisé.']];
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false || strlen($binary) === 0) {
            return ['success' => false, 'data' => ['error' => 'Capture webcam illisible.']];
        }

        if (strlen($binary) > $this->maxImageSize) {
            return ['success' => false, 'data' => ['error' => 'La capture ne doit pas dépasser 5 Mo.']];
        }

        $this->ensureTempDir();
        $safePrefix = preg_replace('/[^a-zA-Z0-9_-]/', '', $prefix) ?: 'capture';
        $targetPath = $this->tempDir . DIRECTORY_SEPARATOR . $safePrefix . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
        file_put_contents($targetPath, $binary);

        if ($this->detectMime($targetPath) !== $matches[1]) {
            @unlink($targetPath);
            return ['success' => false, 'data' => ['error' => "Le contenu de l'image est invalide."]];
        }

        return ['success' => true, 'path' => $targetPath];
    }

    private function runPython(array $arguments): array {
        $errors = [];

        foreach ($this->pythonCandidates() as $candidate) {
            $commandParts = array_merge($candidate, [$this->scriptPath], $arguments);
            $command = implode(' ', array_map(
                fn($part) => escapeshellarg((string)$part),
                $commandParts
            )) . ' 2>&1';

            $output   = [];
            $exitCode = 0;
            exec($command, $output, $exitCode);

            $raw  = trim(implode("\n", $output));
            $data = json_decode($raw, true);

            if (is_array($data)) {
                return [
                    'success' => $exitCode === 0 && !empty($data['success']),
                    'data'    => $data,
                ];
            }

            $errors[] = $raw ?: 'Aucune sortie';
        }

        return [
            'success' => false,
            'data' => [
                'match' => false,
                'error' => "Python n'a retourné aucune sortie JSON valide. Installez Python et opencv-python, ou configurez PYTHON_BIN dans config/.env.",
                'raw'   => implode(" | ", array_unique($errors)),
            ],
        ];
    }

    private function pythonCandidates(): array {
        $configuredPython = getenv('PYTHON_BIN');
        if ($configuredPython) {
            return [[$configuredPython]];
        }

        return [
            ['python'],
            ['py', '-3'],
            ['py', '-3.10'],
            ['py'],
        ];
    }

    private function detectMime(string $path): string {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = (string)finfo_file($finfo, $path);
        finfo_close($finfo);
        return $mime;
    }

    private function extensionForMime(string $mime): string|false {
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];

        return $allowed[$mime] ?? false;
    }

    private function ensureTempDir(): void {
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }

    private function writeTempEncoding($encoding): string {
        $this->ensureTempDir();
        $path = $this->tempDir . DIRECTORY_SEPARATOR . 'encoding_' . bin2hex(random_bytes(8)) . '.json';
        $content = is_string($encoding) ? $encoding : (string)json_encode($encoding);
        file_put_contents($path, $content);
        return $path;
    }
}
