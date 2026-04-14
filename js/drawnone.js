document.querySelectorAll('img').forEach(img => {
img.setAttribute('draggable', 'false');
img.ondragstart = () => false;
img.oncontextmenu = e => e.preventDefault();
});