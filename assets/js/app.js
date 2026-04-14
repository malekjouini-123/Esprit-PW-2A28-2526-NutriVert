document.addEventListener("DOMContentLoaded", function () {
    // Small client-side checks for required and numeric fields.
    document.querySelectorAll("form.needs-validation").forEach(function (form) {
        form.addEventListener("submit", function (event) {
            const requiredFields = form.querySelectorAll("[data-required]");
            const numberFields = form.querySelectorAll("[data-number]");
            let valid = true;
            let message = "";

            requiredFields.forEach(function (field) {
                if (!field.value || !field.value.toString().trim()) {
                    valid = false;
                    message = "Please fill in all required fields.";
                }
            });

            numberFields.forEach(function (field) {
                const value = field.value.trim();
                if (!/^\d+$/.test(value) || parseInt(value, 10) <= 0) {
                    valid = false;
                    message = "Numeric fields must contain positive numbers.";
                }
            });

            if (!valid) {
                event.preventDefault();
                alert(message);
            }
        });
    });

    document.querySelectorAll("[data-confirm]").forEach(function (link) {
        link.addEventListener("click", function (event) {
            if (!confirm("Are you sure you want to delete this item?")) {
                event.preventDefault();
            }
        });
    });
});
