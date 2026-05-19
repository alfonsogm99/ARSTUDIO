document.addEventListener("DOMContentLoaded", () => {

    const btnGuardar = document.getElementById("btn_guardar_perfil");

    const camposPerfil = [

        // usuario
        document.getElementById("username_current_input"),
        document.getElementById("username_new_input"),

        // contraseña
        document.getElementById("current_password_input"),
        document.getElementById("new_password_input"),
        document.getElementById("confirm_password_input"),

        // datos perfil
        document.querySelector('input[name="nombre_usuario"]'),
        document.querySelector('input[name="especialidad"]')
    ];

    camposPerfil.forEach(campo => {

        if(campo){

            campo.addEventListener("input", () => {

                const hayCambios = camposPerfil.some(c =>
                    c && c.value.trim() !== ''
                );

                btnGuardar.style.display = hayCambios
                    ? "inline-block"
                    : "none";

            });

        }

    });

});