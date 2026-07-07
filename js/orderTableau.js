let direccion = {};

function ordenarTabla(th, columna) {

    const tabla = th.closest('table');
    const tbody = tabla.querySelector('tbody');
    const filas = Array.from(tbody.querySelectorAll('tr'));
    const cabeceras = tabla.querySelectorAll('th');

    // Clave única para cada tabla + columna
    const clave = `${tabla.id}_${columna}`;

    // Eliminar indicadores de esta tabla
    cabeceras.forEach(cabecera => {
        cabecera.classList.remove('ordenado', 'asc', 'desc');
    });

    // Alternar dirección
    direccion[clave] = !direccion[clave];

    filas.sort((a, b) => {

        let valorA = a.cells[columna].innerText.trim();
        let valorB = b.cells[columna].innerText.trim();

        // Intentar comparar como números
        const numA = parseFloat(
            valorA.replace(/[^\d.,-]/g, '').replace(',', '.')
        );

        const numB = parseFloat(
            valorB.replace(/[^\d.,-]/g, '').replace(',', '.')
        );

        if (!isNaN(numA) && !isNaN(numB)) {
            return direccion[clave]
                ? numA - numB
                : numB - numA;
        }

        // Comparación de texto
        return direccion[clave]
            ? valorA.localeCompare(valorB, 'es', { sensitivity: 'base' })
            : valorB.localeCompare(valorA, 'es', { sensitivity: 'base' });
    });

    // Reinsertar filas ordenadas
    tbody.innerHTML = '';
    filas.forEach(fila => tbody.appendChild(fila));

    // Marcar columna ordenada
    th.classList.add(
        'ordenado',
        direccion[clave] ? 'asc' : 'desc'
    );
}