<?php
declare(strict_types=1);

require_once __DIR__ . '/../model/NurtviePageModel.php';

/**
 * Contrôleur front — affiche la page publique NutriVert (MVC).
 */
class NurtvieController
{
    public function renderHome(): void
    {
        $model = new NurtviePageModel();
        $page = $model->getPageData();
        extract($page, EXTR_SKIP);
        include dirname(__DIR__) . '/view/layout/nurtvie_main.php';
    }
}
