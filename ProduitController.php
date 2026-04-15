<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Produit.php';

/**
 * Contrôleur API JSON : CRUD produits.
 */
class ProduitController
{
    public function __construct(private ?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? nv_pdo();
    }

    public function handle(): void
    {
        $model = new Produit($this->pdo);
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        try {
            switch ($method) {
                case 'GET':
                    $cat = isset($_GET['categorie_id']) ? (int) $_GET['categorie_id'] : 0;
                    $rows = $model->findAll($cat > 0 ? $cat : null);
                    nv_json($rows);

                case 'POST':
                    $data = $this->parseBody(nv_json_input());
                    if ($data === null) {
                        nv_json(['error' => 'categorie_id, nom et quantité stock (combien) valides requis.'], 400);
                    }
                    $id = $model->create(
                        $data['categorie_id'],
                        $data['nom'],
                        $data['label'],
                        $data['producteur'],
                        $data['prix'],
                        $data['empreinte_co2'],
                        $data['combien'],
                        $data['icone']
                    );
                    nv_json(['ok' => true, 'id' => $id]);

                case 'PUT':
                    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
                    if ($id < 1) {
                        nv_json(['error' => 'Paramètre id manquant.'], 400);
                    }
                    $data = $this->parseBody(nv_json_input());
                    if ($data === null) {
                        nv_json(['error' => 'categorie_id, nom et quantité stock (combien) valides requis.'], 400);
                    }
                    $affected = $model->update(
                        $id,
                        $data['categorie_id'],
                        $data['nom'],
                        $data['label'],
                        $data['producteur'],
                        $data['prix'],
                        $data['empreinte_co2'],
                        $data['combien'],
                        $data['icone']
                    );
                    nv_json(['ok' => true, 'affected' => $affected]);

                case 'DELETE':
                    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
                    if ($id < 1) {
                        nv_json(['error' => 'Paramètre id manquant.'], 400);
                    }
                    try {
                        $model->delete($id);
                        nv_json(['ok' => true]);
                    } catch (PDOException $e) {
                        $code = (int) ($e->errorInfo[1] ?? 0);
                        if ($code === 1451) {
                            nv_json(['error' => 'Impossible de supprimer : ce produit est référencé dans une commande.'], 409);
                        }
                        throw $e;
                    }

                default:
                    nv_json(['error' => 'Méthode non autorisée.'], 405);
            }
        } catch (PDOException $e) {
            nv_json(['error' => 'Erreur base de données.', 'detail' => $e->getMessage()], 500);
        }
    }

    /**
     * @return array{categorie_id:int,nom:string,label:?string,producteur:?string,prix:float,empreinte_co2:?float,combien:int,icone:string}|null
     */
    private function parseBody(array $in): ?array
    {
        $cid = isset($in['categorie_id']) ? (int) $in['categorie_id'] : 0;
        $nom = isset($in['nom']) ? trim((string) $in['nom']) : '';
        if ($cid < 1 || $nom === '') {
            return null;
        }
        $label = isset($in['label']) ? trim((string) $in['label']) : null;
        if ($label === '') {
            $label = null;
        }
        $producteur = isset($in['producteur']) ? trim((string) $in['producteur']) : null;
        if ($producteur === '') {
            $producteur = null;
        }
        $prix = isset($in['prix']) ? (float) $in['prix'] : 0;
        $co2 = array_key_exists('empreinte_co2', $in) && $in['empreinte_co2'] !== '' && $in['empreinte_co2'] !== null
            ? (float) $in['empreinte_co2'] : null;
        $icone = isset($in['icone']) ? preg_replace('/[^a-z0-9\-]/i', '', (string) $in['icone']) : 'fa-seedling';
        if ($icone === '') {
            $icone = 'fa-seedling';
        }
        $combien = isset($in['combien']) ? (int) $in['combien'] : 0;
        if ($combien < 0 || $combien > 9999999) {
            return null;
        }

        return [
            'categorie_id' => $cid,
            'nom' => $nom,
            'label' => $label,
            'producteur' => $producteur,
            'prix' => $prix,
            'empreinte_co2' => $co2,
            'combien' => $combien,
            'icone' => $icone,
        ];
    }
}
