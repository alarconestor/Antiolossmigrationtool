<?php

session_start();

header('Content-Type:application/json');

require_once __DIR__.'/../services/traccar.php';


if(empty($_SESSION['sa'])){

    echo json_encode([
        'ok'=>false,
        'mensaje'=>'Sesión no disponible'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Si se recibe userId
|--------------------------------------------------------------------------
|
| Devuelve los vehículos asignados a ese usuario.
|
| Ejemplo:
|
| importar_usuarios.php?userId=15
|
*/

if(isset($_GET['userId'])){

    $userId=(int)$_GET['userId'];


    if($userId<=0){

        echo json_encode([
            'ok'=>false,
            'mensaje'=>'ID de usuario no válido'
        ]);

        exit;
    }


    $r=traccarGet(

        rtrim($_SESSION['sa']['url'],'/') .
        '/api/devices?userId=' .
        $userId,

        $_SESSION['sa']['cookies']

    );


    if(!$r['ok']){

        echo json_encode($r);

        exit;
    }


    echo json_encode([

        'ok'=>true,

        'vehiculos'=>$r['datos']

    ],JSON_UNESCAPED_UNICODE);

    exit;
}


/*
|--------------------------------------------------------------------------
| Cargar usuarios
|--------------------------------------------------------------------------
|
| Comportamiento original.
|
*/

$r=traccarGet(

    rtrim($_SESSION['sa']['url'],'/') .
    '/api/users',

    $_SESSION['sa']['cookies']

);


if(!$r['ok']){

    echo json_encode($r);

    exit;
}


$_SESSION['source_users']=$r['datos'];


echo json_encode([

    'ok'=>true,

    'usuarios'=>$r['datos']

],JSON_UNESCAPED_UNICODE);