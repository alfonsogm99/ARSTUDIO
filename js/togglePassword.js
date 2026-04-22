function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);
    if (!input) return;

    const isPassword = input.type === "password";

    input.type = isPassword ? "text" : "password";

    icon.classList.toggle("fa-eye", !isPassword);
    icon.classList.toggle("fa-eye-slash", isPassword);
}