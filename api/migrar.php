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

function motivoError($respuesta)
{
    if (empty($respuesta)) {
        return 'Sin respuesta del servidor.';
    }

    $json = json_decode($respuesta, true);

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
| Buscar usuario
|--------------------------------------------------------------------------
*/

function findUser($arr, $s)
{
    $em = strtolower(
        trim($s['email'] ?? '')
    );

    $nm = strtolower(
        trim($s['name'] ?? '')
    );

    foreach ($arr as $u) {

        if (
            $em &&
            strtolower(
                trim($u['email'] ?? '')
            ) === $em
        ) {
            return $u;
        }

        if (
            !$em &&
            $nm &&
            strtolower(
                trim($u['name'] ?? '')
            ) === $nm
        ) {
            return $u;
        }
    }

    return null;
}


/*
|--------------------------------------------------------------------------
| Buscar vehículo por Unique ID
|--------------------------------------------------------------------------
*/

function findDev($arr, $s)
{
    $id = (string) (
        $s['uniqueId'] ?? ''
    );

    foreach ($arr as $d) {

        if (
            $id !== '' &&
            (string) (
                $d['uniqueId'] ?? ''
            ) === $id
        ) {
            return $d;
        }
    }

    return null;
}


/*
|--------------------------------------------------------------------------
| Verificar sesión
|--------------------------------------------------------------------------
*/

if (
    empty($_SESSION['sa']) ||
    empty($_SESSION['sd']) ||
    empty($_SESSION['selected_users'])
) {

    out([
        'type' => 'fatal',
        'message' => 'Sesión incompleta.'
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
        'type' => 'fatal',
        'message' =>
            'No se encontró la contraseña temporal para los usuarios.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Datos de sesión
|--------------------------------------------------------------------------
*/

$sa = $_SESSION['sa'];

$sd = $_SESSION['sd'];

$sel = $_SESSION['selected_users'];


/*
|--------------------------------------------------------------------------
| Contraseña temporal
|--------------------------------------------------------------------------
|
| Esta contraseña fue proporcionada por el administrador
| en el paso Servidor Destino.
|
*/

$passwordTemporal =
    $_SESSION['sd']['password_temporal'];


/*
|--------------------------------------------------------------------------
| URL de recuperación de contraseña
|--------------------------------------------------------------------------
|
| Nunca utilizamos una URL fija.
|
*/

$resetUrl =
    rtrim(
        $_SESSION['sd']['url'],
        '/'
    ) . '/reset-password';


/*
|--------------------------------------------------------------------------
| Estadísticas
|--------------------------------------------------------------------------
*/

$stats = [

    'usersCreated' => 0,

    'usersExisting' => 0,

    'vehiclesCreated' => 0,

    'relations' => 0,

    'errors' => 0

];


/*
|--------------------------------------------------------------------------
| Paquete de migración
|--------------------------------------------------------------------------
*/

$pkg = [

    'version' =>
        '1.0',

    'type' =>
        'Traccar_migration',

    'createdAt' =>
        date('c'),

    'source' => [

        'url' =>
            $sa['url']

    ],

    'destination' => [

        'url' =>
            $sd['url']

    ],

    'users' =>
        []

];


out([

    'type' =>
        'phase',

    'phase' =>
        1,

    'title' =>
        'Fase 1 — Usuarios',

    'message' =>
        'Creando o localizando todos los usuarios antes de procesar vehículos.'

]);


/*
|--------------------------------------------------------------------------
| Obtener usuarios existentes de SD
|--------------------------------------------------------------------------
*/

$ru = traccarGet(

    $sd['url'] .
    '/api/users',

    $sd['cookies']

);


if (!$ru['ok']) {

    out([

        'type' =>
            'fatal',

        'message' =>
            'Error consultando usuarios SD: ' .
            $ru['mensaje']

    ]);

    exit;
}


$sdUsers =
    $ru['datos'];


$total =
    count($sel);


/*
|--------------------------------------------------------------------------
| FASE 1 — USUARIOS
|--------------------------------------------------------------------------
*/

foreach ($sel as $i => $u) {

    $name =
        $u['name'] ??
        'Usuario';


    out([

        'type' =>
            'progress',

        'percent' =>
            round(
                ($i / max(1, $total)) * 50
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
    | Buscar usuario existente
    |--------------------------------------------------------------------------
    */

    $d =
        findUser(
            $sdUsers,
            $u
        );


    /*
    |--------------------------------------------------------------------------
    | Usuario ya existente
    |--------------------------------------------------------------------------
    */

    if ($d) {

        $did =
            (int) $d['id'];

        $stats['usersExisting']++;


        out([

            'type' =>
                'log',

            'level' =>
                'info',

            'message' =>
                $name .
                ' ya existe en SD (ID ' .
                $did .
                ').'

        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Crear usuario
    |--------------------------------------------------------------------------
    */

    else {

        $p =
            $u;


        /*
         * Nunca trasladar ID de SA.
         */

        unset(
            $p['id'],
            $p['password']
        );
    /*
 * Normalizar attributes para Traccar.
 *
 * Traccar espera attributes como objeto.
 * Si SA devuelve un array vacío/lista, lo convertimos
 * en un objeto vacío.
 */

    if (
        isset($p['attributes']) &&
        is_array($p['attributes']) &&
        array_is_list($p['attributes'])
        ) {
        $p['attributes'] = new stdClass();
        }

        /*
         * Asignar contraseña temporal
         * proporcionada por el administrador.
         */

        $p['password'] =
            $passwordTemporal;


        /*
         * Crear usuario en SD
         */

        $c =
            traccarPost(

                $sd['url'] .
                '/api/users',

                $p,

                $sd['cookies']

            );


        if (
            !$c['ok'] ||
            empty(
                $c['datos']['id']
            )
        ) {

            $stats['errors']++;


            out([

                'type' =>
                    'log',

                'level' =>
                    'error',

                'message' =>
                    $name .
                    ' no pudo crearse (HTTP ' .
                        ($c['http'] ?? 0) .
                            '). Motivo: ' .
                    motivoError($c['respuesta'] ?? '')

            ]);


            $pkg['users'][] = [

                'sourceId' =>
                    (int) $u['id'],

                'destinationId' =>
                    null,

                'name' =>
                    $name,

                'email' =>
                    $u['email'] ??
                    null,

                'passwordMigrated' =>
                    false,

                'passwordResetRequired' =>
                    true,

                'vehicles' =>
                    [],

                'errors' => [

                    'usuario' =>
                        'No creado'

                ]

            ];


            continue;
        }


        $d =
            $c['datos'];


        $did =
            (int) $d['id'];


        $stats['usersCreated']++;


        $sdUsers[] =
            $d;


        out([

            'type' =>
                'log',

            'level' =>
                'success',

            'message' =>
                $name .
                ' creado en SD (ID ' .
                $did .
                '). Contraseña temporal asignada.'

        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Guardar información en paquete
    |--------------------------------------------------------------------------
    |
    | IMPORTANTE:
    | Nunca guardamos la contraseña temporal.
    |
    */

    $pkg['users'][] = [

        'sourceId' =>
            (int) $u['id'],

        'destinationId' =>
            $did,

        'name' =>
            $name,

        'email' =>
            $u['email'] ??
            null,

        'phone' =>
            $u['phone'] ??
            null,

        'passwordMigrated' =>
            false,

        'passwordResetRequired' =>
            true,

        'resetUrl' =>
            $resetUrl,

        'vehicles' =>
            [],

        'errors' =>
            []

    ];

}


/*
|--------------------------------------------------------------------------
| FASE 2 — VEHÍCULOS
|--------------------------------------------------------------------------
*/

out([

    'type' =>
        'phase',

    'phase' =>
        2,

    'title' =>
        'Fase 2 — Vehículos y relaciones',

    'message' =>
        'Los usuarios ya existen en SD. Ahora se procesan sus vehículos.'

]);


/*
|--------------------------------------------------------------------------
| Obtener vehículos existentes de SD
|--------------------------------------------------------------------------
*/

$rd =
    traccarGet(

        $sd['url'] .
        '/api/devices',

        $sd['cookies']

    );


if (!$rd['ok']) {

    out([

        'type' =>
            'fatal',

        'message' =>
            'Error consultando dispositivos SD: ' .
            $rd['mensaje']

    ]);

    exit;
}


$sdDev =
    $rd['datos'];


/*
|--------------------------------------------------------------------------
| Procesar usuarios y vehículos
|--------------------------------------------------------------------------
*/

foreach (
    $pkg['users']
    as &$pu
) {


    if (
        empty(
            $pu['destinationId']
        )
    ) {

        continue;
    }


    /*
    |--------------------------------------------------------------------------
    | Obtener vehículos del usuario desde SA
    |--------------------------------------------------------------------------
    */

    $vr =
        traccarGet(

            $sa['url'] .
            '/api/devices?userId=' .
            $pu['sourceId'],

            $sa['cookies']

        );


    if (!$vr['ok']) {

        $stats['errors']++;


        out([

            'type' =>
                'log',

            'level' =>
                'error',

            'message' =>
                $pu['name'] .
                ' — no se pudieron consultar vehículos.'

        ]);


        continue;
    }


    out([

        'type' =>
            'log',

        'level' =>
            'info',

        'message' =>
            $pu['name'] .
            ' — ' .
            count($vr['datos']) .
            ' vehículo(s) encontrados.'

    ]);


    /*
    |--------------------------------------------------------------------------
    | Procesar vehículos
    |--------------------------------------------------------------------------
    */

    foreach (
        $vr['datos']
        as $v
    ) {


        $vn =
            $v['name'] ??
            'Vehículo';


        /*
        |--------------------------------------------------------------------------
        | Buscar vehículo existente
        |--------------------------------------------------------------------------
        */

        $d =
            findDev(
                $sdDev,
                $v
            );


        /*
        |--------------------------------------------------------------------------
        | Vehículo existente
        |--------------------------------------------------------------------------
        */

        if ($d) {

            $did =
                (int) $d['id'];


            out([

                'type' =>
                    'log',

                'level' =>
                    'info',

                'message' =>
                    $vn .
                    ' ya existe en SD (ID ' .
                    $did .
                    ').'

            ]);

        }


        /*
        |--------------------------------------------------------------------------
        | Crear vehículo
        |--------------------------------------------------------------------------
        */

        else {

            $p =
                $v;


            /*
             * Nunca trasladar ID de SA.
             */

            unset(
                $p['id']
            );


            /*
             * Normalizar attributes para Traccar.
             *
             * SA puede devolver:
             *
             *     "attributes": []
             *
             * pero Traccar espera un objeto:
             *
             *     "attributes": {}
             *
             * Si existen atributos reales como:
             *
             *     "attributes": {
             *         "anchor": true
             *     }
             *
             * se conservan sin modificación.
             */

            if (
                isset($p['attributes']) &&
                is_array($p['attributes']) &&
                array_is_list($p['attributes'])
            ) {

                $p['attributes'] =
                    new stdClass();

            }


            /*
             * Crear vehículo en SD
             */

            $c =
                traccarPost(

                    $sd['url'] .
                    '/api/devices',

                    $p,

                    $sd['cookies']

                );


            if (
                !$c['ok'] ||
                empty(
                    $c['datos']['id']
                )
            ) {

                $stats['errors']++;


                out([

                    'type' =>
                        'log',

                    'level' =>
                        'error',

                    'message' =>
                        $vn .
                        ' no pudo crearse (HTTP ' .
                        ($c['http'] ?? 0) .
                        ').'

                ]);


                continue;
            }


            $d =
                $c['datos'];


            $did =
                (int) $d['id'];


            $sdDev[] =
                $d;


            $stats['vehiclesCreated']++;


            out([

                'type' =>
                    'log',

                'level' =>
                    'success',

                'message' =>
                    $vn .
                    ' creado en SD (ID ' .
                    $did .
                    ').'

            ]);

        }


        /*
        |--------------------------------------------------------------------------
        | Crear relación usuario ↔ vehículo
        |--------------------------------------------------------------------------
        */

        $q =
            traccarPost(

                $sd['url'] .
                '/api/permissions',

                [

                    'userId' =>
                        $pu['destinationId'],

                    'deviceId' =>
                        $did

                ],

                $sd['cookies']

            );


        if ($q['ok']) {

            $stats['relations']++;


            out([

                'type' =>
                    'log',

                'level' =>
                    'success',

                'message' =>
                    'Relación creada: ' .
                    $pu['name'] .
                    ' ↔ ' .
                    $vn

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
                    'Falló relación de ' .
                    $vn .
                    ' (HTTP ' .
                    $q['http'] .
                    ').'

            ]);

        }


        /*
        |--------------------------------------------------------------------------
        | Guardar vehículo en paquete
        |--------------------------------------------------------------------------
        */

        $pu['vehicles'][] = [

            'sourceId' =>
                (int) (
                    $v['id'] ??
                    0
                ),

            'destinationId' =>
                $did,

            'name' =>
                $vn,

            'uniqueId' =>
                $v['uniqueId'] ??
                null

        ];

    }

}

unset($pu);


/*
|--------------------------------------------------------------------------
| Estadísticas
|--------------------------------------------------------------------------
*/

$pkg['stats'] =
    $stats;


/*
|--------------------------------------------------------------------------
| Guardar paquete JSON
|--------------------------------------------------------------------------
*/

$dir =
    __DIR__ .
    '/../packages';


if (!is_dir($dir)) {

    mkdir(
        $dir,
        0775,
        true
    );
}


$file =
    $dir .
    '/migration_' .
    date('Y-m-d_H-i-s') .
    '.json';


file_put_contents(

    $file,

    json_encode(

        $pkg,

        JSON_PRETTY_PRINT |
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES

    )

);


/*
|--------------------------------------------------------------------------
| Resultado final
|--------------------------------------------------------------------------
*/

out(

    [
        'type' =>
            'summary'
    ]

    + $stats

    + [

        'package' =>
            basename($file)

    ]

);