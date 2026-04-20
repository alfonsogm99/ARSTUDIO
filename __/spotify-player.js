const player = document.getElementById('spotify-player');
const footer = document.getElementById('footer');
const main = document.querySelector('.main'); // div principal

function resetPlayerStyles() {
    player.style.position = '';
    player.style.top = '';
    player.style.bottom = '';
    player.style.left = '';
    player.style.right = '';
    player.style.transform = '';
    player.style.margin = '';
    player.style.width = '';
    player.style.maxWidth = '';
}

function updatePlayerPosition() {
    const windowWidth = window.innerWidth;
    const footerTop = footer.getBoundingClientRect().top + window.scrollY;
    const playerHeight = player.offsetHeight;
    const scrollBottom = window.scrollY + window.innerHeight;

    if (windowWidth > 497) {

        resetPlayerStyles()
        // Desktop: esquina derecha y sigue scroll
        main.style.marginBottom = playerHeight + 40 + 'px'; // margen extra para desktop

        if (scrollBottom < footerTop) {
            player.style.position = 'fixed';
            player.style.bottom = '20px';
            player.style.left = '';
            player.style.right = '20px';
            player.style.top = '';
            player.style.transform = 'none';
        } else {
            player.style.position = 'absolute';
            player.style.top = (footerTop - playerHeight - 20) - 20 + 'px';
            player.style.bottom = '';
            player.style.left = '';
            player.style.right = '20px';
            player.style.transform = 'none';
        }
    } else {

        resetPlayerStyles()
        
        // Mobile: centrar horizontal y altura del footer
        main.style.marginBottom = '40px'; // solo espacio extra

        player.style.position = 'sticky';
        player.style.top = '20px';   // distancia desde arriba al hacer scroll
        player.style.left = 'auto';
        player.style.right = 'auto';
        player.style.transform = 'none';
        player.style.width = '90%';
        player.style.maxWidth = '300px';
        player.style.margin = '20px auto';
    }
}

// Ejecutar al hacer scroll y resize
window.addEventListener('scroll', updatePlayerPosition);
window.addEventListener('resize', updatePlayerPosition);

// Ejecutar al cargar
updatePlayerPosition();
