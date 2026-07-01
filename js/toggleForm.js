function toggleForm(data = null){

    const form = document.getElementById('formServicio');

    form.style.display = 'block';

    if(data){
        document.querySelector('[name="id_servicio"]').value = data.id_servicio;
        document.querySelector('[name="nombre"]').value = data.nombre;
        document.querySelector('[name="precio"]').value = data.precio;
        document.querySelector('[name="duracion"]').value = data.duracion;

        document.querySelector('button[type="submit"]').textContent = 'Actualizar';

    } else {
        document.querySelector('[name="id_servicio"]').value = '';
        document.querySelector('[name="nombre"]').value = '';
        document.querySelector('[name="precio"]').value = '';
        document.querySelector('[name="duracion"]').value = '30';

        document.querySelector('button[type="submit"]').textContent = 'Crear';
    }

    form.scrollIntoView({
        behavior: 'smooth'
    });
}

function toggleForm_2(data){

    const form = document.getElementById('formCliente');

    form.style.display = 'block';

    document.querySelector('[name="id_cliente"]').value = data.id_cliente;
    document.querySelector('[name="nombre"]').value = data.nombre || '';
    document.querySelector('[name="email"]').value = data.email || '';
    document.querySelector('[name="prefijo_pais"]').value = data.prefijo_pais || '';
    document.querySelector('[name="telefono"]').value = data.telefono || '';
    document.querySelector('[name="observaciones"]').value = data.observaciones || '';

    form.scrollIntoView({
        behavior: 'smooth'
    });
}