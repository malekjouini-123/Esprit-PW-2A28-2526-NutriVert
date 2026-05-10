<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Commande.php';
require_once __DIR__ . '/../model/StatistiqueVenteProduit.php';

/**
 * Contrôleur API JSON : commandes et lignes.
 */
class CommandeController
{
    private const STATUTS = ['en_attente', 'validee', 'livree', 'annulee'];

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
                    if (isset($_GET['stats']) && (string) $_GET['stats'] === 'produits') {
                        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
                        nv_json($this->buildStatsVentesProduits($limit));
                    }
                    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
                    if ($id > 0) {
                        $detail = $this->findByIdWithLignes($id);
                        if ($detail === null) {
                            nv_json(['error' => 'Commande introuvable.'], 404);
                        }
                        nv_json($detail);
                    }
                    nv_json($this->findAll());

                case 'POST':
                    $in = nv_json_input();
                    if (isset($in['commande_id'], $in['produit_id'])) {
                        $this->handleAddLigne($in);

                        return; // Terminate here after adding line
                    }
                    $this->handleCreate($in);
                    return; // Terminate here after creating commande

                case 'PUT':
                    $ligneId = isset($_GET['ligne_id']) ? (int) $_GET['ligne_id'] : 0;
                    if ($ligneId > 0) {
                        $in = nv_json_input();
                        $q = isset($in['quantite']) ? (int) $in['quantite'] : 0;
                        if ($q < 1 || $q > 9999) {
                            nv_json(['error' => 'Quantité invalide (1–9999).'], 400);
                        }
                        $this->updateLigneQuantite($ligneId, $q);
                        nv_json(['ok' => true]);
                    }
                    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
                    if ($id < 1) {
                        nv_json(['error' => 'Paramètre id manquant.'], 400);
                    }
                    $in = nv_json_input();
                    $parsed = $this->parseCommandeBody($in);
                    if ($parsed === null) {
                        nv_json(['error' => 'nom et statut valides requis.'], 400);
                    }
                    $this->updateCommande($id, $parsed['nom'], $parsed['client_email'], $parsed['statut'], $parsed['type_paiement']);
                    nv_json(['ok' => true]);

                case 'DELETE':
                    $ligneId = isset($_GET['ligne_id']) ? (int) $_GET['ligne_id'] : 0;
                    if ($ligneId > 0) {
                        $this->deleteLigne($ligneId);
                        nv_json(['ok' => true]);
                    }
                    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
                    if ($id < 1) {
                        nv_json(['error' => 'Paramètre id manquant.'], 400);
                    }
                    $this->deleteCommande($id);
                    nv_json(['ok' => true]);

