window.addEventListener('scroll', () => {
    const arrow = document.querySelector('.arrow');

    if (window.scrollY >= 80 && window.scrollY < 2400) {
        arrow.classList.remove('hidden');
    } else {
        arrow.classList.add('hidden');
    }
});