function horaToMin(h) {
    const [hh, mm] = h.split(':');
    return parseInt(hh) * 60 + parseInt(mm);
}

// =======================
// DATOS DE CITAS DESDE PHP
// =======================
const appointments = window.calendarData.appointments;
const appointmentMap = {};
appointments.forEach(a => appointmentMap[a.id] = a);
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

    btnPrimary.textContent = "Editar";
    btnPrimary.onclick = () =>
        window.location.href = `admin/editar_cita.php?id=${app.id}&csrf=${csrf_token}`;

    btnDelete.textContent = "Eliminar";
    btnDelete.onclick = () =>
        window.location.href = `admin/eliminar_cita.php?id=${app.id}&csrf=${csrf_token}`;
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
    const apps = appointments.filter(a => a.fecha === date);

    if(apps.length > 0){
        const div = document.createElement('div');
        div.className = 'appointment-count';
        div.textContent = apps.length + ' cita' + (apps.length > 1 ? 's' : '');
        cell.appendChild(div);
    }

    cell.onclick = () => {
        const [y,m,d] = date.split('-');
        const params = new URLSearchParams(window.location.search);
        window.location.href = `?year=${y}&month=${m}&day=${d}&estado=${params.get('estado') ?? ''}&barbero=${params.get('barbero') ?? ''}`;
    }
});

// =======================
// VISTA DIARIA
// =======================
document.querySelectorAll('.hour-slot').forEach(slot => {

    const hora = slot.dataset.hora;
    const slotMin = horaToMin(hora);

    const apps = appointments.filter(a => {
        const inicio = horaToMin(a.hora);
        const fin = inicio + parseInt(a.duracion);
        return slotMin >= inicio && slotMin < fin;
    });

    if(apps.length > 0) slot.classList.add('occupied');

    // 👉 Guardar SOLO si empieza aquí
    apps.forEach(app => {
        const inicio = horaToMin(app.hora);

        const div = document.createElement('div');
        div.className = 'appointment ' + app.estado;

        slot.dataset.appId = app.id;

        if (slotMin === inicio) {

            div.classList.add('line-only');

            const label = document.createElement('div');
            label.className = 'appointment-label';
            label.textContent = app.cliente;

            slot.appendChild(label);
        }

        slot.appendChild(div);
    });

slot.onclick = () => {

    const appId = slot.dataset.appId;

    if (appId) {
        setModalCita(appointmentMap[appId]);
        abrirModal();
        return;
    }

    const year = window.calendarData.year;
    const month = window.calendarData.month;
    const day = window.calendarData.day;

    const fecha = slot.dataset.date ??
        `${year}-${String(month).padStart(2,'0')}-${String(day).padStart(2,'0')}`;

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