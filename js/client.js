function horaToMin(h) {
    const [hh, mm] = h.split(':');
    return parseInt(hh) * 60 + parseInt(mm);
}

function minToHora(min) {

    const h = Math.floor(min / 60);
    const m = min % 60;

    return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}`;
}

const fechaInput = document.getElementById('fecha');
const barberoInput = document.getElementById('barbero');

const serviceCheckboxes = document.querySelectorAll('.service-checkbox');

const slotsContainer = document.getElementById('available-slots');

const totalDuration = document.getElementById('total-duration');
const totalPrice = document.getElementById('total-price');

const selectedHourInput = document.getElementById('selected-hour');

const form = document.getElementById('booking-form');

let currentDuration = 0;
let currentPrice = 0;

// =======================
// CALCULAR SERVICIOS
// =======================
function calcularServicios() {

    currentDuration = 0;
    currentPrice = 0;

    serviceCheckboxes.forEach(cb => {

        if(cb.checked){

            currentDuration += parseInt(cb.dataset.duracion);
            currentPrice += parseFloat(cb.dataset.precio);
        }

    });

    totalDuration.textContent = currentDuration + ' min';
    totalPrice.textContent = currentPrice.toFixed(2) + '€';

    cargarHoras();
}

serviceCheckboxes.forEach(cb => {
    cb.addEventListener('change', calcularServicios);
});

fechaInput.addEventListener('change', cargarHoras);
barberoInput.addEventListener('change', cargarHoras);

// =======================
// CARGAR HORAS DISPONIBLES
// =======================
async function cargarHoras() {

    const fecha = fechaInput.value;
    const barbero = barberoInput.value;

    selectedHourInput.value = '';

    if(!fecha || !barbero || currentDuration <= 0){

        slotsContainer.innerHTML = `
            <p class="empty-msg">
                Selecciona fecha, barbero y servicio
            </p>
        `;

        return;
    }

    slotsContainer.innerHTML = `
        <p class="empty-msg">Cargando...</p>
    `;

    try {

        const fd = new FormData();

        fd.append('fecha', fecha);
        fd.append('barbero', barbero);
        fd.append('duracion', currentDuration);

        const res = await fetch('./get_available_slots.php', {
            method: 'POST',
            body: fd
        });

        const data = await res.json();

        slotsContainer.innerHTML = '';

        if(!data.success){

            slotsContainer.innerHTML = `
                <p class="empty-msg">
                    Error al cargar horarios
                </p>
            `;

            return;
        }

        if(data.slots.length === 0){

            slotsContainer.innerHTML = `
                <p class="empty-msg">
                    No hay horarios disponibles
                </p>
            `;

            return;
        }

        data.slots.forEach(hora => {

            const btn = document.createElement('button');

            btn.type = 'button';
            btn.className = 'slot-btn';

            btn.textContent = hora;

            btn.onclick = () => {

                document.querySelectorAll('.slot-btn')
                    .forEach(b => b.classList.remove('active'));

                btn.classList.add('active');

                selectedHourInput.value = hora;
            };

            slotsContainer.appendChild(btn);

        });

    } catch(err){

        console.error(err);

        slotsContainer.innerHTML = `
            <p class="empty-msg">
                Error de conexión
            </p>
        `;
    }
}

// =======================
// ENVIAR RESERVA
// =======================
form.addEventListener('submit', async (e) => {

    e.preventDefault();

    const servicios = [];

    serviceCheckboxes.forEach(cb => {

        if(cb.checked){
            servicios.push(cb.value);
        }

    });

    if(servicios.length === 0){
        alert('Selecciona un servicio');
        return;
    }

    if(!selectedHourInput.value){
        alert('Selecciona una hora');
        return;
    }

    const fd = new FormData();

    fd.append('csrf_token', window.bookingData.csrf);

    fd.append('fecha', fechaInput.value);
    fd.append('hora', selectedHourInput.value);

    fd.append('barbero', barberoInput.value);

    fd.append('duracion', currentDuration);

    fd.append('notas', document.getElementById('notas').value);

    servicios.forEach(s => {
        fd.append('servicios[]', s);
    });

    try {

        const res = await fetch('./create_booking.php', {
            method: 'POST',
            body: fd
        });

        const data = await res.json();

        if(data.success){

            alert('Reserva creada correctamente');

            window.location.reload();

        } else {

            alert(data.message || 'Error al reservar');
        }

    } catch(err){

        console.error(err);

        alert('Error de conexión');
    }

});