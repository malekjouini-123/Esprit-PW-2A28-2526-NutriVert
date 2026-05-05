<?php

class FaceRecognitionService {
    private $scriptPath;
    private $tempDir;
    private $maxImageSize = 5242880;

    public function __construct() {
        $this->scriptPath = realpath(__DIR__ . '/../scripts/nutrivert_face_id.py') ?: __DIR__ . '/../scripts/nutrivert_face_id.py';
        $this->tempDir = __DIR__ . '/../storage/face-id';
    }

    /** Validate a webcam capture and return its face encoding. */
    public function encodeCapturedImage($dataUrl, $userId) {
        $image = $this->saveBase64Image($dataUrl, 'profile_' . (int)$userId);
        if (!$image['success']) {
            return $image;
        }

        $result = $this->runPython(['encode', '--image', $image['path']]);
        @unlink($image['path']);

        return $result;
    }

    /** Compare one webcam capture against one user's stored Face ID encoding. */
    public function compareCapturedImage($dataUrl, $storedEncoding, $userId) {
        $image = $this->saveBase64Image($dataUrl, 'login_' . (int)$userId);
        if (!$image['success']) {
            return $image;
        }

        if (!$storedEncoding) {
            @unlink($image['path']);
            return ['success' => false, 'data' => ['match' => false, 'error' => "Aucun Face ID n'est configure pour cet email."]];
        }

        $result = $this->runPython([
            'compare',
            '--image',
            $image['path'],
            '--encoding',
            $storedEncoding,
            '--user-id',
            (int)$userId
        ]);

        @unlink($image['path']);

        return $result;
    }

    /** Compare a webcam capture against all stored user face encodings. */
    public function identifyCapturedImage($dataUrl, array $knownFaces) {
        $image = $this->saveBase64Image($dataUrl);
        if (!$image['success']) {
            return $image;
        }

        if (empty($knownFaces)) {
            @unlink($image['path']);
            return ['success' => false, 'data' => ['match' => false, 'error' => "Aucun Face ID n'est configure."]];
        }

        $this->ensureTempDir();
        $knownFacesPath = $this->tempDir . DIRECTORY_SEPARATOR . 'known_' . bin2hex(random_bytes(8)) . '.json';
        file_put_contents($knownFacesPath, json_encode($knownFaces));

        $result = $this->runPython([
            'identify',
            '--image',
            $image['path'],
            '--known-faces',
            $knownFacesPath
        ]);

        @unlink($image['path']);
        @unlink($knownFacesPath);

        return $result;
    }

    /** Save a validated file upload to the temporary Face ID directory. */
    /** Decode a webcam data URL and save it as a temporary image. */
    private function saveBase64Image($dataUrl, $prefix = 'capture') {
        if (!preg_match('/^data:(image\/(?:jpeg|png|webp));base64,(.+)$/', $dataUrl, $matches)) {
            return ['success' => false, 'data' => ['error' => 'Capture webcam invalide.']];
        }

        $extension = $this->extensionForMime($matches[1]);
        if (!$extension) {
            return ['success' => false, 'data' => ['error' => 'Format de capture non autorise.']];
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false || strlen($binary) === 0) {
            return ['success' => false, 'data' => ['error' => 'Capture webcam illisible.']];
        }

        if (strlen($binary) > $this->maxImageSize) {
            return ['success' => false, 'data' => ['error' => 'La capture ne doit pas depasser 5 Mo.']];
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

    /** Call the Python CLI and return its decoded JSON payload. */
    private function runPython(array $arguments) {
        $errors = [];

        foreach ($this->pythonCandidates() as $candidate) {
            $commandParts = array_merge($candidate, [$this->scriptPath], $arguments);
            $command = implode(' ', array_map(function($part) {
                return escapeshellarg((string)$part);
            }, $commandParts)) . ' 2>&1';

            $output = [];
            $exitCode = 0;
            exec($command, $output, $exitCode);

            $raw = trim(implode("\n", $output));
            $data = json_decode($raw, true);

            if (is_array($data)) {
                return [
                    'success' => $exitCode === 0 && !empty($data['success']),
                    'data' => $data
                ];
            }

            $errors[] = $raw ?: 'Aucune sortie';
        }

        return [
            'success' => false,
            'data' => [
                'match' => false,
                'error' => "Python n'a retourne aucune sortie JSON valide. Installez Python et opencv-python, ou configurez PYTHON_BIN.",
                'raw' => implode(" | ", array_unique($errors))
            ]
        ];
    }

    /** Return Python command candidates, preferring an explicit PYTHON_BIN. */
    private function pythonCandidates() {
        $configuredPython = getenv('PYTHON_BIN');
        if ($configuredPython) {
            return [[$configuredPython]];
        }

        return [
            ['py', '-3.10'],
        ];
    }

    /** Detect a file MIME type with fileinfo. */
    private function detectMime($path) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $path);
        finfo_close($finfo);
        return $mime;
    }

    /** Map allowed image MIME types to file extensions. */
    private function extensionForMime($mime) {
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        return $allowed[$mime] ?? false;
    }

    /** Create the temporary Face ID directory when needed. */
    private function ensureTempDir() {
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }
}
