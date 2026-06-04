const clienteInput = document.getElementById('cliente_input');
const idClienteInput = document.getElementById('id_cliente');
const suggestionsContainer = document.getElementById('cliente_suggestions');

let debounceTimeout;

function ajustarPosicion(){
    const rect = clienteInput.getBoundingClientRect();
    suggestionsContainer.style.top = (rect.bottom + window.scrollY) + 'px';
    suggestionsContainer.style.left = (rect.left + window.scrollX) + 'px';
    suggestionsContainer.style.width = rect.width + 'px';
}

function mostrarLista() {
    suggestionsContainer.style.display = 'block';
}

function ocultarLista() {
    suggestionsContainer.style.display = 'none';
    suggestionsContainer.innerHTML = '';
}

clienteInput.addEventListener('input', function(){
    ocultarLista();

    const query = this.value.trim();
    if(query.length < 2) return;

    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        ajustarPosicion();
        fetch('buscar_cliente.php?q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                suggestionsContainer.innerHTML = '';
                if(data.length === 0){
                    const div = document.createElement('div');
                    div.style.padding = '8px';

                    div.innerHTML = `
                        <p style="margin-bottom:5px;">No se encontró ningún cliente</p>
                        <button type="button" id="crear_cliente_btn">Nuevo usuario</button>
                    `;

                    suggestionsContainer.appendChild(div);

                    document.getElementById('crear_cliente_btn').addEventListener('click', () => {
                        const nombre = encodeURIComponent(clienteInput.value.trim());
                        const fecha = "<?= $fecha ?>";
                        const hora = "<?= $hora_inicial ?>";

                        window.location.href = `nuevo_usuario.php?nombre=${nombre}&fecha=${fecha}&hora=${hora}`;
                    });

                    mostrarLista();
                    return;
                }

                data.forEach(c => {
                    const div = document.createElement('div');
                    div.textContent = c.nombre + " (DNI: " + c.dni + ")"; // se ve el DNI en la lista
                    div.dataset.id = c.id_cliente;
                    div.addEventListener('click', () => {
                        clienteInput.value = c.nombre; // SOLO el nombre en el input
                        idClienteInput.value = c.id_cliente; // el hidden guarda el ID real
                        ocultarLista();
                    });
                    suggestionsContainer.appendChild(div);
                });
                mostrarLista();
            });
    }, 300);
});

document.querySelector('form').addEventListener('submit', function(e){
    if(!idClienteInput.value){
        e.preventDefault();
        alert('Selecciona un cliente válido de la lista.');
        clienteInput.focus();
    }
});

document.addEventListener('click', function(e){
    if(!clienteInput.contains(e.target) && !suggestionsContainer.contains(e.target)){
        ocultarLista();
    }
});