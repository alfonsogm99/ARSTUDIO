function horaToMin(h) {
    if (!h) return 0;
    const [hh, mm] = h.substring(0,5).split(':');
    return parseInt(hh) * 60 + parseInt(mm);
}

const vipHorarios = window.calendarData.vipHorarios || [];

function getVipDia(fecha) {
    return vipHorarios.filter(v =>
        v.fecha === fecha
    );
}

const userType = window.calendarData.userType;

if (userType === 'cliente') {
    const filter = document.getElementById('floating-filter');
    const toggle = document.getElementById('toggle-filters');

    if (filter) filter.style.display = 'none';
    if (toggle) toggle.style.display = 'none';
}

// =======================
// DATOS DE CITAS DESDE PHP
// =======================
const appointments = window.calendarData.appointments;

const visibleAppointments = appointments;

const appointmentMap = {};
visibleAppointments.forEach(a => appointmentMap[a.id] = a);
const csrf_token = window.calendarData.csrfToken;
let selectedCita = null;

// =======================
// ELEMENTOS DEL MODAL
// =======================
const modal = document.getElementById('modal_cita');
const modalTitle = document.getElementById('modal_title');
const modalInfo = document.getElementById('modal_info');
const btnPrimary = document.getElementById('btn_primary');
const btnDelete = document.getElementById('btn_delete');

function abrirModal() { modal.style.display = 'flex'; }
function cerrarModal() { modal.style.display = 'none'; }
modal.onclick = e => { if(e.target.id === 'modal_cita') cerrarModal(); }

const normalizarHora = h => h.substring(0,5);

const horarios = window.calendarData.horariosDisponibles;
//console.log("📅 HORARIOS COMPLETOS DESDE PHP:", horarios);

const bloqueos = window.calendarData.bloqueos || [];

function getBloqueosDia(fecha) {
    return bloqueos.filter(b =>
        b.fecha === fecha
    );
}

function normalizar(texto) {
    return (texto || "")
        .toString()
        .toLowerCase()
        .trim()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/\s+/g, " ");
}

function getDiaSemanaFromYMD(fecha) {
    const [y, m, d] = fecha.split('-').map(Number);

    const date = new Date(y, m - 1, d);

    const dias = [
        'domingo',
        'lunes',
        'martes',
        'miercoles',
        'jueves',
        'viernes',
        'sabado'
    ];

    return dias[date.getDay()];
}

function estaDiaCompletamenteBloqueado(fecha) {
    const horario = getHorarioDia(fecha);

    if (!horario) return true; // si no hay horario, consideramos bloqueado

    const inicioMin = parseHora(horario.hora_inicio) ?? 9*60;
    const finMin = parseHora(horario.hora_fin) ?? 20*60;
    const pausaInicio = parseHora(horario.pausa_inicio);
    const pausaFin = parseHora(horario.pausa_fin);

    const bloqueosDia = getBloqueosDia(fecha);
    const apps = visibleAppointments.filter(a => a.fecha === fecha);

    // recorrer slots cada 15 min
    for (let min = inicioMin; min < finMin; min += 15) {
        // slot dentro de pausa
        if (pausaInicio != null && pausaFin != null && min >= pausaInicio && min < pausaFin) continue;

        // slot ocupado por cita
        const ocupado = apps.some(a => {
            const start = horaToMin(a.hora);
            const end = start + (parseInt(a.duracion) || 0);
            return min >= start && min < end;
        });

        if (ocupado) continue;

        // slot bloqueado por bloqueo
        const bloqueado = bloqueosDia.some(b => {
            const bInicio = parseHora(b.hora_inicio) ?? 0;
            const bFin = parseHora(b.hora_fin) ?? 0;
            const finBloqueo = (bInicio === bFin && bInicio === 0) ? 24*60 : bFin;
            return min >= bInicio && min < finBloqueo;
        });

        if (!ocupado && !bloqueado) {
            // encontramos al menos un slot libre
            return false;
        }
    }

    // si llegamos aquí, todos los slots están ocupados/bloqueados
    return true;
}

function getHorarioDia(fecha) {

    const diaSemana = getDiaSemanaFromYMD(fecha);

    //console.log("🔎 BUSCANDO HORARIO PARA:", {
    //    fecha,
    //    diaSemana
    //});

    for (const gen of horarios) {

        //console.log("➡️ COMPROBANDO HORARIO GENERAL:", gen);

        if (Number(gen.id_barbero) !== Number(window.calendarData.barberoFiltro)) {
            continue;
        }

        if (!gen.dia_semana) continue;

        //console.log("📌 DIA EN BBDD:", gen.dia_semana);

        if (normalizar(gen.dia_semana) === normalizar(diaSemana)) {
            //console.log("✅ MATCH HORARIO ENCONTRADO:", gen);
            return gen;
        }
    }

    console.warn("🚨 SIN HORARIO PARA:", fecha);
    return null;
}

