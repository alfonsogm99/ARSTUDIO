<p align="center">
  <img src="assets/portada.png" width="500">
</p>

```
v.1.2.3
```

## Recuento de productos

Se amplía el apartado `Resumen` del Panel de Contabilidad. Ahora también es posible consultar de un vistazo el rendimiento de los productos destacados y stock disponible.

Se añaden los siguientes indicadores:

- `Productos vendidos` — importe total obtenido por la venta de productos durante el mes.
- `Productos en stock` — número total de productos disponibles en el inventario.
- `Ceras` — ud. vendidas durante el mes y stock restante (/x).
- `Polvos de cabello` — ud. vendidas durante el mes y stock restante (/x).

<p align="center">
  <img src="assets/1.png" width="500">
</p>

## Productos.php

Se incorpora un nuevo panel de administración para gestionar los productos de la barbería. Que permite mantener actualizado el inventario desde el panel de administración, facilitando el control de existencias y posterior integración con el sistema de ventas.

<p align="center">
  <img src="assets/2.png" width="500">
</p>

### Funcionalidades

- `Añadir`/`Editar`/`Eliminar` productos.
- Clasificación por tipo (`cera`, `polvos de bcabello`, `aceite` ...).
- Gestión del precio de compra y del precio de venta.
- Control de stock y stock mínimo.
- Descripción para cada producto.
- Listado ordenable por ID, nombre, tipo, precio y stock (incorporado en la `v.1.2.2`).

## Panel de configuración

- Se ha deshabilitado la antigua opción `AR` que permitía añadir información a la página principal `/main`.
- Se habilita el nuevo panel de control `productos.php`.

<p align="center">
  <img src="assets/3.png" width="350">&nbsp;&nbsp;&nbsp;
  <img src="assets/4.png" width="350">
</p>
