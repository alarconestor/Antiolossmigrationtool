<?php

session_start();

set_time_limit(0);

ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', '0');

header(
    'Content-Type:application/x-ndjson; charset=utf-8'
);

header(
    'Cache-Control:no-cache'
);

header(
    'X-Accel-Buffering:no'
);

require_once __DIR__ . '/../services/traccar.php';


/*
|--------------------------------------------------------------------------
| Enviar evento al navegador
|--------------------------------------------------------------------------
*/

function out($a)
{
    echo json_encode(
        $a,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    ) . "\n";

    @ob_flush();
    @flush();
}


/*
|--------------------------------------------------------------------------
| Motivo de error
|--------------------------------------------------------------------------
*/

function motivoError($respuesta)
{
    if (empty($respuesta)) {

        return 'Sin respuesta del servidor.';

    }


    $json =
        json_decode(
            $respuesta,
            true
        );


    if (is_array($json)) {

        if (!empty($json['message'])) {

            return $json['message'];

        }


        if (!empty($json['error'])) {

            return $json['error'];

        }


        return json_encode(
            $json,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );

    }


    return trim($respuesta);
}


/*
|--------------------------------------------------------------------------
| ENVIAR CORREO DIRECTAMENTE A TRACCAR
|--------------------------------------------------------------------------
|
| Esta función es independiente de services/traccar.php.
|
| Utiliza exactamente el endpoint que comprobamos manualmente:
|
| /api/notifications/send/mail?userId=ID
|
*/

function enviarCorreoTraccar(
    $url,
    $userId,
    $subject,
    $digest,
    $body,
    $cookies
) {

    $mensaje = [

        'subject' =>
            $subject,

        'digest' =>
            $digest,

        'body' =>
            $body,

        'priority' =>
            false

    ];


    /*
    |--------------------------------------------------------------------------
    | Generar JSON
    |--------------------------------------------------------------------------
    |
    | JSON_UNESCAPED_UNICODE permite conservar correctamente
    | acentos y caracteres UTF-8.
    |
    */

    $json =
        json_encode(

            $mensaje,

            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES |
            JSON_INVALID_UTF8_SUBSTITUTE

        );


    if ($json === false) {

        return [

            'ok' =>
                false,

            'http' =>
                0,

            'respuesta' =>
                'Error generando JSON: ' .
                json_last_error_msg()

        ];

    }


    /*
    |--------------------------------------------------------------------------
    | URL
    |--------------------------------------------------------------------------
    */

    $endpoint =
        rtrim(
            $url,
            '/'
        ) .
        '/api/notifications/send/mail?userId=' .
        (int) $userId;


    /*
    |--------------------------------------------------------------------------
    | CURL
    |--------------------------------------------------------------------------
    */

    $ch =
        curl_init(
            $endpoint
        );


    curl_setopt_array(

        $ch,

        [

            CURLOPT_RETURNTRANSFER =>
                true,

            CURLOPT_POST =>
                true,

            CURLOPT_POSTFIELDS =>
                $json,

            CURLOPT_HTTPHEADER => [

                'Content-Type: application/json; charset=utf-8',

                'Accept: application/json',

                'Cookie: ' . $cookies

            ],

            CURLOPT_TIMEOUT =>
                30,

            CURLOPT_CONNECTTIMEOUT =>
                10,

            CURLOPT_SSL_VERIFYPEER =>
                true,

            CURLOPT_SSL_VERIFYHOST =>
                2

        ]

    );


    $respuesta =
        curl_exec(
            $ch
        );


    /*
    |--------------------------------------------------------------------------
    | Error CURL
    |--------------------------------------------------------------------------
    */

    if (
        $respuesta === false
    ) {

        $error =
            curl_error(
                $ch
            );


        curl_close(
            $ch
        );


        return [

            'ok' =>
                false,

            'http' =>
                0,

            'respuesta' =>
                $error

        ];

    }


    /*
    |--------------------------------------------------------------------------
    | HTTP
    |--------------------------------------------------------------------------
    */

    $http =
        curl_getinfo(

            $ch,

            CURLINFO_HTTP_CODE

        );


    curl_close(
        $ch
    );


    return [

        'ok' =>
            $http >= 200 &&
            $http < 300,

        'http' =>
            $http,

        'respuesta' =>
            $respuesta,

        'datos' =>
            json_decode(
                $respuesta,
                true
            )

    ];
}


/*
|--------------------------------------------------------------------------
| Verificar sesión
|--------------------------------------------------------------------------
*/