function parseHora(h) {
    if (!h) return null;
    const [hh, mm] = h.split(':');
    return parseInt(hh) * 60 + parseInt(mm);
}

function resetModalButtons() {
    btnPrimary.onclick = null;
    btnDelete.onclick = null;

    btnPrimary.disabled = false;
    btnDelete.disabled = false;

    btnPrimary.style.display = "inline-block";
    btnDelete.style.display = "inline-block";

    btnPrimary.textContent = "";
    btnDelete.textContent = "";

    // 🔥 RESTAURAR CLASE BASE
    btnPrimary.className = "btn-primary";
    btnDelete.className = "btn-danger";
}

function setModalCita(app) {

    resetModalButtons();

    modalTitle.textContent = "Detalles de la cita";
    modalInfo.innerHTML = '';

    const fields = {
        "Cliente": app.cliente,
        "Hora": app.hora,
        "Duración": app.duracion + ' min',
        "Estado": app.estado,
        "Pago": app.estado_pago || 'Pendiente',
        "Notas": app.notas || '-'
    };

    for (const [label, value] of Object.entries(fields)) {

        const fDiv = document.createElement('div');

        const strong = document.createElement('strong');
        strong.textContent = label + ': ';

        fDiv.appendChild(strong);
        fDiv.appendChild(document.createTextNode(value));

        modalInfo.appendChild(fDiv);
    }

    // =========================
    // 🔒 MODAL CLIENTE
    // =========================
    if (window.calendarData.userType === 'cliente') {

        //btnPrimary.textContent = "Cambiar";

        //btnPrimary.onclick = () => {
        //    window.location.href =
        //        `admin/contactar_barbero.php?cita=${app.id}`;
        //};

        btnPrimary.textContent = "Cambiar";
        btnPrimary.disabled = true;

        btnPrimary.style.opacity = "0.5";
        btnPrimary.style.cursor = "not-allowed";

        btnPrimary.onclick = null;



        btnDelete.textContent = "Cancelar";

        btnDelete.onclick = () => {

            const confirmar = confirm(
                "¿Seguro que desea cancelar su cita?"
            );

            if (!confirmar) return;

            fetch(`admin/solicitar_cancelacion.php`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: new URLSearchParams({
                    id_cita: app.id,
                    csrf: csrf_token
                })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    alert("Solicitud enviada correctamente");
                    cerrarModal();
                    location.reload();
                } else {
                    alert("Error al solicitar cancelación");
                }
            });
        };

        return;
    }

    // =========================
    // 🔓 ADMIN / BARBERO
    // =========================

    btnPrimary.textContent = "Editar";

    btnPrimary.onclick = () =>
        window.location.href =
            `admin/editar_cita.php?id=${app.id}&csrf=${csrf_token}`;

    btnDelete.textContent = "Eliminar";

    btnDelete.onclick = () =>
        window.location.href =
            `admin/eliminar_cita.php?id=${app.id}&fecha=${app.fecha}&csrf=${csrf_token}`;
}

function setModalNuevaCita(fecha, hora) {
    resetModalButtons();

    modalTitle.textContent = "Nueva cita";

    modalInfo.innerHTML = `
        No hay ninguna cita programada en este horario.<br>
        ¿Desea generar una nueva cita a las <strong>${hora}</strong>?
    `;

    btnPrimary.textContent = "Crear";
    btnPrimary.onclick = () =>
        window.location.href = `admin/anadir_cita.php?fecha=${fecha}&hora=${hora}&csrf=${csrf_token}`;

    // 🔥 CLAVE: ocultar eliminar
    btnDelete.style.display = "none";
    btnDelete.onclick = null;
}

