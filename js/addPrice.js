let counter = 1000;

document.getElementById('addPrice').addEventListener('click', () => {
    const container = document.querySelector('.mainsets_form');

    const id = 'new_' + Date.now() + '_' + Math.floor(Math.random() * 1000);

    const div = document.createElement('div');
    div.className = "price-row";

    div.innerHTML = `
        <label class="switch">
            <input type="checkbox" name="precios_items[${id}][visible]" checked>
            <span class="slider"></span>
        </label>

        <input type="text" name="precios_items[${id}][titulo]" placeholder="Título">

        <input type="number" step="0.5" name="precios_items[${id}][precio]" value="0">

        <button type="button" onclick="this.parentElement.remove()">X</button>
    `;

    container.insertBefore(div, container.querySelector('button[type="submit"]'));
});

function markDelete(btn) {
    const row = btn.parentElement;
    row.querySelector('input[name*="[deleted]"]').value = "1";
    row.style.display = "none";
}