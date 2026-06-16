// Fecha objetivo
const fechaObjetivo = new Date("2026-06-16 10:11:00").getTime();

const contador = document.getElementById("contador");

function actualizarContador() {

    const ahora = new Date().getTime();
    const diferencia = fechaObjetivo - ahora;

    // Cálculos
    const dias = Math.floor(diferencia / (1000 * 60 * 60 * 24));

    const horas = Math.floor(
    (diferencia % (1000 * 60 * 60 * 24))
    / (1000 * 60 * 60)
    );

    const minutos = Math.floor(
    (diferencia % (1000 * 60 * 60))
    / (1000 * 60)
    );

    const segundos = Math.floor(
    (diferencia % (1000 * 60))
    / 1000
    );

    contador.innerHTML =
    `${dias}d ${horas}h ${minutos}m ${segundos}s`;

    // Finalizar
    if (diferencia < 0) {
    clearInterval(intervalo);
    contador.innerHTML = 'ARSTUDIO<br>¡ya se encuentra abierto!<br> <div class="map-card"><iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3161.5199181579137!2d-3.8194895253215675!3d37.5899210227887!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd6ddffecdb58f3b%3A0xb7db56329283d542!2sAR%20STUDIO!5e0!3m2!1ses!2ses!4v1780051583948!5m2!1ses!2ses" allowfullscreen="" loading="lazy"> </iframe></div>';
    }
}

// Actualiza cada segundo
const intervalo = setInterval(actualizarContador, 1000);

// Ejecutar inmediatamente
actualizarContador();