// =======================
// VISTA MENSUAL
// =======================
document.querySelectorAll('.day-cell[data-date]').forEach(cell => {
    const date = cell.dataset.date;
    const apps = visibleAppointments.filter(a => a.fecha === date);

    // bloqueos totales
    if (estaDiaCompletamenteBloqueado(date)) {
        cell.classList.add('disabled-day');
        cell.style.opacity = "0.5";
        cell.style.pointerEvents = "none";
        cell.style.cursor = "not-allowed";
        return; // no se agrega onclick
    }

    if(apps.length > 0){
        const div = document.createElement('div');
        div.className = 'appointment-count';
        div.textContent = apps.length + ' cita' + (apps.length > 1 ? 's' : '');
        cell.appendChild(div);
    }

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const cellDate = new Date(date + 'T00:00:00');

    if (cellDate < today && userType === 'cliente') {
        cell.style.opacity = "0.5";
        cell.style.pointerEvents = "none";
        cell.style.cursor = "not-allowed";
        cell.classList.add("disabled-day");
    } else {
        cell.onclick = () => {
            const [y, m, d] = date.split('-');
            const params = new URLSearchParams(window.location.search);

            window.location.href =
                `?year=${y}&month=${m}&day=${d}` +
                `&estado=${params.get('estado') ?? ''}` +
                `&barbero=${params.get('barbero') ?? ''}`;
        };
    }
});

function puedeVerCita(app) {

    const userType = window.calendarData.userType;

    if (userType === 'admin' || userType === 'barbero') {
        return true;
    }

    if (userType === 'cliente') {
        return Number(app.id_cliente) === Number(window.calendarData.clienteId);
    }

    return false;
}
// =======================
// VISTA DIARIA
// =======================
document.querySelectorAll('.hour-slot').forEach(slot => {

    const hora = slot.dataset.hora;
    const slotMin = horaToMin(hora);

    const fecha =
        window.calendarData.year + '-' +
        String(window.calendarData.month).padStart(2, '0') + '-' +
        String(window.calendarData.day).padStart(2, '0');

    const bloqueosDia = getBloqueosDia(fecha);

    console.log("BLOQUEOS DIA:", bloqueosDia);
    console.log("SLOT:", hora, slotMin);

    const horario = getHorarioDia(fecha);

    // 🚨 protección extra
    if (!horario) {
        console.warn("Sin horario para:", fecha);
        slot.classList.add('disabled-hour');
        return;
    }

    const inicioMin = parseHora(horario.hora_inicio) ?? 9 * 60;
    const finMin = parseHora(horario.hora_fin) ?? 20 * 60;

    const pausaInicio = parseHora(horario.pausa_inicio);
    const pausaFin = parseHora(horario.pausa_fin);

    if (slotMin < inicioMin || slotMin >= finMin) {
        slot.classList.add('disabled-hour');
        return;
    }

    if (pausaInicio != null && pausaFin != null && pausaInicio < pausaFin) {
        if (slotMin >= pausaInicio && slotMin < pausaFin) {
            slot.classList.add('disabled-hour');
            return;
        }
    }

    for (const b of bloqueosDia) {
        const bInicio = parseHora(b.hora_inicio) ?? 0;
        const bFin = parseHora(b.hora_fin) ?? 0;

        // Si inicio === fin y es 0, asumimos todo el día
        const finBloqueo = (bInicio === bFin && bInicio === 0) ? 24*60 : bFin;

        if (slotMin >= bInicio && slotMin < finBloqueo) {
            slot.classList.add('disabled-hour');
            slot.dataset.bloqueo = b.id_bloqueo;
            return; // 🔥 bloquea completamente la celda
        }
    }

    const now = new Date();
    const todayDate = now.toISOString().split('T')[0];

    const currentDate =
        window.calendarData.year + '-' +
        String(window.calendarData.month).padStart(2, '0') + '-' +
        String(window.calendarData.day).padStart(2, '0');

    const isToday = currentDate === todayDate;

    const currentMinutes = now.getHours() * 60 + now.getMinutes();

    const hayBloqueo = bloqueosDia.some(b => {
        if (!b.hora_inicio || !b.hora_fin) return false;

        const bInicio = parseHora(b.hora_inicio);
        const bFin = parseHora(b.hora_fin);

        if (bInicio == null || bFin == null) return false;

        return slotMin >= bInicio && slotMin < bFin;
    });

    if (hayBloqueo) {
        slot.classList.add('disabled-hour');
        slot.dataset.bloqueo = "1";
        return;
    }

    const apps = visibleAppointments.filter(a => {
        const start = horaToMin(a.hora);
        const end = start + (parseInt(a.duracion) || 0);

        return slotMin >= start && slotMin < end;
    });

    const vipDia = getVipDia(fecha);

    const bloqueadoVIP = vipDia.some(v => {
        const vInicio = parseHora(v.hora_inicio);
        const vFin = parseHora(v.hora_fin);

        return slotMin >= vInicio && slotMin < vFin;
    });

    if (bloqueadoVIP && userType === 'cliente') {
        slot.classList.add('disabled-hour');
        return;
    }

    if (bloqueadoVIP) {
        slot.classList.add('vip-slot');
    }

    const isPastHourToday =
    userType === 'cliente' &&
    isToday &&
    slotMin < currentMinutes;

    if (isPastHourToday) {                        
        slot.classList.add('disabled-hour');
    }

    if (apps.length > 0) {

        slot.classList.add('occupied');

        const estados = new Set(
            apps
                .map(a => a.estado)
                .filter(e => e && e.trim() !== '')
        );
        //console.log('slot', hora, estados);

        if (estados.has('pendiente_cancelacion')) {
            slot.classList.add('ocupado-cancelacion');
        }
    }

    // 👉 Guardar SOLO si empieza aquí
    apps.forEach(app => {
        const inicio = horaToMin(app.hora);

        const visible = puedeVerCita(app);

        const div = document.createElement('div');
        if (visible) {
            div.className = 'appointment ' + app.estado;
        } else {
            div.className = 'appointment oculto';
        }

        slot.dataset.appId = app.id;

        if (slotMin === inicio) {

            div.classList.add('line-only');

            const label = document.createElement('div');
            label.className = 'appointment-label';
            if (puedeVerCita(app)) {
                label.textContent = app.cliente;
            } else {
                label.textContent = "Cita ocupada";
                label.classList.add("anonima");
            }

            slot.appendChild(label);
        }

        slot.appendChild(div);
    });

slot.onclick = () => {

    if (slot.classList.contains('disabled-hour')) {
        return;
    }

    const appId = slot.dataset.appId;

    if (appId) {

        const cita = appointmentMap[appId];

        // 🔒 CLIENTE SOLO PUEDE ABRIR SUS CITAS
        if (
            window.calendarData.userType === 'cliente' &&
            Number(cita.id_cliente) !== Number(window.calendarData.clienteId)
        ) {

            modalTitle.textContent = "Horario ocupado";

            modalInfo.innerHTML = `
                Este horario ya está reservado.
            `;

            resetModalButtons();

            btnPrimary.style.display = "none";
            btnDelete.style.display = "none";

            abrirModal();
            return;
        }

        setModalCita(cita);
        abrirModal();
        return;
    }

    const year = window.calendarData.year;
    const month = window.calendarData.month;
    const day = window.calendarData.day;

    const fecha = slot.dataset.date ??
        `${year}-${String(month).padStart(2,'0')}-${String(day).padStart(2,'0')}`;


    const fechaHoraSlot = new Date(`${fecha}T${hora}`);
    const hoy = new Date();

    const esDiaAnterior =
        fechaHoraSlot.toDateString() !== hoy.toDateString() &&
        fechaHoraSlot < hoy;

    if (
        userType !== 'cliente' &&
        esDiaAnterior
    ) {
        return;
    }


    setModalNuevaCita(fecha, hora);
    abrirModal();
};

});

