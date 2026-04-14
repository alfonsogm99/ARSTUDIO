const stars = document.querySelector(".stars");
const speedFactor = 0.1;

window.addEventListener("scroll", () => {

    const scrollTop = window.scrollY;
    const maxScroll = document.body.scrollHeight - window.innerHeight;

    const progress = scrollTop / maxScroll;
    
    const rotation = progress * 360 * speedFactor;

    stars.style.transform = `rotate(${rotation}deg)`;
});