fetch("../../dev/precios.json")
  .then(response => response.json())
  .then(data => {

    const caballero = document.getElementById("caballero");
    const nino = document.getElementById("nino");

    // Caballero
    data.caballero.forEach(servicio => {
      const li = document.createElement("li");
      li.innerHTML = `<h2>${servicio.nombre} -> ${servicio.precio}€</h2>`;
      caballero.appendChild(li);
    });

    // Niño
    data.nino.forEach(servicio => {
      const li = document.createElement("li");
      li.innerHTML = `<h2>${servicio.precio}€</h2>`;
      nino.appendChild(li);
    });

  });
