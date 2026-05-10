<?php
/**
 * Ancienne URL — redirige vers le point d’entrée MVC (index.php).
 */
declare(strict_types=1);
header('Location: ../index.php', true, 302);
exit;
