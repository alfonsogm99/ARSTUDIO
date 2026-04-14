document.addEventListener("DOMContentLoaded", () => {
    const button = document.querySelector(".menu i,#menu_bar i");
    const logo = document.querySelector(".scaler img");

    button.addEventListener("click", () => {
        document.body.classList.toggle("light-mode");
        button.classList.toggle("fas");
        button.classList.toggle("far");

        const theme = document.body.classList.contains("light-mode") ? "light" : "dark";

        if (logo) {
            logo.src = theme === "light"
                ? "../img/arstudio_negativo.png"
                : "../img/arstudio.png";
        }

        fetch('../../admin/lightmode.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'theme=' + theme
        })
        .then(res => res.json())
        .then(data => console.log('Respuesta PHP:', data))
        .catch(err => console.error(err));
    });
});