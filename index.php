<?php
/**
 * Point d’entrée front NutriVert (MVC).
 * URL : http://localhost/projet_web/index.php
 */
declare(strict_types=1);
require_once __DIR__ . '/Controller/NurtvieController.php';
(new NurtvieController())->renderHome();
