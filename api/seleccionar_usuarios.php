<?php

session_start();

header('Content-Type:application/json');


$in = json_decode(
    file_get_contents('php://input'),
    true
);


$ids = array_map(
    'intval',
    $in['ids'] ?? []
);


/*
|--------------------------------------------------------------------------
| Configuración de vehículos recibida
|--------------------------------------------------------------------------
|
| La estructura enviada por usuarios.php es:
|
| {
|     "357": {
|         "vehicles": [25, 30]
|     },
|     "358": {
|         "vehicles": []
|     }
| }
|
*/


$vehiclesInput =
    $in['vehicles'] ?? [];


$selectedVehicles = [];


foreach(
    $vehiclesInput as $userId => $config
){

    $userId =
        (int)$userId;


    if($userId <= 0){
        continue;
    }


    /*
     * Extraer la lista real de vehículos.
     */

    if(
        is_array($config) &&
        isset($config['vehicles']) &&
        is_array($config['vehicles'])
    ){

        $vehicles =
            $config['vehicles'];

    }

    /*
     * Compatibilidad por si algún día
     * recibimos directamente un array.
     */

    elseif(
        is_array($config)
    ){

        $vehicles =
            $config;

    }

    else{

        $vehicles = [];

    }


    /*
     * Normalizar IDs.
     */

    $selectedVehicles[$userId] =
        array_values(
            array_unique(
                array_map(
                    'intval',
                    $vehicles
                )
            )
        );

}


/*
|--------------------------------------------------------------------------
| Usuarios seleccionados
|--------------------------------------------------------------------------
*/

$out = [];


foreach(
    $_SESSION['source_users'] ?? []
    as $u
){

    if(
        in_array(
            (int)$u['id'],
            $ids,
            true
        )
    ){

        $out[] = $u;

    }

}


if(!$out){

    echo json_encode([

        'ok'=>false,

        'mensaje'=>
            'No hay usuarios seleccionados'

    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| Guardar usuarios
|--------------------------------------------------------------------------
*/

$_SESSION['selected_users'] =
    $out;


/*
|--------------------------------------------------------------------------
| Guardar vehículos seleccionados
|--------------------------------------------------------------------------
*/

$_SESSION['selected_vehicles'] =
    $selectedVehicles;


/*
|--------------------------------------------------------------------------
| Respuesta
|--------------------------------------------------------------------------
*/

echo json_encode([

    'ok'=>true,

    'total'=>count($out),

    'vehiculos'=>
        $selectedVehicles

],JSON_UNESCAPED_UNICODE);