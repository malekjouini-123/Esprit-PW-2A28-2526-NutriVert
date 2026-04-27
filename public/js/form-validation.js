document.addEventListener('DOMContentLoaded', function () {
    const formConfigs = [
        { id: 'loginForm',    validator: validateLogin    },
        { id: 'registerForm', validator: validateRegister },
        { id: 'profileForm',  validator: validateProfile  },
        { id: 'addUserForm',  validator: validateAddUser  },
        { id: 'editUserFormElement', validator: validateEditUser }
    ];

    formConfigs.forEach(config => {
        const form = document.getElementById(config.id);
        if (!form) return;

        // ── Validation en temps réel sur chaque champ ──────────────────────
        attachRealTimeValidation(form, config.validator);

        // ── Validation finale à la soumission ──────────────────────────────
        form.addEventListener('submit', function (event) {
            clearAllFieldErrors(form);
            const hasError = config.validator(form);
            if (hasError) event.preventDefault();
        });
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
//  VALIDATION EN TEMPS RÉEL
// ═══════════════════════════════════════════════════════════════════════════════

function attachRealTimeValidation(form, validatorFn) {
    const fields = form.querySelectorAll('input, select, textarea');

    fields.forEach(field => {
        // Valider quand l'utilisateur quitte le champ (blur)
        field.addEventListener('blur', function () {
            clearAllFieldErrors(form);
            validatorFn(form);
        });

        // Effacer l'erreur du champ dès que l'utilisateur retape (input)
        field.addEventListener('input', function () {
            clearFieldError(form, field.name);
            // Re-valider la confirmation mot de passe si on modifie password
            if (field.name === 'password') {
                const confirm = form.querySelector('[name="confirm_password"]');
                if (confirm && confirm.value.trim() !== '') {
                    clearFieldError(form, 'confirm_password');
                    const pwd  = field.value.trim();
                    const conf = confirm.value.trim();
                    if (pwd !== conf) {
                        showFieldError(form, 'confirm_password', 'Les mots de passe ne correspondent pas.');
                    }
                }
            }
        });
    });
}

// ═══════════════════════════════════════════════════════════════════════════════
//  UTILITAIRES AFFICHAGE ERREURS
// ═══════════════════════════════════════════════════════════════════════════════

function getFieldValue(form, name) {
    const field = form.querySelector(`[name="${name}"]`);
    return field ? field.value.trim() : '';
}

function isEmailValid(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showFieldError(form, fieldName, message) {
    const field = form.querySelector(`[name="${fieldName}"]`);
    if (!field) return;

    // Ne pas dupliquer le message
    if (field.parentElement.querySelector('.field-error-message')) return;

    const errorEl = document.createElement('div');
    errorEl.className = 'field-error-message';
    errorEl.textContent = message;

    field.classList.add('input-error');
    field.insertAdjacentElement('afterend', errorEl);
}

function clearFieldError(form, fieldName) {
    const field = form.querySelector(`[name="${fieldName}"]`);
    if (!field) return;
    field.classList.remove('input-error');
    const err = field.parentElement.querySelector('.field-error-message');
    if (err) err.remove();
}

function clearAllFieldErrors(form) {
    form.querySelectorAll('.field-error-message').forEach(el => el.remove());
    form.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
}

// ═══════════════════════════════════════════════════════════════════════════════
//  VALIDATEURS PAR FORMULAIRE
// ═══════════════════════════════════════════════════════════════════════════════

function validateLogin(form) {
    let hasError = false;
    const email    = getFieldValue(form, 'email');
    const password = getFieldValue(form, 'password');

    if (!email) {
        showFieldError(form, 'email', 'Le champ email est obligatoire.');
        hasError = true;
    } else if (!isEmailValid(email)) {
        showFieldError(form, 'email', 'Veuillez saisir une adresse email valide.');
        hasError = true;
    }

    if (!password) {
        showFieldError(form, 'password', 'Le mot de passe est obligatoire.');
        hasError = true;
    } else if (password.length < 6) {
        showFieldError(form, 'password', 'Le mot de passe doit contenir au moins 6 caractères.');
        hasError = true;
    }

    return hasError;
}

function validateRegister(form) {
    let hasError = false;
    const nom             = getFieldValue(form, 'nom');
    const prenom          = getFieldValue(form, 'prenom');
    const email           = getFieldValue(form, 'email');
    const password        = getFieldValue(form, 'password');
    const confirmPassword = getFieldValue(form, 'confirm_password');

    if (!nom) {
        showFieldError(form, 'nom', 'Le nom est obligatoire.');
        hasError = true;
    }
    if (!prenom) {
        showFieldError(form, 'prenom', 'Le prénom est obligatoire.');
        hasError = true;
    }
    if (!email) {
        showFieldError(form, 'email', "L'adresse email est obligatoire.");
        hasError = true;
    } else if (!isEmailValid(email)) {
        showFieldError(form, 'email', 'Veuillez saisir une adresse email valide.');
        hasError = true;
    }
    if (!password) {
        showFieldError(form, 'password', 'Le mot de passe est obligatoire.');
        hasError = true;
    } else if (password.length < 6) {
        showFieldError(form, 'password', 'Le mot de passe doit contenir au moins 6 caractères.');
        hasError = true;
    }
    if (!confirmPassword) {
        showFieldError(form, 'confirm_password', 'Veuillez confirmer le mot de passe.');
        hasError = true;
    } else if (password !== confirmPassword) {
        showFieldError(form, 'confirm_password', 'Les mots de passe ne correspondent pas.');
        hasError = true;
    }

    return hasError;
}

function validateProfile(form) {
    let hasError   = false;
    const poids    = getFieldValue(form, 'poids');
    const taille   = getFieldValue(form, 'taille');
    const objectif = getFieldValue(form, 'objectif');
    const regime   = getFieldValue(form, 'regime');

    if (poids !== '') {
        const poidsValue = Number(poids);
        if (isNaN(poidsValue) || poidsValue <= 0) {
            showFieldError(form, 'poids', 'Le poids doit être un nombre positif.');
            hasError = true;
        }
    }
    if (taille !== '') {
        const tailleValue = Number(taille);
        if (isNaN(tailleValue) || tailleValue <= 0) {
            showFieldError(form, 'taille', 'La taille doit être un nombre positif.');
            hasError = true;
        }
    }
    if (objectif !== '' && objectif.length < 3) {
        showFieldError(form, 'objectif', "L'objectif nutritionnel est trop court.");
        hasError = true;
    }
    if (regime !== '' && regime.length < 3) {
        showFieldError(form, 'regime', 'Le régime alimentaire est trop court.');
        hasError = true;
    }

    return hasError;
}

function validateAddUser(form) {
    let hasError   = false;
    const nom      = getFieldValue(form, 'nom');
    const prenom   = getFieldValue(form, 'prenom');
    const email    = getFieldValue(form, 'email');
    const password = getFieldValue(form, 'password');
    const role     = getFieldValue(form, 'role');

    if (!nom) {
        showFieldError(form, 'nom', 'Le nom est obligatoire.');
        hasError = true;
    }
    if (!prenom) {
        showFieldError(form, 'prenom', 'Le prénom est obligatoire.');
        hasError = true;
    }
    if (!email) {
        showFieldError(form, 'email', "L'adresse email est obligatoire.");
        hasError = true;
    } else if (!isEmailValid(email)) {
        showFieldError(form, 'email', 'Veuillez saisir une adresse email valide.');
        hasError = true;
    }
    if (!password) {
        showFieldError(form, 'password', 'Le mot de passe est obligatoire.');
        hasError = true;
    } else if (password.length < 6) {
        showFieldError(form, 'password', 'Le mot de passe doit contenir au moins 6 caractères.');
        hasError = true;
    }
    if (!role) {
        showFieldError(form, 'role', 'Le rôle est obligatoire.');
        hasError = true;
    }

    return hasError;
}

function validateEditUser(form) {
    let hasError   = false;
    const nom      = getFieldValue(form, 'nom');
    const prenom   = getFieldValue(form, 'prenom');
    const email    = getFieldValue(form, 'email');
    const password = getFieldValue(form, 'password');
    const role     = getFieldValue(form, 'role');

    if (!nom) {
        showFieldError(form, 'nom', 'Le nom est obligatoire.');
        hasError = true;
    }
    if (!prenom) {
        showFieldError(form, 'prenom', 'Le prénom est obligatoire.');
        hasError = true;
    }
    if (!email) {
        showFieldError(form, 'email', "L'adresse email est obligatoire.");
        hasError = true;
    } else if (!isEmailValid(email)) {
        showFieldError(form, 'email', 'Veuillez saisir une adresse email valide.');
        hasError = true;
    }
    // Le mot de passe est optionnel lors de la modification
    if (password && password.length < 6) {
        showFieldError(form, 'password', 'Le mot de passe doit contenir au moins 6 caractères.');
        hasError = true;
    }
    if (!role) {
        showFieldError(form, 'role', 'Le rôle est obligatoire.');
        hasError = true;
    }

    return hasError;
}