<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__, 2) . '/model/Categorie.php';
require_once dirname(__DIR__, 2) . '/model/Produit.php';

/**
 * Backend admin — CRUD marketplace (catégories & produits), vues HTML.
 * Contrôle de saisie JS partagé avec le front : js/nv_saisie_marketplace.js
 */
class AdminMarketplaceController
{
    public function dispatch(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $pdo = nv_pdo();
        $catModel = new Categorie($pdo);
        $prodModel = new Produit($pdo);

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $this->handlePost($catModel, $prodModel);
            return;
        }

        $flash = $_SESSION['admin_flash'] ?? null;
        unset($_SESSION['admin_flash']);

        $editCatId = isset($_GET['edit_cat']) ? (int) $_GET['edit_cat'] : 0;
        $editProdId = isset($_GET['edit_prod']) ? (int) $_GET['edit_prod'] : 0;
        $editCat = $editCatId > 0 ? $catModel->findById($editCatId) : null;
        $editProd = $editProdId > 0 ? $prodModel->findById($editProdId) : null;

        $categories = $catModel->findAll();
        $produits = $prodModel->findAll();
        $produitsStockFaible = [];
        foreach ($produits as $p) {
            if ((int) ($p['combien'] ?? 0) < 2) {
                $produitsStockFaible[] = $p;
            }
        }

        $labelOptions = ['Bio', 'Local', 'Sans gluten', 'Équitable', 'Circuit court', 'AOP', 'Saison'];
        $iconeOptions = [
            'fa-seedling' => 'Graine',
            'fa-apple-alt' => 'Fruit / légume',
            'fa-leaf' => 'Feuille',
            'fa-carrot' => 'Carotte',
            'fa-fish' => 'Poisson',
            'fa-egg' => 'Œufs',
            'fa-bread-slice' => 'Boulangerie',
            'fa-truck' => 'Livraison',
        ];
        if ($editProd && !empty($editProd['icone']) && !array_key_exists((string) $editProd['icone'], $iconeOptions)) {
            $ic = (string) $editProd['icone'];
            $iconeOptions = [$ic => $ic] + $iconeOptions;
        }

        extract(compact(
            'categories',
            'produits',
            'produitsStockFaible',
            'flash',
            'editCat',
            'editProd',
            'labelOptions',
            'iconeOptions'
        ));

