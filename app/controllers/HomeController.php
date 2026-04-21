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
}
