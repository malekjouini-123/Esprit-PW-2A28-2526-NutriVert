<?php
declare(strict_types=1);

/**
 * Modèle front — métadonnées et chemins publics de la page d’accueil NutriVert.
 */
class NurtviePageModel
{
    /**
     * @return array{
     *   title:string,
     *   css_href:string,
     *   js_saisie_href:string,
     *   js_page_href:string,
     *   logo_src:string,
     *   admin_href:string
     * }
     */
    public function getPageData(): array
    {
        return [
            'title' => 'NutriVert | Mangez intelligemment, vivez durablement',
            'css_href' => 'css/nurtvie.css',
            'js_saisie_href' => 'js/nv_saisie_marketplace.js',
            'js_page_href' => 'js/nurtvie_page.js',
            'logo_src' => 'front/logo web.png',
            'admin_href' => 'back/marketplace.php',
        ];
    }
}
