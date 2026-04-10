<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Categorie.php';

/**
 * Contrôleur API JSON : CRUD catégories.
 */
class CategorieController
{
    public function __construct(private ?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? nv_pdo();
    }

    public function handle(): void
    {
        $model = new Categorie($this->pdo);
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        try {
            switch ($method) {
                case 'GET':
                    nv_json($model->findAll());

                case 'POST':
                    $in = nv_json_input();
                    $nom = isset($in['nom']) ? trim((string) $in['nom']) : '';
                    if ($nom === '') {
                        nv_json(['error' => 'Le nom de la catégorie est obligatoire.'], 400);
                    }
                    $desc = isset($in['description']) ? trim((string) $in['description']) : null;
                    if ($desc === '') {
                        $desc = null;
                    }
                    $id = $model->create($nom, $desc);
                    nv_json(['ok' => true, 'id' => $id]);

                case 'PUT':
                    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
                    if ($id < 1) {
                        nv_json(['error' => 'Paramètre id manquant.'], 400);
                    }
                    $in = nv_json_input();
                    $nom = isset($in['nom']) ? trim((string) $in['nom']) : '';
                    if ($nom === '') {
                        nv_json(['error' => 'Le nom de la catégorie est obligatoire.'], 400);
                    }
                    $desc = isset($in['description']) ? trim((string) $in['description']) : null;
                    if ($desc === '') {
                        $desc = null;
                    }
                    $affected = $model->update($id, $nom, $desc);
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
                            nv_json(['error' => 'Impossible de supprimer : des produits sont encore liés à cette catégorie.'], 409);
                        }
                        throw $e;
                    }

                default:
                    nv_json(['error' => 'Méthode non autorisée.'], 405);
            }
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                nv_json(['error' => 'Ce nom de catégorie existe déjà.'], 409);
            }
            nv_json(['error' => 'Erreur base de données.', 'detail' => $e->getMessage()], 500);
        }
    }
}
