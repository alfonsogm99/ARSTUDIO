<?php
session_start();
require '../admin/config.php';

$error = '';
$success = '';

if (!isset($_SESSION['pending_user'])) {
    header("Location: register.php");
    exit;
}

$data = $_SESSION['pending_user'];

function verifyOTP($phone, $code, $sid, $token, $verifySid) {

    $url = "https://verify.twilio.com/v2/Services/$verifySid/VerificationCheck";

    $postData = [
        "To" => $phone,
        "Code" => $code
    ];

    //echo "<pre style='background:#000;color:#0f0;padding:10px'>";
    //echo "🔍 URL:\n$url\n\n";
    //echo "📤 POST DATA:\n";
    //print_r($postData);

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $curlError = curl_error($ch);

    curl_close($ch);

    //echo "\n📥 HTTP CODE:\n";
    //var_dump($httpCode);

    //echo "\n📥 RAW RESPONSE:\n";
    //var_dump($response);

    if ($curlError) {
        echo "\n❌ CURL ERROR:\n$curlError\n";
    }

    //echo "</pre>";

    return [$response, $httpCode];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $code = trim($_POST['code']);

    $sid = "AC0a0df29f986f88e187dbb3ad4a910040";
    $token = "2cf39fb445e56e7e359ecbe729d66544";
    $verifySid = "VA764fa9e539468a0e134406cbe73dfb7e";

    list($response, $httpCode) = verifyOTP(
        $data['phone'],
        $code,
        $sid,
        $token,
        $verifySid
    );

    $result = json_decode($response, true);

    if ($httpCode >= 400) {
        $error = "❌ HTTP $httpCode → " . $response;
    } elseif (($result['status'] ?? '') === 'approved') {

        $stmt = $conn->prepare("
            INSERT INTO usuario 
            (username, email, phone, password, rol, activo, status)
            VALUES (?, ?, ?, ?, 'cliente', 0, 'verified')
        ");

        $stmt->bind_param(
            "ssss",
            $data['username'],
            $data['email'],
            $data['phone'],
            $data['password']
        );

        $stmt->execute();

        $userId = $stmt->insert_id;

        $_SESSION['user'] = [
            'id' => $userId,
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'rol' => 'cliente',
            'status' => 'verified'
        ];

        unset($_SESSION['pending_user']);


        $success = "Su número de teléfono ha sido verificado correctamente. Serás redirigido en 5 segundos...";
        $redirect = true;
    } else {
        $error = "❌ Código incorrecto.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="icon" type="image/x-icon" href="../../img/favicon.ico">
<link rel="stylesheet" href="../../css/style.css">
<title>ARSTUDIO · OTP</title>
</head>
<body>


<div class="form">

    <img class="login logo" src="../../img/renewed_logo.png">

    <h3 style="text-align:center;">Verifica tu cuenta</h3>

    <form method="post" class="form_class">

        <input type="text" name="code" placeholder="Código de 6 dígitos" maxlength="6" required>

        <br>
        
        <p style="text-align:center; font-size:12px; opacity:.5;">
            Hemos enviado un código a tu WhatsApp 📲
        </p>

        <button type="submit">Verificar</button>

    </form>


</div>

<?php if ($error): ?>
    <p class="error-message"><?= $error ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p class="success-message"><?= $success ?></p>
<?php endif; ?>

<?php if (!empty($redirect)): ?>
<script>
let seconds = 5;

const interval = setInterval(() => {
    seconds--;

    if (seconds <= 0) {
        clearInterval(interval);
        window.location.href = "../logout";
    }
}, 1000);
</script>
<?php endif; ?>


<?php
$configFile = '../admin/config.json';

$config = [];

if (file_exists($configFile)) {
    $json = file_get_contents($configFile);
    $decoded = json_decode($json, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $config = $decoded;
    }
}

$version = $config['app']['version'] ?? '0.0.0';
?>


<section class="cookies">
    <h2 class="cookies__titulo">🍪 ¿Aceptas nuestras Cookies?</h2>
    <p class="cookies__texto">Este sitio utiliza cookies para mejorar la experiencia del usuario.</p>
    <div class="cookies__botones">
        <button class="cookies__boton cookies__boton--si">Si</button>
        <button class="cookies__boton cookies__boton--no">No</button>
    </div>
</section>

<!-- No borrar, aquí se generarán todas las etiquetas <script> si acepta el usuario -->
<div id="nuevosScripts"></div>

<footer class="site-footer">
    <span class="version">v.<?= htmlspecialchars($version) ?></span><br>
    <span class="copyright">© 2026 alfonsogm</span>
</footer>

<br><br>
<footer class="policy">
        <a href="../includes/privacidad.html">Política de Privacidad</a> |
        <a href="../includes/cookies.html">Política de Cookies</a> |
        <a href="../includes/terminos.html">Términos y Condiciones</a>
</footer>

<script src="../../js/togglePassword.js"></script>
<script src="../../js/cookies.js"></script>
</body>
</html>