function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll("input[required], select[required], textarea[required]");
    let isValid = true;
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = "red";
            isValid = false;
        } else {
            input.style.borderColor = "#ddd";
        }
    });
    return isValid;
}

function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
            document.getElementById(previewId).style.display = "block";
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function confirmDelete(message) {
    return confirm(message || "A jeni i sigurt?");
}
