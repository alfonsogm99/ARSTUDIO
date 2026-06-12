<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../web/img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=ABeeZee:ital@0;1&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="./css/style.css">
    <title>menu_toggle + logo_scrolled</title>
</head>
<body>

    <!--     
    
    <section>
        <div id="frame"></div>
    </section> 
    
    -->

    <div class="buttons">
        <button class="menu-toggle active" aria-label="Cerrar menú"></button>
        <button class="dark-toggle" aria-label="Modo oscuro"><i class="far fa-moon"></i></button>
    </div>
    

    <div class="logo_loading_maintenance">
        <img src="../../web/img/letters.png" class="logo_letters">
        <div class="stars"></div>
    </div>

    <div class="menu">
        <ul>
            <!-- Home -->
            <h3><a href="#">Home</a></h3>

            <!-- Dirección -->
            <li>
                <h3>Dirección</h3>
                <ul>
                    <li>
                        <a href="https://maps.app.goo.gl/sEEChLabXYi2mggc8">
                    C/Trav. de Farjas 8, 23150 Valdepeñas de Jaén
                    </a>
                    </li>
                </ul>
            </li>

            <!-- Productos -->
            <li>
            <h3>Productos</h3>
            <ul>
                <li><a href="#">+2000€ en máquinas</a></li>
                <li><a href="#">Kemei de Pozin</a></li>
                <li><a href="#">Lameculos de Octyl</a></li>
            </ul>
            </li>

            <!-- Formación -->
            <li>
            <h3>Formación</h3>
            <ul>
                <li>
                <a href="https://www.instagram.com/juanca_unique/">
                    @juanca_unique <i class="fab fa-instagram"></i>
                </a>
                </li>
                <li>
                <a href="https://www.instagram.com/blesson_school/">
                    @blesson_school <i class="fab fa-instagram"></i>
                </a>
                </li>
            </ul>
            </li>

            <!-- Redes sociales -->
            <li>
            <h3>Redes sociales</h3>
            <ul>
                <li>
                <a href="https://www.instagram.com/aaleexrp/">
                    @aaleexrp <i class="fab fa-instagram"></i>
                </a>
                </li>
                <li>
                <a href="#">
                    alejandritoyatusabe <i class="fab fa-facebook"></i>
                </a>
                </li>
                <li>
                <a href="#">
                    AR BARBER <i class="fas fa-xmark"></i>
                </a>
                </li>
            </ul>
            </li>
        </ul>
        </div>

        <hr>

        <footer class="version">
            <p>v.0.9</p>
        </footer>

        <footer class="copyright">
            <p>© 2026 alfonsogm</p>
        </footer>


    <script>
        const toggle = document.querySelector('.menu-toggle');
        const menu = document.querySelector('.menu');

        toggle.addEventListener('click', () => {
        toggle.classList.toggle('active');
        menu.classList.toggle('open');
        });
    </script>

    <script>
        // Selección de elementos
        const darkToggle = document.querySelector('.dark-toggle');
        const body = document.body;
        const logo = document.querySelector('.logo_letters');

        // Inicializar estado según localStorage
        const savedMode = localStorage.getItem("dark-mode");
        if(savedMode === "dark"){
        body.classList.add("dark-mode");
        logo.src = "../../web/img/letters_white.png";
        darkToggle.innerHTML = '<i class="far fa-sun"></i>';
        } else {
        logo.src = "../../web/img/letters.png";
        darkToggle.innerHTML = '<i class="far fa-moon"></i>';
        }

        // Función toggle
        darkToggle.addEventListener('click', () => {
        body.classList.toggle("dark-mode");

        if(body.classList.contains("dark-mode")){
            logo.src = "../../web/img/letters_white.png";
            darkToggle.innerHTML = '<i class="far fa-sun"></i>';
            localStorage.setItem("dark-mode","dark");
        } else {
            logo.src = "../../web/img/letters.png";
            darkToggle.innerHTML = '<i class="far fa-moon"></i>';
            localStorage.setItem("dark-mode","light");
        }
        });
    </script>

    <script src="./js/animation.js"></script>
</body>
</html>