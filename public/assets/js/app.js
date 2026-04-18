
let ingredients = [];
document.addEventListener('DOMContentLoaded', () => {
const btn = document.getElementById('addIngredientBtn');
if(!btn) return;

btn.addEventListener('click', () => {
const nom = document.getElementById('nom_produit').value.trim();
const quantite = document.getElementById('quantite').value.trim();
const image = document.getElementById('image').value.trim();

if (!nom || !quantite) { alert("Remplir les champs"); return; }

ingredients.push({ nom_produit: nom, quantite: quantite, image: image || 'https://placehold.co/60x60' });
renderIngredients();

document.getElementById('nom_produit').value='';
document.getElementById('quantite').value='';
document.getElementById('image').value='';
});
});

function renderIngredients(){
const table=document.getElementById('ingredientsTable');
if(!table) return;
table.innerHTML='';
ingredients.forEach((ing,i)=>{
table.innerHTML+=`<tr>
<td>${ing.nom_produit}</td>
<td>${ing.quantite}</td>
<td><img src="${ing.image}" width="40"></td>
<td><button onclick="removeIngredient(${i})">X</button></td>
</tr>`;
});
document.getElementById('ingredients_json').value=JSON.stringify(ingredients);
}
function removeIngredient(i){
ingredients.splice(i,1);
renderIngredients();
}
