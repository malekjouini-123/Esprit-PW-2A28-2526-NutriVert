document.addEventListener('DOMContentLoaded', function () {
    const deleteModal = document.getElementById('deleteModal');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const cancelDelete = document.getElementById('cancelDelete');

    document.querySelectorAll('.open-delete-modal').forEach(function (button) {
        button.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            if (confirmDeleteBtn && url) {
                confirmDeleteBtn.setAttribute('href', url);
            }
            if (deleteModal) {
                deleteModal.classList.add('show');
            }
        });
    });

    if (cancelDelete) {
        cancelDelete.addEventListener('click', function () {
            if (deleteModal) {
                deleteModal.classList.remove('show');
            }
        });
    }

    if (deleteModal) {
        deleteModal.addEventListener('click', function (e) {
            if (e.target === deleteModal) {
                deleteModal.classList.remove('show');
            }
        });
    }

    const recetteForms = document.querySelectorAll('.validate-recette-form');
    recetteForms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            let valid = true;
            const titre = document.getElementById('titre');
            const objectif = document.getElementById('objectif');
            const regime = document.getElementById('regime');
            const duree = document.getElementById('duree');

            [titre, objectif, regime, duree].forEach(function (field) {
                if (field && field.nextElementSibling) {
                    field.nextElementSibling.textContent = '';
                }
            });

            if (titre && titre.value.trim().length < 3) {
                titre.nextElementSibling.textContent = 'Minimum 3 caractères';
                valid = false;
            }

            if (objectif && objectif.value.trim().length < 5) {
                objectif.nextElementSibling.textContent = 'Minimum 5 caractères';
                valid = false;
            }

            if (regime && regime.value.trim() === '') {
                regime.nextElementSibling.textContent = 'Champ obligatoire';
                valid = false;
            }

            if (duree && (duree.value.trim() === '' || isNaN(duree.value) || parseInt(duree.value, 10) <= 0)) {
                duree.nextElementSibling.textContent = 'Durée invalide';
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    });

    const instructionForms = document.querySelectorAll('.validate-instruction-form');
    instructionForms.forEach(function (form) {
        form.addEventListener('submit', function (e) {
            let valid = true;
            const idRecette = document.getElementById('id_recette');
            const etape = document.getElementById('etape');
            const description = document.getElementById('description');
            const ingredientProduit = document.getElementById('ingredient_produit');

            [idRecette, etape, description, ingredientProduit].forEach(function (field) {
                if (field && field.nextElementSibling) {
                    field.nextElementSibling.textContent = '';
                }
            });

            if (idRecette && idRecette.value.trim() === '') {
                idRecette.nextElementSibling.textContent = 'Choisir une recette';
                valid = false;
            }

            if (etape && etape.value.trim().length < 2) {
                etape.nextElementSibling.textContent = 'Étape obligatoire';
                valid = false;
            }

            if (description && description.value.trim().length < 5) {
                description.nextElementSibling.textContent = 'Minimum 5 caractères';
                valid = false;
            }

            if (ingredientProduit && ingredientProduit.value.trim() === '') {
                ingredientProduit.nextElementSibling.textContent = 'Champ obligatoire';
                valid = false;
            } else if (ingredientProduit) {
                try {
                    JSON.parse(ingredientProduit.value);
                } catch (err) {
                    ingredientProduit.nextElementSibling.textContent = 'JSON invalide';
                    valid = false;
                }
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    });

    const form = document.getElementById('recetteInstructionForm');
    const addStepBtn = document.getElementById('add-step-btn');
    const stepsContainer = document.getElementById('steps-container');

    function createStepBlock() {
        const stepBlock = document.createElement('div');
        stepBlock.className = 'step-block';
        stepBlock.innerHTML = `
            <label>Étape</label>
            <input type="text" name="etape[]">
            <div class="error-message"></div>

            <label>Description</label>
            <textarea name="description[]"></textarea>
            <div class="error-message"></div>

            <label>Ingrédients / Produits (JSON)</label>
            <textarea name="ingredient_produit[]">[]</textarea>
            <div class="error-message"></div>

            <button type="button" class="btn-remove remove-step-btn">Supprimer cette étape</button>
        `;
        return stepBlock;
    }

    if (addStepBtn && stepsContainer) {
        addStepBtn.addEventListener('click', function () {
            stepsContainer.appendChild(createStepBlock());
        });

        stepsContainer.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-step-btn')) {
                const block = e.target.closest('.step-block');
                if (block) {
                    block.remove();
                }
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            let valid = true;

            document.querySelectorAll('#recetteInstructionForm .error-message').forEach(function (el) {
                el.textContent = '';
            });

            const titre = document.getElementById('titre');
            const objectif = document.getElementById('objectif');
            const regime = document.getElementById('regime');
            const duree = document.getElementById('duree');

            if (titre && titre.value.trim().length < 3) {
                titre.nextElementSibling.textContent = 'Minimum 3 caractères';
                valid = false;
            }

            if (objectif && objectif.value.trim().length < 5) {
                objectif.nextElementSibling.textContent = 'Minimum 5 caractères';
                valid = false;
            }

            if (regime && regime.value.trim() === '') {
                regime.nextElementSibling.textContent = 'Champ obligatoire';
                valid = false;
            }

            if (duree && (duree.value.trim() === '' || isNaN(duree.value) || parseInt(duree.value, 10) <= 0)) {
                duree.nextElementSibling.textContent = 'Durée invalide';
                valid = false;
            }

            const stepBlocks = stepsContainer ? stepsContainer.querySelectorAll('.step-block') : [];

            if (stepBlocks.length === 0) {
                valid = false;
            }

            stepBlocks.forEach(function (block) {
                const etape = block.querySelector('input[name="etape[]"]');
                const description = block.querySelector('textarea[name="description[]"]');
                const ingredient = block.querySelector('textarea[name="ingredient_produit[]"]');
                const errors = block.querySelectorAll('.error-message');

                if (etape && etape.value.trim() === '') {
                    errors[0].textContent = 'Étape obligatoire';
                    valid = false;
                }

                if (description && description.value.trim() === '') {
                    errors[1].textContent = 'Description obligatoire';
                    valid = false;
                }

                if (ingredient && ingredient.value.trim() === '') {
                    errors[2].textContent = 'Champ obligatoire';
                    valid = false;
                } else if (ingredient) {
                    try {
                        JSON.parse(ingredient.value);
                    } catch (err) {
                        errors[2].textContent = 'JSON invalide';
                        valid = false;
                    }
                }
            });

            if (!valid) {
                e.preventDefault();
            }
        });
    }
});