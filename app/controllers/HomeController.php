<?php
class HomeController extends BaseController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index(): void
    {
        $search = trim($_GET['search'] ?? '');

        if ($search !== '') {
            $stmt = $this->pdo->prepare('SELECT * FROM recette WHERE titre LIKE :titre ORDER BY id_recette DESC');
            $stmt->execute([':titre' => '%' . $search . '%']);
            $recettes = $stmt->fetchAll();
        } else {
            $stmt = $this->pdo->query('SELECT * FROM recette ORDER BY id_recette DESC');
            $recettes = $stmt->fetchAll();
        }

        $this->render('front/home', [
            'pageTitle' => 'NutriVert | FrontOffice',
            'recettes' => $recettes,
            'search' => $search,
        ]);
    }

    public function recetteDetail(int $id): void
    {
        $stmtRecette = $this->pdo->prepare('SELECT * FROM recette WHERE id_recette = :id');
        $stmtRecette->execute([':id' => $id]);
        $recette = $stmtRecette->fetch();

        if (!$recette) {
            $this->redirect('index.php?page=front_home');
            return;
        }

        $stmtInstructions = $this->pdo->prepare('SELECT * FROM instruction WHERE id_recette = :id_recette ORDER BY id_instruction ASC');
        $stmtInstructions->execute([':id_recette' => $id]);
        $instructions = $stmtInstructions->fetchAll();

        $this->render('front/recette_detail', [
            'pageTitle' => 'Détail recette',
            'recette' => $recette,
            'instructions' => $instructions,
        ]);
    }

    public function saveRecette(int $id): void
    {
        if ($id <= 0) {
            $this->redirect('index.php?page=front_home');
            return;
        }

        $stmt = $this->pdo->prepare('SELECT id_recette FROM recette WHERE id_recette = :id');
        $stmt->execute([':id' => $id]);
        $recette = $stmt->fetch();

        if (!$recette) {
            $this->redirect('index.php?page=front_home');
            return;
        }

        if (!isset($_SESSION['recettes_enregistrees']) || !is_array($_SESSION['recettes_enregistrees'])) {
            $_SESSION['recettes_enregistrees'] = [];
        }

        if (!in_array($id, $_SESSION['recettes_enregistrees'], true)) {
            $_SESSION['recettes_enregistrees'][] = $id;
        }

        $this->redirect('index.php?page=front_favoris');
    }

    public function favoris(): void
    {
        $ids = $_SESSION['recettes_enregistrees'] ?? [];
        $ids = array_values(array_filter(array_map('intval', $ids), function ($id) {
            return $id > 0;
        }));

        $recettes = [];

        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->pdo->prepare('SELECT * FROM recette WHERE id_recette IN (' . $placeholders . ') ORDER BY titre ASC');
            $stmt->execute($ids);
            $recettes = $stmt->fetchAll();
        }

        $this->render('front/favoris', [
            'pageTitle' => 'Mes recettes enregistrées | NutriVert',
            'recettes' => $recettes,
        ]);
    }

    public function removeFavori(int $id): void
    {
        if (isset($_SESSION['recettes_enregistrees']) && is_array($_SESSION['recettes_enregistrees'])) {
            $_SESSION['recettes_enregistrees'] = array_values(array_filter($_SESSION['recettes_enregistrees'], function ($savedId) use ($id) {
                return (int) $savedId !== $id;
            }));
        }

        $this->redirect('index.php?page=front_favoris');
    }
}