                default:
                    nv_json(['error' => 'Méthode non autorisée.'], 405);
            }
        } catch (InvalidArgumentException $e) {
            nv_json(['error' => $e->getMessage()], 400);
        } catch (PDOException $e) {
            nv_json(['error' => 'Erreur base de données.', 'detail' => $e->getMessage()], 500);
        }
    }

    /**
     * @param array<string,mixed> $in
     */
    private function handleAddLigne(array $in): void
    {
        $cid = (int) $in['commande_id'];
        $pid = (int) $in['produit_id'];
        $q = isset($in['quantite']) ? (int) $in['quantite'] : 1;
        if ($cid < 1 || $pid < 1) {
            nv_json(['error' => 'commande_id et produit_id requis.'], 400);
        }
        if ($q < 1 || $q > 9999) {
            nv_json(['error' => 'Quantité invalide (1–9999).'], 400);
        }
        $this->addOrUpdateLigne($cid, $pid, $q);
        nv_json(['ok' => true]);
    }

    /**
     * @param array<string,mixed> $in
     */
    private function handleCreate(array $in): void
    {
        $parsed = $this->parseCommandeBody($in);
        if ($parsed === null) {
            nv_json(['error' => 'nom (2–180 car.) et statut valides requis.'], 400);
        }
        $commande = (new Commande())
            ->setNom($parsed['nom'])
            ->setClientEmail($parsed['client_email'])
            ->setStatut($parsed['statut'])
            ->setTypePaiement($parsed['type_paiement']);
        $id = $this->createCommande($commande);
        nv_json(['ok' => true, 'id' => $id]);
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function findAll(): array
    {
        $sql = 'SELECT c.id, c.nom, c.date_commande, c.statut, c.client_email, c.total, c.type_paiement,
            (SELECT COUNT(*) FROM ligne_commande lc WHERE lc.commande_id = c.id) AS nb_lignes
            FROM commande c ORDER BY c.date_commande DESC, c.id DESC';
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * @return array{commande: array<string,mixed>, lignes: list<array<string,mixed>>}|null
     */
    private function findByIdWithLignes(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, nom, date_commande, statut, client_email, total, type_paiement FROM commande WHERE id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }
        $lq = $this->pdo->prepare(
            'SELECT lc.id AS ligne_id, lc.produit_id, lc.quantite, lc.prix_unitaire, p.nom AS produit_nom
            FROM ligne_commande lc
            INNER JOIN produit p ON p.id = lc.produit_id
            WHERE lc.commande_id = ?
            ORDER BY lc.id ASC'
        );
        $lq->execute([$id]);
        $lignes = $lq->fetchAll();
        return ['commande' => $row, 'lignes' => $lignes];
    }

    private function createCommande(Commande $commande): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO commande (nom, date_commande, statut, client_email, total, type_paiement) VALUES (?, NOW(), ?, ?, 0, ?)'
        );
        $stmt->execute([$commande->getNom(), $commande->getStatut(), $commande->getClientEmail(), $commande->getTypePaiement()]);
        return (int) $this->pdo->lastInsertId();
    }

    private function updateCommande(int $id, string $nom, ?string $clientEmail, string $statut, ?string $typePaiement): int
    {
        $stmt = $this->pdo->prepare(
            'UPDATE commande SET nom = ?, statut = ?, client_email = ?, type_paiement = ? WHERE id = ?'
        );
        $stmt->execute([$nom, $statut, $clientEmail, $typePaiement, $id]);
        return $stmt->rowCount();
    }

    private function deleteCommande(int $id): void
    {
        $lq = $this->pdo->prepare('SELECT produit_id, quantite FROM ligne_commande WHERE commande_id = ?');
        $lq->execute([$id]);
        $rows = $lq->fetchAll();
        $this->pdo->beginTransaction();
        try {
            foreach ($rows as $r) {
                $rst = $this->pdo->prepare('UPDATE produit SET combien = combien + ? WHERE id = ?');
                $rst->execute([(int) $r['quantite'], (int) $r['produit_id']]);
            }
            $stmt = $this->pdo->prepare('DELETE FROM commande WHERE id = ?');
            $stmt->execute([$id]);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function addOrUpdateLigne(int $commandeId, int $produitId, int $quantite): void
    {
        if ($quantite < 1) {
            throw new InvalidArgumentException('Quantité invalide.');
        }
        $this->pdo->beginTransaction();
        try {
            $pl = $this->pdo->prepare('SELECT prix, promo_pourcent, combien, nom FROM produit WHERE id = ? FOR UPDATE');
            $pl->execute([$produitId]);
            $pr = $pl->fetch();
            if ($pr === false) {
                throw new InvalidArgumentException('Produit introuvable.');
            }
            $prix = nv_prix_apres_promo((float) $pr['prix'], (int) ($pr['promo_pourcent'] ?? 0));
            $stock = (int) $pr['combien'];
            $nomProd = (string) $pr['nom'];
            if ($stock < $quantite) {
                throw new InvalidArgumentException(
                    'Stock insuffisant pour « ' . $nomProd . ' » (disponible : ' . $stock . ').'
                );
            }

            $ex = $this->pdo->prepare(
                'SELECT id, quantite FROM ligne_commande WHERE commande_id = ? AND produit_id = ? FOR UPDATE'
            );
            $ex->execute([$commandeId, $produitId]);
            $line = $ex->fetch();
            if ($line !== false) {
                $newQ = (int) $line['quantite'] + $quantite;
                $up = $this->pdo->prepare('UPDATE ligne_commande SET quantite = ?, prix_unitaire = ? WHERE id = ?');
                $up->execute([$newQ, $prix, (int) $line['id']]);
            } else {
                $ins = $this->pdo->prepare(
                    'INSERT INTO ligne_commande (commande_id, produit_id, quantite, prix_unitaire) VALUES (?,?,?,?)'
                );
                $ins->execute([$commandeId, $produitId, $quantite, $prix]);
            }

            $dec = $this->pdo->prepare('UPDATE produit SET combien = combien - ? WHERE id = ? AND combien >= ?');
            $dec->execute([$quantite, $produitId, $quantite]);
            if ($dec->rowCount() === 0) {
                throw new InvalidArgumentException('Stock insuffisant pour « ' . $nomProd . ' ».');
            }

            $this->recalcTotal($commandeId);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function updateLigneQuantite(int $ligneId, int $quantite): void
    {
        if ($quantite < 1 || $quantite > 9999) {
            throw new InvalidArgumentException('Quantité invalide.');
        }
        $this->pdo->beginTransaction();
        try {
            $st = $this->pdo->prepare(
                'SELECT lc.commande_id, lc.produit_id, lc.quantite AS old_q, p.nom
                FROM ligne_commande lc
                INNER JOIN produit p ON p.id = lc.produit_id
                WHERE lc.id = ? FOR UPDATE'
            );
            $st->execute([$ligneId]);
            $row = $st->fetch();
            if ($row === false) {
                throw new InvalidArgumentException('Ligne introuvable.');
            }
            $cid = (int) $row['commande_id'];
            $pid = (int) $row['produit_id'];
            $oldQ = (int) $row['old_q'];
            $nomProd = (string) $row['nom'];
            $delta = $quantite - $oldQ;

            if ($delta !== 0) {
                $pl = $this->pdo->prepare('SELECT combien FROM produit WHERE id = ? FOR UPDATE');
                $pl->execute([$pid]);
                $p = $pl->fetch();
                if ($p === false) {
                    throw new InvalidArgumentException('Produit introuvable.');
                }
                $stock = (int) $p['combien'];
                if ($delta > 0) {
                    if ($stock < $delta) {
                        throw new InvalidArgumentException(
                            'Stock insuffisant pour « ' . $nomProd . ' » (besoin de +' . $delta . ' unités, stock : ' . $stock . ').'
                        );
                    }
                    $u = $this->pdo->prepare('UPDATE produit SET combien = combien - ? WHERE id = ? AND combien >= ?');
                    $u->execute([$delta, $pid, $delta]);
                    if ($u->rowCount() === 0) {
                        throw new InvalidArgumentException('Stock insuffisant pour « ' . $nomProd . ' ».');
                    }
                } else {
                    $u = $this->pdo->prepare('UPDATE produit SET combien = combien + ? WHERE id = ?');
                    $u->execute([-$delta, $pid]);
                }
            }

            $up = $this->pdo->prepare('UPDATE ligne_commande SET quantite = ? WHERE id = ?');
            $up->execute([$quantite, $ligneId]);
            $this->recalcTotal($cid);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function deleteLigne(int $ligneId): void
    {
        $this->pdo->beginTransaction();
        try {
            $st = $this->pdo->prepare(
                'SELECT commande_id, produit_id, quantite FROM ligne_commande WHERE id = ?'
            );
            $st->execute([$ligneId]);
            $row = $st->fetch();
            if ($row === false) {
                $this->pdo->commit();
                return;
            }
            $cid = (int) $row['commande_id'];
            $pid = (int) $row['produit_id'];
            $qty = (int) $row['quantite'];
            $del = $this->pdo->prepare('DELETE FROM ligne_commande WHERE id = ?');
            $del->execute([$ligneId]);
            $rst = $this->pdo->prepare('UPDATE produit SET combien = combien + ? WHERE id = ?');
            $rst->execute([$qty, $pid]);
            $this->recalcTotal($cid);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function recalcTotal(int $commandeId): void
    {
        $s = $this->pdo->prepare(
            'UPDATE commande SET total = (
                SELECT COALESCE(SUM(quantite * prix_unitaire), 0) FROM ligne_commande WHERE commande_id = ?
            ) WHERE id = ?'
        );
        $s->execute([$commandeId, $commandeId]);
    }

    private function parseCommandeBody(array $in): ?array
    {
        $nom = isset($in['nom']) ? trim((string) $in['nom']) : 'Ma commande';
        $statut = isset($in['statut']) ? trim((string) $in['statut']) : 'en_attente';
        if (!in_array($statut, self::STATUTS, true)) {
            $statut = 'en_attente';
        }
        $emailRaw = isset($in['client_email']) ? trim((string) $in['client_email']) : '';
        $clientEmail = $emailRaw === '' ? null : $emailRaw;
        $typePaiement = isset($in['type_paiement']) ? trim((string) $in['type_paiement']) : null;
        return [
            'nom' => $nom,
            'client_email' => $clientEmail,
            'statut' => $statut,
            'type_paiement' => $typePaiement
        ];
    }

    /**
     * @return array{
     *   resume: array{produits_distincts:int,quantites_vendues:int,chiffre_affaires:float},
     *   top_produits: list<array<string,mixed>>
     * }
     */
    private function buildStatsVentesProduits(int $limit): array
    {
        $limit = max(1, min(100, $limit));
        $stats = $this->findStatsVentesProduits($limit);

        $produitsDistincts = count($stats);
        $quantitesVendues = 0;
        $chiffreAffaires = 0.0;
        $rows = [];

        foreach ($stats as $stat) {
            $quantitesVendues += $stat->getQuantiteVendue();
            $chiffreAffaires += $stat->getChiffreAffaires();
            $rows[] = [
                'produit_id' => $stat->getProduitId(),
                'produit_nom' => $stat->getProduitNom(),
                'categorie_nom' => $stat->getCategorieNom(),
                'quantite_vendue' => $stat->getQuantiteVendue(),
                'nb_commandes' => $stat->getNbCommandes(),
                'chiffre_affaires' => round($stat->getChiffreAffaires(), 2),
                'derniere_vente' => $stat->getDerniereVente(),
            ];
        }

        return [
            'resume' => [
                'produits_distincts' => $produitsDistincts,
                'quantites_vendues' => $quantitesVendues,
                'chiffre_affaires' => round($chiffreAffaires, 2),
            ],
            'top_produits' => $rows,
        ];
    }

    /**
     * @return list<StatistiqueVenteProduit>
     */
    private function findStatsVentesProduits(int $limit): array
    {
        $sql = 'SELECT p.id AS produit_id, p.nom AS produit_nom, c.nom AS categorie_nom,
            SUM(lc.quantite) AS quantite_vendue,
            COUNT(DISTINCT lc.commande_id) AS nb_commandes,
            SUM(lc.quantite * lc.prix_unitaire) AS chiffre_affaires,
            MAX(co.date_commande) AS derniere_vente
            FROM ligne_commande lc
            INNER JOIN produit p ON p.id = lc.produit_id
            INNER JOIN categorie c ON c.id = p.categorie_id
            INNER JOIN commande co ON co.id = lc.commande_id
            GROUP BY p.id, p.nom, c.nom
            ORDER BY quantite_vendue DESC, chiffre_affaires DESC, p.nom ASC
            LIMIT ' . (int) $limit;

        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll();

        $stats = [];
        foreach ($rows as $row) {
            $stats[] = (new StatistiqueVenteProduit())
                ->setProduitId((int) $row['produit_id'])
                ->setProduitNom((string) $row['produit_nom'])
                ->setCategorieNom((string) $row['categorie_nom'])
                ->setQuantiteVendue((int) $row['quantite_vendue'])
                ->setNbCommandes((int) $row['nb_commandes'])
                ->setChiffreAffaires((float) $row['chiffre_affaires'])
                ->setDerniereVente(isset($row['derniere_vente']) ? (string) $row['derniere_vente'] : null);
        }

        return $stats;
    }
}
