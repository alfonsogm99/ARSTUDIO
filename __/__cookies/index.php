<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>cookies</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="estilo.css">
</head>

<style>
.cookies {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;

    background: #000;
    color: white;

    font-family: Arial, sans-serif;

    display: flex;
    justify-content: space-between;
    align-items: center;

    padding: 1rem 1.5rem;

    box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.3);
    z-index: 9999;

    backdrop-filter: blur(6px);
}

.cookies__titulo {
    font-size: 1rem;
    margin: 0;
    font-weight: bold;
}

.cookies__texto {
    font-size: 0.9rem;
    margin: 0;
    opacity: 0.8;
}

.cookies__botones {
    display: flex;
    gap: 0.5rem;
}

.cookies__boton {
    background: transparent;
    border: 1px solid white;
    padding: 0.6rem 1rem;
    font-size: 0.9rem;
    color: white;

    border-radius: 6px;

    transition: all 0.2s ease;
    cursor: pointer;
}

/* Botón aceptar */
.cookies__boton--si {
    background: #000;
    color: #fff;
    border: 1px solid white;
}

/* Hover */
.cookies__boton:hover {
    transform: translateY(-2px);
    opacity: 0.9;
}

/* Responsive */
@media (max-width: 600px) {
    .cookies {
        flex-direction: column;
        text-align: center;
        gap: 0.8rem;
    }

    .cookies__botones {
        width: 100%;
        justify-content: center;
    }
}

.cookies {
    animation: slideUp 0.4s ease;
}

@keyframes slideUp {
    from {
        transform: translateY(100%);
    }
    to {
        transform: translateY(0);
    }
}
</style>

<body>

<h1>footer</h1>

    <section class="cookies">
        <h2 class="cookies__titulo">🍪 ¿Aceptas nuestras Cookies?</h2>
        <p class="cookies__texto">Este sitio utiliza cookies para mejorar la experiencia del usuario.</p>
        <div class="cookies__botones">
            <button class="cookies__boton cookies__boton--si">Si</button>
            <button class="cookies__boton cookies__boton--no">No</button>
        </div>
    </section>

    <!-- No borrar, aquí se generarán todas las etiquetas <script> si acepta el usuario -->
    <div id="nuevosScripts"></div>

<footer>
  <a href="privacidad.html">Política de Privacidad</a> |
  <a href="cookies.html">Política de Cookies</a> |
  <a href="terminos.html">Términos y Condiciones</a>
</footer>

<script defer>
        let cookies = () => {
            //======================================================================
            // COOKIES
            //======================================================================

            //-----------------------------------------------------
            // Configuración
            //-----------------------------------------------------
            const urlsScriptsCookies = ['https://analytics.google.com', 'https://facebook.com'];

            function contenidoScriptsCookies () {
                ////////////  ¿Google Analítics? /////////////
                ////////////  ¿Facebook Pixel? /////////////
                ////////////  ¿Admob? /////////////
                ////////////  etc  /////////////
            }


            //-----------------------------------------------------
            // Variables
            //-----------------------------------------------------
            const seccionCookie = document.querySelector('section.cookies');
            const cookieSi = document.querySelector('.cookies__boton--si');
            const cookieNo = document.querySelector('.cookies__boton--no');
            const nuevosScripts = document.querySelector('#nuevosScripts');

            //-----------------------------------------------------
            // Funciones
            //-----------------------------------------------------

            /**
             * Método que oculta la sección de Cookie para siempre
             */
            function ocultarCookie() {
                // Borra la sección de cookies en el HTML
                seccionCookie.remove();
            }

            /**
             * Método que marca las cookies como aceptadas
             */
            function aceptarCookies() {
                // Oculta el HTML de cookies
                ocultarCookie();
                // Guarda que ha aceptado
                localStorage.setItem('cookie', 'true');
                // Tu codigo a ejecutar si aceptan las cookies
                ejecutarSiAcepta();
            }

            /**
             * Método que marca las cookies como denegadas
             */
            function denegarCookies() {
                // Oculta el HTML de cookies
                ocultarCookie();
                // Guarda que ha aceptado
                localStorage.setItem('cookie', 'false');
            }

            /**
             * Método que ejecuta tu código si aceptan las cookies
             */
            function ejecutarSiAcepta() {
                // Crea los <script>
                urlsScriptsCookies.forEach((url) => {
                    const nuevoScript = document.createElement('script');
                    nuevoScript.setAttribute('src', url);
                    nuevosScripts.appendChild(nuevoScript);
                });
                // Lanza los códigos
                contenidoScriptsCookies();
            }

            /**
             * Método que inicia la lógica
             */
            function iniciar() {
                const saved = localStorage.getItem('cookie');

                if (saved === 'true') {
                    ocultarCookie();
                    ejecutarSiAcepta();
                } else if (saved === 'false') {
                    ocultarCookie();
                }
            }

            //-----------------------------------------------------
            // Eventos
            //-----------------------------------------------------
            cookieSi.addEventListener('click',aceptarCookies, false);
            cookieNo.addEventListener('click',denegarCookies, false);

            return {
                iniciar: iniciar
            }
        };

        // Activa el código. Comenta si quieres desactivarlo.
        cookies().iniciar();
</script>
</body>
</html>



