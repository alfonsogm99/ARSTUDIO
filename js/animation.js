const mainImage = document.querySelector('.scaler img');
const firstSection = document.querySelector('main section:first-of-type');
const layers = document.querySelectorAll('.grid > .layer');

const startScale = 2; // imagen grande al inicio
const endScale = 1;   // tamaño final en grid
const triggerPoint = 0.5; // porcentaje del scroll donde termina la animación

window.addEventListener('scroll', () => {
  const scrollTop = window.scrollY;
  const sectionTop = firstSection.offsetTop;
  const sectionHeight = firstSection.offsetHeight;

  // progreso normalizado 0→1 durante la animación
  let progress = (scrollTop - sectionTop) / (sectionHeight * triggerPoint);
  progress = Math.min(Math.max(progress, 0), 1);

  // ------------------------
  // Reducir imagen principal
  // ------------------------
  const scale = startScale - (startScale - endScale) * progress;
  mainImage.style.transform = `translate(-50%, -50%) scale(${scale})`;

  // ------------------------
  // Mostrar capas de imágenes
  // ------------------------
  layers.forEach((layer, index) => {
    const delay = index * 0.1;
    const layerProgress = Math.max(0, (progress - delay) / (1 - delay));
    const layerScale = Math.min(layerProgress, 1);
    const layerOpacity = Math.min(layerProgress, 1);

    layer.style.transform = `scale(${layerScale})`;
    layer.style.opacity = layerOpacity;
  });
});