// =======================
// FILTROS FLOTANTES (ESTADO + BARBERO)
// =======================
const estadoButtons = document.querySelectorAll('.estado-options .filter-btn');
const barberoButtons = document.querySelectorAll('.barbero-options .filter-btn');

let selectedEstado = window.calendarData.estadoFiltro;
let selectedBarbero = window.calendarData.barberoFiltro;

// Activar clases iniciales
estadoButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.estado === selectedEstado));
barberoButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.barbero === selectedBarbero));

// Función que aplica ambos filtros juntos
function actualizarFiltros() {
    estadoButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.estado === selectedEstado));
    barberoButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.barbero === selectedBarbero));

    const year = window.calendarData.year;
    const month = window.calendarData.month;
    const day = window.calendarData.day;
    const params = new URLSearchParams();
    if(year !== null) params.set('year', year);
    if(month !== null) params.set('month', month);
    if(day !== null) params.set('day', day);
    if(selectedEstado) params.set('estado', selectedEstado);
    if(selectedBarbero) params.set('barbero', selectedBarbero);

    window.location.href = '?' + params.toString();
}

// Eventos de click: toggle
estadoButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        selectedEstado = (selectedEstado === btn.dataset.estado) ? '' : btn.dataset.estado;
        actualizarFiltros();
    });
});

barberoButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        selectedBarbero = (selectedBarbero === btn.dataset.barbero) ? '' : btn.dataset.barbero;
        actualizarFiltros();
    });
});



// =======================
// TOGGLE FILTROS (OK)
// =======================
const toggleBtn = document.getElementById('toggle-filters');
const filterBox = document.getElementById('floating-filter');

toggleBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    filterBox.classList.toggle('active');
});

// evitar cierre al hacer click dentro
filterBox.addEventListener('click', (e) => {
    e.stopPropagation();
});

// cerrar al hacer click fuera
document.addEventListener('click', () => {
    filterBox.classList.remove('active');
});