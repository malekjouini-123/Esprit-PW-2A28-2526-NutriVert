<?php
class HomeController extends BaseController
{
    private Recette $recetteModel;
    private Instruction $instructionModel;

    public function __construct(Recette $recetteModel, Instruction $instructionModel)
    {
        $this->recetteModel = $recetteModel;
        $this->instructionModel = $instructionModel;
    }

public function index(): void
{
    $search = trim($_GET['search'] ?? '');

    if ($search !== '') {
        $recettes = $this->recetteModel->searchByTitre($search);
    } else {
        $recettes = $this->recetteModel->all();
    }

    $this->render('front/home', [
        'pageTitle' => 'NutriVert | FrontOffice',
        'recettes' => $recettes,
        'search' => $search,
    ]);
}

    public function recetteDetail(int $id): void
    {
        $recette = $this->recetteModel->find($id);

        if (!$recette) {
            $this->redirect('index.php?page=front_home');
            return;
        }

        $this->render('front/recette_detail', [
            'pageTitle' => 'Détail recette',
            'recette' => $recette,
            'instructions' => $this->instructionModel->byRecette($id),
        ]);
    }
}