function toggleHorario(){
    const el = document.getElementById('panel_horario');
    if(!el) return;

    const isHidden = window.getComputedStyle(el).display === 'none';

    el.style.display = isHidden ? 'block' : 'none';

    el.scrollIntoView({
        behavior: 'smooth'
    });
}

function closeOtherPanels(exceptId){
    const panels = ['panel_ausencias', 'panel_vip'];

    panels.forEach(id => {
        if(id !== exceptId){
            const el = document.getElementById(id);
            if(el) el.style.display = 'none';
        }
    });
}

function toggleAusencias(){
    const el = document.getElementById('panel_ausencias');

    const isHidden = el.style.display === 'none' || !el.style.display;

    if(isHidden){
        closeOtherPanels('panel_ausencias');
        el.style.display = 'block';
    } else {
        el.style.display = 'none';
    }

    el.scrollIntoView({ behavior: 'smooth' });
}

function toggleVIP(){
    const el = document.getElementById('panel_vip');

    const isHidden = el.style.display === 'none' || !el.style.display;

    if(isHidden){
        closeOtherPanels('panel_vip');
        el.style.display = 'block';
    } else {
        el.style.display = 'none';
    }

    el.scrollIntoView({ behavior: 'smooth' });
}

function toggleNuevoHorario(value){

    const modo = document.getElementById('modo_horario');
    const editando = document.getElementById('id_general_editando');

    const panel = document.getElementById('nuevo_horario');

    const bloqueAusencias = document.getElementById('bloque_ausencias');
    const panelAusencias = document.getElementById('panel_ausencias');

    const btnGuardarHorario = document.getElementById('guardar_horario');
    const btnGuardarCambios = document.getElementById('btn_guardar_cambios');
    const btnEliminarHorario = document.getElementById('btn_eliminar_horario');
    const btnEditarHorario = document.getElementById('btn_editar_horario');

    if(!panel) return;

    if(value === "new"){

        if(modo) modo.value = "new";
        if(editando) editando.value = "";

        // Mostrar formulario automáticamente
        panel.style.display = "block";

        // Limpiar formulario
        document.getElementById("nombre_horario").value = "";

        document.querySelectorAll('#nuevo_horario input[type="time"]').forEach(el => {
            el.value = "";
        });

        // Ocultar ausencias/VIP
        if(bloqueAusencias){
            bloqueAusencias.style.display = "none";
        }

        if(panelAusencias){
            panelAusencias.style.display = "none";
        }

        // Botones
        if(btnGuardarHorario){
            btnGuardarHorario.style.display = "inline-block";
        }

        if(btnGuardarCambios){
            btnGuardarCambios.style.display = "none";
        }

        if(btnEliminarHorario){
            btnEliminarHorario.style.display = "none";
        }

    } else {

        if(modo) modo.value = "edit";
        if(editando) editando.value = value;

        panel.style.display = "none";

        if(bloqueAusencias){
            bloqueAusencias.style.display = "block";
        }

        if(panelAusencias){
            panelAusencias.style.display = "none";
        }

        if(btnGuardarHorario){
            btnGuardarHorario.style.display = "none";
        }

        if(btnGuardarCambios){
            btnGuardarCambios.style.display = "none";
        }

        if(btnEliminarHorario){
            btnEliminarHorario.style.display = "inline-block";
        }

        panel.scrollIntoView({
            behavior: 'smooth'
        });
    }

    panel.scrollIntoView({
        behavior: 'smooth'
    });
}


// estado inicial al cargar página
document.addEventListener("DOMContentLoaded", () => {

    const select = document.getElementById("select_horario");

    if (!select) return;

    select.addEventListener("change", () => {

        const value = select.value;

        document.getElementById("id_general_editando").value =
            value !== "new" ? value : "";

    });

});


function guardarHorario() {

    const nombre = document.getElementById('nombre_horario').value;

    const dias = [
        'lunes','martes','miercoles',
        'jueves','viernes','sabado','domingo'
    ];

    const horas = {};

    dias.forEach(dia => {
        horas[dia] = {
            inicio: document.getElementById(`inicio_${dia}`).value,
            fin: document.getElementById(`fin_${dia}`).value,
            pausa_inicio: document.getElementById(`pausa_inicio_${dia}`).value,
            pausa_fin: document.getElementById(`pausa_fin_${dia}`).value
        };
    });

    const modo = document.getElementById('modo_horario').value;
    const idEditar = document.getElementById('id_general_editando').value;

    // Crear formulario
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'nuevo_horario.php';

    const inputGuardar = document.createElement('input');
    inputGuardar.type = 'hidden';
    inputGuardar.name = 'guardar_horario';
    inputGuardar.value = '1';
    form.appendChild(inputGuardar);

    const inputModo = document.createElement('input');
    inputModo.type = 'hidden';
    inputModo.name = 'modo_horario';
    inputModo.value = modo;
    form.appendChild(inputModo);

    const inputId = document.createElement('input');
    inputId.type = 'hidden';
    inputId.name = 'id_general_editando';
    inputId.value = idEditar;
    form.appendChild(inputId);

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

function prepararHorario(){

    const select = document.getElementById("select_horario");
    const action = document.getElementById("action");

    if(!select || !action) return true;

    if(select.value !== "new"){

        action.value = "cambiar_horario";

    } else {

        action.value = "";

    }

    return true;
}