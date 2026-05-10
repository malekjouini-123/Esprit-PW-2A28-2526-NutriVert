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
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        try {
            switch ($method) {
                case 'GET':
                    $cat = isset($_GET['categorie_id']) ? (int) $_GET['categorie_id'] : 0;
                    $rows = $this->findAll($cat > 0 ? $cat : null);
                    nv_json($rows);

                case 'POST':
                    $data = $this->parseBody(nv_json_input(), null);
                    if ($data === null) {
                        nv_json(['error' => 'categorie_id, nom et quantité stock (combien) valides requis.'], 400);
                    }
                    $produit = (new Produit())
                        ->setCategorieId($data['categorie_id'])
                        ->setNom($data['nom'])
                        ->setLabel($data['label'])
                        ->setProducteur($data['producteur'])
                        ->setPrix($data['prix'])
                        ->setPromoPourcent($data['promo_pourcent'])
                        ->setEmpreinteCo2($data['empreinte_co2'])
                        ->setCombien($data['combien'])
                        ->setIcone($data['icone']);
                    $id = $this->createProduit($produit);
                    nv_json(['ok' => true, 'id' => $id]);

                case 'PUT':
                    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
                    if ($id < 1) {
                        nv_json(['error' => 'Paramètre id manquant.'], 400);
                    }
                    $mergePromo = $this->readPromoPourcent($id);
                    $data = $this->parseBody(nv_json_input(), $mergePromo);
                    if ($data === null) {
                        nv_json(['error' => 'categorie_id, nom et quantité stock (combien) valides requis.'], 400);
                    }
                    $produit = (new Produit())
                        ->setId($id)
                        ->setCategorieId($data['categorie_id'])
                        ->setNom($data['nom'])
                        ->setLabel($data['label'])
                        ->setProducteur($data['producteur'])
                        ->setPrix($data['prix'])
                        ->setPromoPourcent($data['promo_pourcent'])
                        ->setEmpreinteCo2($data['empreinte_co2'])
                        ->setCombien($data['combien'])
                        ->setIcone($data['icone']);
                    $affected = $this->updateProduit($produit);
                    nv_json(['ok' => true, 'affected' => $affected]);

                case 'DELETE':
                    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
                    if ($id < 1) {
                        nv_json(['error' => 'Paramètre id manquant.'], 400);
                    }
                    try {
                        $this->deleteProduit($id);
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
     * @return array{categorie_id:int,nom:string,label:?string,producteur:?string,prix:float,promo_pourcent:int,empreinte_co2:?float,combien:int,icone:string}|null
     */
    private function parseBody(array $in, ?int $promoMergeIfMissing): ?array
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

        if (array_key_exists('promo_pourcent', $in)) {
            $promo = nv_promo_pourcent_normalize((int) $in['promo_pourcent']);
        } elseif ($promoMergeIfMissing !== null) {
            $promo = nv_promo_pourcent_normalize($promoMergeIfMissing);
        } else {
            $promo = 0;
        }

        return [
            'categorie_id' => $cid,
            'nom' => $nom,
            'label' => $label,
            'producteur' => $producteur,
            'prix' => $prix,
            'promo_pourcent' => $promo,
            'empreinte_co2' => $co2,
            'combien' => $combien,
            'icone' => $icone,
        ];
    }

    private function readPromoPourcent(int $produitId): int
    {
        $stmt = $this->pdo->prepare('SELECT promo_pourcent FROM produit WHERE id = ?');
        $stmt->execute([$produitId]);
        $row = $stmt->fetch();
        if ($row === false) {
            return 0;
        }
        return nv_promo_pourcent_normalize((int) ($row['promo_pourcent'] ?? 0));
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function findAll(?int $categorieId = null): array
    {
        $sqlList = 'SELECT p.id, p.categorie_id, p.nom, p.label, p.producteur, p.prix, p.promo_pourcent, p.empreinte_co2, p.combien, p.icone,
            c.nom AS categorie_nom
            FROM produit p
            INNER JOIN categorie c ON c.id = p.categorie_id';
        if ($categorieId !== null && $categorieId > 0) {
            $stmt = $this->pdo->prepare($sqlList . ' WHERE p.categorie_id = ? ORDER BY p.nom ASC');
            $stmt->execute([$categorieId]);
            $rows = $stmt->fetchAll();
        } else {
            $stmt = $this->pdo->query($sqlList . ' ORDER BY c.nom ASC, p.nom ASC');
            $rows = $stmt->fetchAll();
        }
        foreach ($rows as &$row) {
            $pct = nv_promo_pourcent_normalize((int) ($row['promo_pourcent'] ?? 0));
            $row['promo_pourcent'] = $pct;
            $row['prix_affiche'] = nv_prix_apres_promo((float) $row['prix'], $pct);
        }
        unset($row);
        return $rows;
    }

    private function createProduit(Produit $produit): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO produit (categorie_id, nom, label, producteur, prix, promo_pourcent, empreinte_co2, combien, icone) VALUES (?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $produit->getCategorieId(),
            $produit->getNom(),
            $produit->getLabel(),
            $produit->getProducteur(),
            $produit->getPrix(),
            $produit->getPromoPourcent(),
            $produit->getEmpreinteCo2(),
            $produit->getCombien(),
            $produit->getIcone(),
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    private function updateProduit(Produit $produit): int
    {
        $stmt = $this->pdo->prepare(
            'UPDATE produit SET categorie_id=?, nom=?, label=?, producteur=?, prix=?, promo_pourcent=?, empreinte_co2=?, combien=?, icone=? WHERE id=?'
        );
        $stmt->execute([
            $produit->getCategorieId(),
            $produit->getNom(),
            $produit->getLabel(),
            $produit->getProducteur(),
            $produit->getPrix(),
            $produit->getPromoPourcent(),
            $produit->getEmpreinteCo2(),
            $produit->getCombien(),
            $produit->getIcone(),
            (int) $produit->getId(),
        ]);
        return $stmt->rowCount();
    }

    private function deleteProduit(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM produit WHERE id = ?');
        $stmt->execute([$id]);
    }
}