        ob_start();
        include dirname(__DIR__) . '/view/marketplace_admin.php';
        $adminContent = ob_get_clean();
        include dirname(__DIR__) . '/view/layout_admin.php';
    }

    private function redirect(): void
    {
        header('Location: marketplace.php');
        exit;
    }

    private function flash(bool $ok, string $msg): void
    {
        $_SESSION['admin_flash'] = ['ok' => $ok, 'message' => $msg];
    }

    private function handlePost(Categorie $catModel, Produit $prodModel): void
    {
        $action = $_POST['admin_action'] ?? '';

        try {
            switch ($action) {
                case 'cat_save':
                    $this->saveCategory($catModel);
                    break;
                case 'cat_delete':
                    $this->deleteCategory($catModel);
                    break;
                case 'prod_save':
                    $this->saveProduct($prodModel, $catModel);
                    break;
                case 'prod_delete':
                    $this->deleteProduct($prodModel);
                    break;
                default:
                    $this->flash(false, 'Action inconnue.');
            }
        } catch (RuntimeException $e) {
            $this->flash(false, $e->getMessage());
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $this->flash(false, 'Ce nom de catégorie existe déjà.');
            } else {
                $this->flash(false, 'Erreur base de données.');
            }
        }

        $this->redirect();
    }

    private function saveCategory(Categorie $catModel): void
    {
        $nom = trim((string) ($_POST['cat_nom'] ?? ''));
        $desc = trim((string) ($_POST['cat_description'] ?? ''));
        $desc = $desc === '' ? null : $desc;
        if (strlen($nom) < 2 || strlen($nom) > 120) {
            throw new RuntimeException('Nom catégorie : 2 à 120 caractères.');
        }
        if ($desc !== null && strlen($desc) > 255) {
            throw new RuntimeException('Description : maximum 255 caractères.');
        }
        $id = isset($_POST['cat_id']) ? (int) $_POST['cat_id'] : 0;
        if ($id > 0) {
            $catModel->update($id, $nom, $desc);
            $this->flash(true, 'Catégorie modifiée.');
        } else {
            $catModel->create($nom, $desc);
            $this->flash(true, 'Catégorie ajoutée.');
        }
    }

    private function deleteCategory(Categorie $catModel): void
    {
        $id = (int) ($_POST['cat_id'] ?? 0);
        if ($id < 1) {
            throw new RuntimeException('Identifiant catégorie invalide.');
        }
        try {
            $catModel->delete($id);
            $this->flash(true, 'Catégorie supprimée.');
        } catch (PDOException $e) {
            $code = (int) ($e->errorInfo[1] ?? 0);
            if ($code === 1451) {
                throw new RuntimeException('Impossible de supprimer : des produits sont encore liés à cette catégorie.');
            }
            throw $e;
        }
    }

    private function saveProduct(Produit $prodModel, Categorie $catModel): void
    {
        $cid = (int) ($_POST['prod_categorie_id'] ?? 0);
        $nom = trim((string) ($_POST['prod_nom'] ?? ''));
        $producteur = trim((string) ($_POST['prod_producteur'] ?? ''));
        $producteur = $producteur === '' ? null : $producteur;
        $prix = (float) ($_POST['prod_prix'] ?? 0);
        $co2raw = trim((string) ($_POST['prod_co2'] ?? ''));
        $co2 = $co2raw === '' ? null : (float) $co2raw;
        $icone = preg_replace('/[^a-z0-9\-]/i', '', (string) ($_POST['prod_icone'] ?? 'fa-seedling'));
        if ($icone === '') {
            $icone = 'fa-seedling';
        }

        $labels = $_POST['prod_labels'] ?? [];
        if (!is_array($labels)) {
            $labels = [];
        }
        $allowed = ['Bio', 'Local', 'Sans gluten', 'Équitable', 'Circuit court', 'AOP', 'Saison'];
        $labels = array_values(array_intersect($allowed, array_map('strval', $labels)));
        $labelStr = $labels === [] ? null : implode(', ', $labels);

        if ($cid < 1 || $catModel->findById($cid) === null) {
            throw new RuntimeException('Choisissez une catégorie valide.');
        }
        if (strlen($nom) < 2 || strlen($nom) > 180) {
            throw new RuntimeException('Nom produit : 2 à 180 caractères.');
        }
        if ($producteur !== null && strlen($producteur) > 150) {
            throw new RuntimeException('Producteur : maximum 150 caractères.');
        }
        if (!is_finite($prix) || $prix < 0 || $prix > 999999.99) {
            throw new RuntimeException('Prix invalide.');
        }
        if ($co2 !== null && (!is_finite($co2) || $co2 < 0)) {
            throw new RuntimeException('Empreinte CO₂ invalide (nombre ≥ 0).');
        }

        $combien = (int) ($_POST['prod_combien'] ?? 0);
        if ($combien < 0 || $combien > 9999999) {
            throw new RuntimeException('Quantité en stock (Combien) : entier entre 0 et 9 999 999.');
        }

        $id = isset($_POST['prod_id']) ? (int) $_POST['prod_id'] : 0;
        if ($id > 0) {
            $prodModel->update($id, $cid, $nom, $labelStr, $producteur, $prix, $co2, $combien, $icone);
            $this->flash(true, 'Produit modifié.');
        } else {
            $prodModel->create($cid, $nom, $labelStr, $producteur, $prix, $co2, $combien, $icone);
            $this->flash(true, 'Produit ajouté.');
        }
    }

    private function deleteProduct(Produit $prodModel): void
    {
        $id = (int) ($_POST['prod_id'] ?? 0);
        if ($id < 1) {
            throw new RuntimeException('Identifiant produit invalide.');
        }
        try {
            $prodModel->delete($id);
            $this->flash(true, 'Produit supprimé.');
        } catch (PDOException $e) {
            $code = (int) ($e->errorInfo[1] ?? 0);
            if ($code === 1451) {
                throw new RuntimeException('Impossible de supprimer : ce produit est référencé dans une commande.');
            }
            throw $e;
        }
    }
}
