// =======================
// CONFIGURACIÓN HORARIO
// =======================

// Días de la semana activos (0 = domingo)
const WORKING_DAYS = [1, 2, 3, 4, 5]; // Lunes a Viernes

// Horario laboral
const START_HOUR = 9;   // 09:00
const END_HOUR   = 20;  // 20:00

// Duración de cada cita (minutos)
const SLOT_DURATION = 30;

// Pausas (ej: comida)
const BREAKS = [
    { start: "14:00", end: "17:00" }
];

// =======================
// DOM
// =======================
const timeSlotsContainer = document.getElementById("timeSlots");

const separator = document.getElementById("separator");

// =======================
// UTILIDADES
// =======================
function timeToMinutes(time) {
    const [h, m] = time.split(":").map(Number);
    return h * 60 + m;
}

function minutesToTime(min) {
    const h = String(Math.floor(min / 60)).padStart(2, "0");
    const m = String(min % 60).padStart(2, "0");
    return `${h}:${m}`;
}

function isInBreak(minutes) {
    return BREAKS.some(b => {
        return (
            minutes >= timeToMinutes(b.start) &&
            minutes < timeToMinutes(b.end)
        );
    });
}

// =======================
// RENDER HORAS
// =======================
function renderTimeSlots(dateStr) {
    timeSlotsContainer.innerHTML = "";

    const timeSelect = document.getElementById("timeSelect");
    const isMobile = window.innerWidth <= 497;

    if (separator) separator.style.display = "block";

    if (timeSelect) {
        timeSelect.innerHTML = '<option value="">Selecciona una hora</option>';
    }

    const dateObj = new Date(dateStr);
    const dayOfWeek = dateObj.getDay();

    if (!WORKING_DAYS.includes(dayOfWeek)) {

        const emessage = "No hay horario disponible este día";

        timeSlotsContainer.innerHTML = '<div class="emessage-wrapper"><p class="emessage">'
                                        + emessage + '</p></div>';
        
        if (timeSelect) {
            timeSelect.innerHTML = '<option value="">' + emessage + '</option>';
        }

        document.getElementById("confirmAppointment").style.display = "none";
        document.getElementById("confirmAppointment").disabled = true;

        return;
    }

    const now = new Date();

    let startMinutes = START_HOUR * 60;
    const endMinutes = END_HOUR * 60;

    while (startMinutes + SLOT_DURATION <= endMinutes) {
        const slotTime = minutesToTime(startMinutes);
        const slotDate = new Date(`${dateStr}T${slotTime}:00`);

        let disabled = false;

        // Bloqueo por Google Calendar
        if (
            window.busyMapGlobal &&
            (
                window.busyMapGlobal[dateStr] === "full" ||
                (
                    Array.isArray(window.busyMapGlobal[dateStr]) &&
                    window.busyMapGlobal[dateStr].includes(slotTime)
                )
            )
        ) {
            disabled = true;
        }



        if (
            dateObj.toDateString() === now.toDateString() &&
            slotDate <= now
        ) disabled = true;

        if (isInBreak(startMinutes)) disabled = true;

        // =======================
        // 📱 MOBILE → SELECT
        // =======================
        if (isMobile && timeSelect) {
            if (!disabled) {
                const opt = document.createElement("option");
                opt.value = slotTime;
                opt.textContent = slotTime;
                timeSelect.appendChild(opt);
            }
        }

        // =======================
        // 🖥 DESKTOP → BOTONES
        // =======================
        if (!isMobile) {
            const btn = document.createElement("button");
            btn.textContent = slotTime;
            btn.className = "time-slot";

            if (disabled) {
                btn.classList.add("disabled");
                btn.disabled = true;
            } else {
                btn.onclick = () => {
                    // Deseleccionar todos los botones
                    document.querySelectorAll(".time-slot").forEach(b => b.classList.remove("selected"));

                    // Seleccionar el clicado
                    btn.classList.add("selected");

                    // Asignar valor global para createAppointment()
                    selectedTimeGlobal = slotTime;

                    // Mostrar y habilitar el botón de confirmar
                    const confirmBtn = document.getElementById("confirmAppointment");
                    confirmBtn.disabled = false;
                    confirmBtn.style.display = "block";

                    // Mantener el dispatchEvent si lo necesitas para otros listeners
                    document.dispatchEvent(
                        new CustomEvent("time:selected", {
                            detail: {
                                date: dateStr,
                                time: slotTime
                            }
                        })
                    );
                };
            }

            timeSlotsContainer.appendChild(btn);
        }

        startMinutes += SLOT_DURATION;
    }
}


// =======================
// ESCUCHAR SELECCIÓN DE DÍA
// =======================
document.addEventListener("day:selected", (e) => {
    const { date } = e.detail;
    renderTimeSlots(date);
});
