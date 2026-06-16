

<style>
body {
  margin: 0;
  background: black;
  overflow: hidden;
}

.scene {
  width: 100vw;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
}

.ring {
  position: relative;
  width: 160px;
  height: 160px;
  animation: spin 5s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.dot {
  position: absolute;
  width: 14px;
  height: 14px;
  background: white;
  border-radius: 50%;
  top: 50%;
  left: 50%;
  transform-origin: 0 0;
  opacity: 1;
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.dot.hide {
  opacity: 0;
  transform: scale(0);
}
</style>


<div class="scene">
  <div class="ring" id="ring">
    <div class="dot"></div>
    <div class="dot"></div>
    <div class="dot"></div>
    <div class="dot"></div>
    <div class="dot"></div>
    <div class="dot"></div>
    <div class="dot"></div>
    <div class="dot"></div>
    <div class="dot"></div>
  </div>
</div>

<script>
const dots = document.querySelectorAll(".dot");

const radius = 70;
const total = dots.length;

// estado de vida
const alive = Array(total).fill(true);

// colocar en círculo (ángulo base)
const baseAngles = dots.forEach((dot, i) => {
  const angle = (i / total) * Math.PI * 2;
  dot.dataset.angle = angle;
});

// animación continua
let rotation = 0;

// 🔥 ESTE ES EL PUNTO LÍMITE (abajo centro)
const LIMIT_ANGLE = Math.PI / 2; // 90° = abajo (dependiendo del sistema)

function animate() {
  rotation += 0.02; // velocidad

  dots.forEach((dot, i) => {
    if (!alive[i]) return;

    const baseAngle = parseFloat(dot.dataset.angle);
    const angle = baseAngle + rotation;

    const x = Math.cos(angle) * radius;
    const y = Math.sin(angle) * radius;

    dot.style.transform = `translate(${x}px, ${y}px)`;

    // 🔥 DETECCIÓN DE PASO POR EL PUNTO LÍMITE (ABAJO)
    // cuando el ángulo cruza zona inferior
    const normalized = (angle % (Math.PI * 2) + Math.PI * 2) % (Math.PI * 2);

    // zona inferior (180° hacia abajo)
    const isAtBottomZone =
      normalized > (Math.PI / 2 - 0.15) &&
      normalized < (Math.PI / 2 + 0.15);

    // dirección derecha → izquierda ya implícita en el giro

    if (isAtBottomZone && alive[i]) {
      alive[i] = false;

      // desaparece cuando cruza el punto inferior
      dot.classList.add("hide");
    }
  });

  requestAnimationFrame(animate);
}

animate();
</script>