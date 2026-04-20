/**
 * Nutrivert — Smart Form Validation
 * Auto-attaches to all forms by detecting field names.
 * No data-attributes or HTML changes required.
 */
(function () {
  'use strict';

  // ─── Rule Definitions (by field name) ───────────────────────
  const RULES = {
    title:            { required: true, minLength: 3, label: 'Title' },
    description:      { required: true, label: 'Description' },
    duration_weeks:   { required: true, positiveInt: true, label: 'Duration' },
    difficulty_level: { required: true, label: 'Difficulty' },
    coaching_id:      { required: true, label: 'Program' },
    name:             { required: true, minLength: 2, label: 'Name' },
    sets:             { required: true, positiveInt: true, label: 'Sets' },
    reps:             { required: true, positiveInt: true, label: 'Reps' },
    rest_time:        { required: true, label: 'Rest time' },
  };

  // ─── Validate a single field ────────────────────────────────
  function validate(field) {
    const name = field.name;
    const rule = RULES[name];
    if (!rule) return ''; // no rule → always valid

    const value = field.value.trim();

    if (rule.required && value === '') {
      return `${rule.label} is required.`;
    }

    if (rule.minLength && value.length < rule.minLength) {
      return `${rule.label} must be at least ${rule.minLength} characters.`;
    }

    if (rule.positiveInt) {
      const num = Number(value);
      if (!Number.isInteger(num) || num <= 0) {
        return `${rule.label} must be a positive number.`;
      }
    }

    return ''; // valid
  }

  // ─── UI Helpers ─────────────────────────────────────────────

  function getOrCreateMessage(field) {
    const group = field.closest('.form-group');
    if (!group) return null;
    let msg = group.querySelector('.field-error');
    if (!msg) {
      msg = document.createElement('span');
      msg.className = 'field-error';
      // Insert right after the field itself
      field.parentNode.insertBefore(msg, field.nextSibling);
    }
    return msg;
  }

  function showError(field, text) {
    field.classList.remove('is-valid');
    field.classList.add('is-invalid');
    const msg = getOrCreateMessage(field);
    if (msg) {
      msg.textContent = text;
      msg.style.display = 'block';
    }
  }

  function showValid(field) {
    field.classList.remove('is-invalid');
    field.classList.add('is-valid');
    const msg = getOrCreateMessage(field);
    if (msg) {
      msg.textContent = '';
      msg.style.display = 'none';
    }
  }

  function resetField(field) {
    field.classList.remove('is-invalid', 'is-valid');
    const msg = getOrCreateMessage(field);
    if (msg) {
      msg.textContent = '';
      msg.style.display = 'none';
    }
  }

  // ─── Run validation on a field, return true if valid ────────
  function validateField(field, force) {
    const rule = RULES[field.name];
    if (!rule) return true;

    // Don't validate pristine fields until submit (force = true)
    if (!force && !field.dataset.touched) return true;

    const error = validate(field);
    if (error) {
      showError(field, error);
      return false;
    }

    // Only show green if user has interacted
    if (field.dataset.touched) {
      showValid(field);
    }
    return true;
  }

  // ─── Attach to a form ──────────────────────────────────────
  function attachValidation(form) {
    // Collect all validatable fields
    const fields = Array.from(form.elements).filter(el => RULES[el.name]);
    if (fields.length === 0) return; // no validatable fields → skip

    // Mark form as JS-validated (disables native popups, keeps fallback)
    form.setAttribute('novalidate', '');

    fields.forEach(field => {
      // On blur: mark touched, validate
      field.addEventListener('blur', () => {
        field.dataset.touched = '1';
        validateField(field, false);
      });

      // On input: real-time re-validation (only if already touched)
      field.addEventListener('input', () => {
        if (field.dataset.touched) {
          validateField(field, false);
        }
      });

      // Selects: also listen to change
      if (field.tagName === 'SELECT') {
        field.addEventListener('change', () => {
          field.dataset.touched = '1';
          validateField(field, false);
        });
      }
    });

    // On submit: validate all, block if any invalid
    form.addEventListener('submit', (e) => {
      let allValid = true;
      let firstInvalid = null;

      fields.forEach(field => {
        field.dataset.touched = '1';
        const valid = validateField(field, true);
        if (!valid && allValid) {
          allValid = false;
          firstInvalid = field;
        }
      });

      if (!allValid) {
        e.preventDefault();
        // Smooth scroll to first error
        if (firstInvalid) {
          firstInvalid.focus({ preventScroll: true });
          firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      }
    });
  }

  // ─── Inject CSS for validation states ───────────────────────
  function injectStyles() {
    if (document.getElementById('nv-validate-css')) return;
    const style = document.createElement('style');
    style.id = 'nv-validate-css';
    style.textContent = `
      /* Error state */
      .form-input.is-invalid,
      .form-select.is-invalid,
      .form-textarea.is-invalid {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
      }

      /* Valid state */
      .form-input.is-valid,
      .form-select.is-valid,
      .form-textarea.is-valid {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
      }

      /* Error message */
      .field-error {
        display: none;
        color: #ef4444;
        font-size: 0.8rem;
        font-weight: 600;
        margin-top: 0.4rem;
        padding-left: 0.2rem;
        animation: fieldErrorIn 0.25s ease;
      }

      @keyframes fieldErrorIn {
        from { opacity: 0; transform: translateY(-4px); }
        to   { opacity: 1; transform: translateY(0); }
      }
    `;
    document.head.appendChild(style);
  }

  // ─── Init ───────────────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', () => {
    injectStyles();
    document.querySelectorAll('form[method="post"]').forEach(attachValidation);
  });

})();
