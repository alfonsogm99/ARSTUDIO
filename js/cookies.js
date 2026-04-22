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