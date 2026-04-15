function generarDNI() {
    const numero = Math.floor(Math.random() * (99999999 - 10000000 + 1)) + 10000000;
    const letras = "TRWAGMYFPDXBNJZSQVHLCKE";
    const letra = letras[numero % 23];
    
    document.getElementById("dni").value = numero + letra;
}