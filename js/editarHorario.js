function editarAusencia(id, fecha, inicio, fin, tipo, motivo){

    document.getElementById("modo").value = "editar";
    document.getElementById("id_bloqueo").value = id;

    document.getElementById("fecha").value = fecha;
    document.getElementById("hora_inicio").value = inicio;
    document.getElementById("hora_fin").value = fin;
    document.getElementById("tipo").value = tipo;
    document.getElementById("motivo").value = motivo;

    document.getElementById("btn_guardar").textContent = "Guardar cambios";

    document.getElementById("formAusencia")
        .scrollIntoView({behavior: "smooth"});
}

function editarVIP(id, fecha, inicio, fin, motivo){

    document.getElementById("vip_action").value = "vip_edit";
    document.getElementById("id_reserva").value = id;

    document.getElementById("vip_fecha").value = fecha;
    document.getElementById("vip_inicio").value = inicio;
    document.getElementById("vip_fin").value = fin;
    document.getElementById("vip_motivo").value = motivo;

    document.getElementById("btn_vip_guardar").textContent = "Guardar cambios";

    document.getElementById("formVIP")
        .scrollIntoView({behavior: "smooth"});
}


function editarHorario(id) {

    document.getElementById('modo_horario').value = 'edit';
    document.getElementById("id_general_editando").value = id;

    document.getElementById('nuevo_horario').style.display = 'block';

    const horario = horariosData[id];
    if (!horario) return;

    document.getElementById('nombre_horario').value = horario.nombre_general;

    document.querySelectorAll('#nuevo_horario input[type="time"]').forEach(el => {
        el.value = '';
    });

    horario.dias.forEach(dia => {
        console.log(dia);
        const d = dia.dia_semana;

        const set = (idEl, val) => {
            const el = document.getElementById(idEl);
            if (!el) return;

            // Tratar 00:00 y 00:00:00 como vacío
            if (
                val === null ||
                val === undefined ||
                val === '' ||
                val === '00:00' ||
                val === '00:00:00'
            ) {
                el.value = '';
                return;
            }

            el.value = val.substring(0, 5);
        };

        set(`inicio_${d}`, dia.hora_inicio);
        set(`fin_${d}`, dia.hora_fin);
        set(`pausa_inicio_${d}`, dia.pausa_inicio);
        set(`pausa_fin_${d}`, dia.pausa_fin);
    });
}


document.addEventListener("DOMContentLoaded", () => {
    const select = document.getElementById("select_horario");

    if (!select) return;

    const handleChange = () => {
        const value = select.value;
        toggleNuevoHorario(value);

        document.getElementById("id_general_editando").value =
            value !== "new" ? value : "";
    };

    select.addEventListener("change", handleChange);
    handleChange();
});