<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>📧 Emails de Développement - NutriVert</title>
    <style>
        body { 
            background: #f0f9ea; 
            font-family: Arial; 
            padding: 2rem;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        h1 { 
            color: #2e7d32;
        }
        .email-item {
            background: white;
            border-left: 4px solid #2e7d32;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .email-to {
            font-weight: bold;
            color: #333;
        }
        .email-subject {
            color: #666;
            font-size: 0.9rem;
        }
        .email-date {
            color: #999;
            font-size: 0.85rem;
        }
        .reset-link {
            background: #f0f9ea;
            border: 1px solid #2e7d32;
            padding: 0.8rem;
            border-radius: 0.5rem;
            margin-top: 0.5rem;
            word-break: break-all;
            font-family: monospace;
            font-size: 0.9rem;
        }
        .reset-link a {
            color: #2e7d32;
            text-decoration: none;
            font-weight: bold;
        }
        .reset-link a:hover {
            text-decoration: underline;
        }
        .no-emails {
            text-align: center;
            color: #999;
            padding: 2rem;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            color: #856404;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>📧 Emails de Développement</h1>
    
    <div class="warning">
        ⚠️ <strong>Mode Développement</strong><br>
        Cette page affiche les emails générés par le système de réinitialisation de mot de passe.
        En production, les vrais emails seront envoyés.
    </div>

    <?php
    $emailDir = __DIR__ . '/storage/emails';
    $emails = [];
    
    if (is_dir($emailDir)) {
        $files = array_diff(scandir($emailDir, SCANDIR_SORT_DESCENDING), ['.', '..']);
        
        if (empty($files)) {
            echo '<div class="no-emails">Aucun email généré pour le moment.</div>';
        } else {
            foreach ($files as $file) {
                $filepath = $emailDir . '/' . $file;
                if (is_file($filepath)) {
                    $content = file_get_contents($filepath);
                    echo '<div class="email-item">';
                    echo '<div class="email-date">📅 ' . substr($file, 0, 19) . '</div>';
                    
                    // Parse email content
                    preg_match('/TO: (.+)\n/', $content, $toMatch);
                    preg_match('/SUBJECT: (.+)\n/', $content, $subjectMatch);
                    preg_match('/RESET_LINK: (.+)\n/', $content, $linkMatch);
                    
                    if (!empty($toMatch)) {
                        echo '<div class="email-to">📧 ' . htmlspecialchars($toMatch[1]) . '</div>';
                    }
                    if (!empty($subjectMatch)) {
                        echo '<div class="email-subject">📝 ' . htmlspecialchars($subjectMatch[1]) . '</div>';
                    }
                    if (!empty($linkMatch)) {
                        $link = trim($linkMatch[1]);
                        echo '<div class="reset-link">';
                        echo '<strong>Lien de réinitialisation:</strong><br>';
                        echo '<a href="' . htmlspecialchars($link) . '" target="_blank">';
                        echo htmlspecialchars($link);
                        echo '</a>';
                        echo '</div>';
                    }
                    
                    echo '</div>';
                }
            }
        }
    } else {
        echo '<div class="no-emails">Le dossier d\'emails n\'existe pas.</div>';
    }
    ?>

    <p style="text-align: center; margin-top: 2rem;">
        <a href="public/index.php" style="color: #2e7d32; text-decoration: none;">← Retour à l'accueil</a>
    </p>
</div>
</body>
</html>
