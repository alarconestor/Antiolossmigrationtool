<?php

session_start();

require_once __DIR__ . '/../services/traccar.php';


/*
|--------------------------------------------------------------------------
| Datos recibidos
|--------------------------------------------------------------------------
*/

$url = rtrim(
    $_POST['url'] ?? '',
    '/'
);

$usuario = $_POST['user'] ?? '';

$password = $_POST['pass'] ?? '';

$passwordTemporal = $_POST['password_temporal'] ?? '';


/*
|--------------------------------------------------------------------------
| Validar contraseña temporal
|--------------------------------------------------------------------------
*/

if (strlen($passwordTemporal) < 6) {

    $_SESSION['wizard_error'] =
        'La contraseña temporal debe tener al menos 6 caracteres.';

    header('Location: ../wizard/destino.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| Conectar con Servidor Destino
|--------------------------------------------------------------------------
*/

$r = traccarLogin(
    $url,
    $usuario,
    $password
);


if (!$r['ok']) {

    $_SESSION['wizard_error'] =
        $r['mensaje'];

    header('Location: ../wizard/destino.php');

    exit;
}


/*
|--------------------------------------------------------------------------
| Guardar sesión del Servidor Destino
|--------------------------------------------------------------------------
*/

$_SESSION['sd'] = [

    'url' =>
        $url,

    'cookies' =>
        $r['cookies'],

    /*
     * Esta contraseña solamente se mantiene
     * durante la sesión de migración.
     *
     * NO se almacena en el paquete JSON.
     */
    'password_temporal' =>
        $passwordTemporal

];


/*
|--------------------------------------------------------------------------
| Continuar con la migración
|--------------------------------------------------------------------------
*/

header(
    'Location: ../wizard/migracion.php'
);

exit;