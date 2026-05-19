function toggleHorario(){
    const el = document.getElementById('panel_horario');
    if(!el) return;

    const isHidden = window.getComputedStyle(el).display === 'none';

    el.style.display = isHidden ? 'block' : 'none';
}

function toggleAusencias(){
    const el = document.getElementById('panel_ausencias');
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function toggleNuevoHorario(value){

    const panel = document.getElementById('nuevo_horario');

    const bloqueAusencias = document.getElementById('bloque_ausencias');

    const panelAusencias = document.getElementById('panel_ausencias');

    const btnGuardarHorario = document.getElementById('guardar_horario');

    const btnGuardarCambios = document.getElementById('btn_guardar_cambios');

    const btnEliminarHorario = document.getElementById('btn_eliminar_horario');

    if(!panel) return;

    if(value === "new"){

        // Mostrar panel crear horario
        panel.style.display = "block";

        // Ocultar ausencias
        if(bloqueAusencias){
            bloqueAusencias.style.display = "none";
        }

        // Cerrar panel ausencias
        if(panelAusencias){
            panelAusencias.style.display = "none";
        }

        // Mostrar botón guardar nuevo horario
        if(btnGuardarHorario){
            btnGuardarHorario.style.display = "inline-block";
        }

        // Ocultar guardar cambios
        if(btnGuardarCambios){
            btnGuardarCambios.style.display = "none";
        }

        // Ocultar eliminar horario
        if(btnEliminarHorario){
            btnEliminarHorario.style.display = "none";
        }

    } else {

        // Ocultar panel nuevo horario
        panel.style.display = "none";

        // Mostrar bloque ausencias
        if(bloqueAusencias){
            bloqueAusencias.style.display = "block";
        }

        // Ocultar panel ausencias
        if(panelAusencias){
            panelAusencias.style.display = "none";
        }

        // Ocultar guardar nuevo horario
        if(btnGuardarHorario){
            btnGuardarHorario.style.display = "none";
        }

        // Mostrar guardar cambios
        if(btnGuardarCambios){
            btnGuardarCambios.style.display = "inline-block";
        }

        // Mostrar eliminar horario
        if(btnEliminarHorario){
            btnEliminarHorario.style.display = "inline-block";
        }
    }
}

// estado inicial al cargar página
document.addEventListener("DOMContentLoaded", () => {
    const select = document.getElementById("select_horario");
    if(select){
        toggleNuevoHorario(select.value);
    }
});


function guardarHorario() {
    const nombre = document.getElementById('nombre_horario').value;
    const dias = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];
    const horas = {};

    dias.forEach(dia => {
        horas[dia] = {
            inicio: document.getElementById(`inicio_${dia}`).value,
            fin: document.getElementById(`fin_${dia}`).value,
            pausa_inicio: document.getElementById(`pausa_inicio_${dia}`).value,
            pausa_fin: document.getElementById(`pausa_fin_${dia}`).value
        };
    });

    // Crear formulario dinámico
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'nuevo_horario.php';

    const inputGuardar = document.createElement('input');
    inputGuardar.type = 'hidden';
    inputGuardar.name = 'guardar_horario';
    inputGuardar.value = '1';
    form.appendChild(inputGuardar);

    const inputNombre = document.createElement('input');
    inputNombre.type = 'hidden';
    inputNombre.name = 'nombre_horario';
    inputNombre.value = nombre;
    form.appendChild(inputNombre);

    const inputHoras = document.createElement('input');
    inputHoras.type = 'hidden';
    inputHoras.name = 'horas';
    inputHoras.value = JSON.stringify(horas);
    form.appendChild(inputHoras);

    document.body.appendChild(form);
    form.submit();
}


function actualizarDuracion(select) {
    const opcion = select.options[select.selectedIndex];
    const duracionMin = opcion.getAttribute('data-duracion');
    if(duracionMin){
        const horas = Math.floor(duracionMin / 60);
        const minutos = duracionMin % 60;
        document.getElementById('horario_duracion').innerText = `Duración: ${horas}h ${minutos}m`;
    } else {
        document.getElementById('horario_duracion').innerText = '';
    }
}