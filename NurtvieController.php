<?php
declare(strict_types=1);

require_once __DIR__ . '/../model/NurtviePageModel.php';
require_once __DIR__ . '/../model/ThemePreference.php';

/**
 * Contrôleur front — affiche la page publique NutriVert (MVC).
 */
class NurtvieController
{
    public function renderHome(): void
    {
        $theme = $this->resolveThemePreference();
        $model = (new NurtviePageModel())
            ->setTitle('NutriVert | Mangez intelligemment, vivez durablement')
            ->setCssHref('css/nurtvie.css')
            ->setJsSaisieHref('js/nv_saisie_marketplace.js')
            ->setJsPageHref('js/nurtvie_page.js')
            ->setLogoSrc('logoo.png')
            ->setAdminHref('back/marketplace.php')
            ->setInitialTheme($theme->getResolvedTheme());
        $page = [
            'title' => $model->getTitle(),
            'css_href' => $model->getCssHref(),
            'js_saisie_href' => $model->getJsSaisieHref(),
            'js_page_href' => $model->getJsPageHref(),
            'logo_src' => $model->getLogoSrc(),
            'admin_href' => $model->getAdminHref(),
            'initial_theme' => $model->getInitialTheme(),
        ];
        extract($page, EXTR_SKIP);
        include dirname(__DIR__) . '/view/layout/nurtvie_main.php';
    }

    private function resolveThemePreference(): ThemePreference
    {
        $mode = isset($_COOKIE['nv_theme_mode']) ? (string) $_COOKIE['nv_theme_mode'] : 'auto';
        if (!in_array($mode, ['light', 'dark', 'auto'], true)) {
            $mode = 'auto';
        }
        $hour = (int) date('G');
        $autoTheme = ($hour >= 19 || $hour < 7) ? 'dark' : 'light';
        $resolved = $mode === 'auto' ? $autoTheme : $mode;
        return (new ThemePreference())
            ->setMode($mode)
            ->setResolvedTheme($resolved);
    }
}
