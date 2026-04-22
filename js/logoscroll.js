const stars = document.querySelector(".logscroll_str");
const speedFactor = 0.5;

window.addEventListener("scroll", () => {

    const scrollTop = window.scrollY;
    const maxScroll = document.body.scrollHeight - window.innerHeight;

    const progress = scrollTop / maxScroll;
    
    const rotation = progress * 360 * speedFactor;

    stars.style.transform = `rotate(${rotation}deg)`;
});