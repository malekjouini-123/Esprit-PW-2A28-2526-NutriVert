let ingredients = [];

document.addEventListener('DOMContentLoaded', function () {
    console.log('app.js chargé');

    // ====== GESTION INGREDIENTS ======
    const btn = document.getElementById('addIngredientBtn');

    if (btn) {
        btn.addEventListener('click', function () {
            const nomField = document.getElementById('nom_produit');
            const quantiteField = document.getElementById('quantite');
            const imageField = document.getElementById('image');

            if (!nomField || !quantiteField || !imageField) {
                return;
            }

            const nom = nomField.value.trim();
            const quantite = quantiteField.value.trim();
            const image = imageField.value.trim();

            if (!nom || !quantite) {
                alert('Remplir les champs');
                return;
            }

            ingredients.push({
                nom_produit: nom,
                quantite: quantite,
                image: image || 'https://placehold.co/60x60'
            });

            renderIngredients();

            nomField.value = '';
            quantiteField.value = '';
            imageField.value = '';
        });
    }

    // ====== GESTION SUPPRESSION ======
    const modal = document.getElementById('deleteModal');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const cancelBtn = document.getElementById('cancelDelete');
    const deleteButtons = document.querySelectorAll('.open-delete-modal');

    console.log('modal:', modal);
    console.log('confirmBtn:', confirmBtn);
    console.log('cancelBtn:', cancelBtn);
    console.log('deleteButtons:', deleteButtons.length);

    if (modal && confirmBtn && cancelBtn && deleteButtons.length > 0) {
        deleteButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const deleteUrl = this.getAttribute('data-url');
                console.log('click supprimer', deleteUrl);

                if (deleteUrl) {
                    confirmBtn.setAttribute('href', deleteUrl);
                    modal.classList.add('show');
                }
            });
        });

        cancelBtn.addEventListener('click', function () {
            modal.classList.remove('show');
            confirmBtn.setAttribute('href', '#');
        });

        window.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.classList.remove('show');
                confirmBtn.setAttribute('href', '#');
            }
        });
    }
});

function renderIngredients() {
    const table = document.getElementById('ingredientsTable');
    const jsonField = document.getElementById('ingredients_json');

    if (!table || !jsonField) {
        return;
    }

    table.innerHTML = '';

    ingredients.forEach(function (ing, i) {
        table.innerHTML += `
            <tr>
                <td>${ing.nom_produit}</td>
                <td>${ing.quantite}</td>
                <td><img src="${ing.image}" width="40" alt="ingredient"></td>
                <td><button type="button" onclick="removeIngredient(${i})">X</button></td>
            </tr>
        `;
    });

    jsonField.value = JSON.stringify(ingredients);
}

function removeIngredient(i) {
    ingredients.splice(i, 1);
    renderIngredients();
}