if (
    empty($_SESSION['sd']) ||
    empty($_SESSION['selected_users'])
) {

    out([

        'type' =>
            'fatal',

        'message' =>
            'Sesión de migración no disponible.'

    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Verificar contraseña temporal
|--------------------------------------------------------------------------
*/

if (
    empty(
        $_SESSION['sd']['password_temporal']
    )
) {

    out([

        'type' =>
            'fatal',

        'message' =>
            'No se encontró la contraseña temporal.'

    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Datos servidor destino
|--------------------------------------------------------------------------
*/

$sd =
    $_SESSION['sd'];


/*
|--------------------------------------------------------------------------
| Contraseña temporal
|--------------------------------------------------------------------------
*/

$passwordTemporal =
    $sd['password_temporal'];


/*
|--------------------------------------------------------------------------
| Buscar paquete de migración
|--------------------------------------------------------------------------
*/

$package =
    $_SESSION['migration_package'] ?? null;


/*
|--------------------------------------------------------------------------
| Si no está en sesión buscar paquete más reciente
|--------------------------------------------------------------------------
*/

if (!$package) {

    $dir =
        __DIR__ .
        '/../packages';


    $files =
        glob(
            $dir .
            '/migration_*.json'
        );


    if (
        empty($files)
    ) {

        out([

            'type' =>
                'fatal',

            'message' =>
                'No se encontró el paquete de migración.'

        ]);

        exit;

    }


    /*
     * Más reciente primero.
     */

    usort(

        $files,

        function ($a, $b) {

            return filemtime($b)
                <=>
                filemtime($a);

        }

    );


    $package =
        basename(
            $files[0]
        );

}


/*
|--------------------------------------------------------------------------
| Cargar paquete
|--------------------------------------------------------------------------
*/

$file =
    __DIR__ .
    '/../packages/' .
    basename($package);


if (
    !is_file($file)
) {

    out([

        'type' =>
            'fatal',

        'message' =>
            'No se encontró el paquete: ' .
            $package

    ]);

    exit;
}


$json =
    file_get_contents(
        $file
    );


$pkg =
    json_decode(
        $json,
        true
    );


if (
    !is_array($pkg) ||
    empty($pkg['users'])
) {

    out([

        'type' =>
            'fatal',

        'message' =>
            'El paquete de migración no contiene usuarios.'

    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Guardar paquete en sesión
|--------------------------------------------------------------------------
*/

$_SESSION['migration_package'] =
    $package;


/*
|--------------------------------------------------------------------------
| FASE 3 — COMUNICACIÓN
|--------------------------------------------------------------------------
*/

out([

    'type' =>
        'phase',

    'phase' =>
        3,

    'title' =>
        'Fase 3 — Comunicación',

    'message' =>
        'Preparando comunicación con los usuarios migrados.'

]);


/*
|--------------------------------------------------------------------------
| Estadísticas
|--------------------------------------------------------------------------
*/

$stats = [

    'sent' =>
        0,

    'errors' =>
        0,

    'skipped' =>
        0

];


/*
|--------------------------------------------------------------------------
| USUARIOS DEL PAQUETE
|--------------------------------------------------------------------------
*/

$users =
    $pkg['users'];


$total =
    count($users);


/*
|--------------------------------------------------------------------------
| OBTENER USUARIOS ACTUALES DEL SERVIDOR DESTINO
|--------------------------------------------------------------------------
|
| IMPORTANTE:
|
| NO confiamos ciegamente en destinationId del paquete.
|
| Si un usuario fue eliminado y creado nuevamente,
| su ID puede haber cambiado.
|
*/

out([

    'type' =>
        'log',

    'level' =>
        'info',

    'message' =>
        'Consultando usuarios actuales del servidor destino...'

]);


$rUsers =
    traccarGet(

        rtrim(
            $sd['url'],
            '/'
        ) .
        '/api/users',

        $sd['cookies']

    );


if (
    !$rUsers['ok']
) {

    out([

        'type' =>
            'fatal',

        'message' =>
            'No se pudieron consultar los usuarios del servidor destino: ' .
            ($rUsers['mensaje'] ?? 'Error desconocido.')

    ]);

    exit;
}


$destinationUsers =
    $rUsers['datos'];


out([

    'type' =>
        'log',

    'level' =>
        'success',

    'message' =>
        count($destinationUsers) .
        ' usuario(s) encontrados en el servidor destino.'

]);


/*
|--------------------------------------------------------------------------
| Procesar usuarios
|--------------------------------------------------------------------------
*/

foreach (
    $users as $i => $u
) {

    $name =
        $u['name'] ??
        'Usuario';


    $email =
        trim(
            $u['email'] ??
            ''
        );


    /*
    |--------------------------------------------------------------------------
    | Progreso
    |--------------------------------------------------------------------------
    */

    out([

        'type' =>
            'progress',

        'percent' =>
            round(
                ($i / max(1, $total)) * 100
            ),

        'message' =>
            'Usuario ' .
            ($i + 1) .
            ' de ' .
            $total .
            ': ' .
            $name

    ]);


    /*
    |--------------------------------------------------------------------------
    | Validar correo
    |--------------------------------------------------------------------------
    */

    if (
        $email === ''
    ) {

        $stats['skipped']++;


        out([

            'type' =>
                'log',

            'level' =>
                'error',

            'message' =>
                $name .
                ' — no tiene correo electrónico.'

        ]);

        continue;

    }


    /*
    |--------------------------------------------------------------------------
    | BUSCAR USUARIO REAL EN DESTINO
    |--------------------------------------------------------------------------
    */

    $destinationUser =
        null;


    foreach (
        $destinationUsers as $du
    ) {

        $destEmail =
            trim(
                $du['email'] ??
                ''
            );


        if (
            $destEmail !== '' &&
            strtolower($destEmail) ===
            strtolower($email)
        ) {

            $destinationUser =
                $du;

            break;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Usuario no encontrado
    |--------------------------------------------------------------------------
    */

    if (
        !$destinationUser
    ) {

        $stats['errors']++;


        out([

            'type' =>
                'log',

            'level' =>
                'error',

            'message' =>
                $name .
                ' — no existe actualmente en el servidor destino (' .
                $email .
                ').'

        ]);

        continue;

    }


    /*
    |--------------------------------------------------------------------------
    | ID REAL ACTUAL
    |--------------------------------------------------------------------------
    */

    $destinationId =
        (int) (
            $destinationUser['id'] ??
            0
        );


    if (
        $destinationId <= 0
    ) {

        $stats['errors']++;


        out([

            'type' =>
                'log',

            'level' =>
                'error',

            'message' =>
                $name .
                ' — se encontró el usuario pero no tiene un ID válido.'

        ]);

        continue;

    }


    /*
    |--------------------------------------------------------------------------
    | Mostrar ID utilizado
    |--------------------------------------------------------------------------
    */

    out([

        'type' =>
            'log',

        'level' =>
            'info',

        'message' =>
            $name .
            ' — usuario destino encontrado. ID actual: ' .
            $destinationId

    ]);


    /*
    |--------------------------------------------------------------------------
    | Asunto
    |--------------------------------------------------------------------------
    */

    $subject =
        'Migración de cuenta - Antiloss GPS';


    /*
    |--------------------------------------------------------------------------
    | Digest
    |--------------------------------------------------------------------------
    */

    $digest =
        'Información de migración de cuenta';


    /*
    |--------------------------------------------------------------------------
    | Cuerpo
    |--------------------------------------------------------------------------
    */

    $body =
        "Hola " .
        $name .
        ",\n\n" .

        "Tu cuenta de Antiloss GPS ha sido migrada a un nuevo servidor.\n\n" .

        "Puedes ingresar utilizando tu correo electrónico:\n" .

        $email .
        "\n\n" .

        "Tu contraseña temporal es:\n" .

        $passwordTemporal .
        "\n\n" .

        "Por seguridad, te recomendamos cambiar esta contraseña después de iniciar sesión.\n\n" .

        "Servidor:\n" .

        rtrim(
            $sd['url'],
            '/'
        ) .
        "\n\n" .

        "Saludos,\n" .

        "Antiloss Technologies";


    /*
    |--------------------------------------------------------------------------
    | ENVIAR CORREO
    |--------------------------------------------------------------------------
    |
    | NO utilizamos traccarPost().
    |
    | NO modificamos services/traccar.php.
    |
    | Comunicación independiente.
    |
    */

    $r =
        enviarCorreoTraccar(

            $sd['url'],

            $destinationId,

            $subject,

            $digest,

            $body,

            $sd['cookies']

        );


    /*
    |--------------------------------------------------------------------------
    | Resultado
    |--------------------------------------------------------------------------
    */

    if (
        $r['ok']
    ) {

        $stats['sent']++;


        out([

            'type' =>
                'log',

            'level' =>
                'success',

            'message' =>
                'Correo enviado correctamente a ' .
                $email .
                ' (' .
                $name .
                ').'

        ]);

    }

    else {

        $stats['errors']++;


        out([

            'type' =>
                'log',

            'level' =>
                'error',

            'message' =>
                'No se pudo enviar correo a ' .
                $email .
                ' (HTTP ' .
                ($r['http'] ?? 0) .
                '). Motivo: ' .
                motivoError(
                    $r['respuesta'] ?? ''
                )

        ]);

    }

}


/*
|--------------------------------------------------------------------------
| Finalizar
|--------------------------------------------------------------------------
*/

out([

    'type' =>
        'progress',

    'percent' =>
        100,

    'message' =>
        'Comunicación finalizada.'

]);


/*
|--------------------------------------------------------------------------
| Resumen
|--------------------------------------------------------------------------
*/

out([

    'type' =>
        'summary',

    'sent' =>
        $stats['sent'],

    'errors' =>
        $stats['errors'],

    'skipped' =>
        $stats['skipped'],

    'package' =>
        $package

]);