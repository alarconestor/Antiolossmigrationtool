<?php

session_start();

if (
    empty($_SESSION['sd']) ||
    empty($_SESSION['selected_users'])
) {
    header('Location: ../index.php');
    exit;
}

?>

<!doctype html>

<html lang="es">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width,initial-scale=1"
    >

    <title>Migración</title>

    <link
        rel="stylesheet"
        href="../assets/css/app.css"
    >

</head>


<body>

<div class="app wide">


    <div class="brand">

        Traccar <span>Migration Tool</span>

    </div>


    <div class="steps">

        <b class="done">
            ✓ Origen
        </b>

        <i></i>

        <b class="done">
            ✓ Usuarios
        </b>

        <i></i>

        <b class="done">
            ✓ Destino
        </b>

        <i></i>

        <b class="on">
            4 Migración
        </b>

    </div>


    <main class="card">

        <small>
            PASO 4
        </small>


        <h1 id="title">
            Listo para migrar
        </h1>


        <p id="sub">
            Fase 1: usuarios.
            Después Fase 2: vehículos y relaciones.
        </p>


        <div class="bar">

            <span id="fill"></span>

        </div>


        <div class="phase">

            <b id="p1">
                1. Usuarios
            </b>

            <b id="p2">
                2. Vehículos y relaciones
            </b>

        </div>


        <div
            id="current"
            class="current"
        >
            Pulsa iniciar para comenzar.
        </div>


        <div
            id="log"
            class="log"
        ></div>


        <div id="summary" class="summary hide"></div>

<div id="final-actions" class="hide">
    <a id="download-package"
       class="button"
       href="#"
       download>
        Descargar paquete de migración
    </a>
     <!--
        ------------------------------------------------------------
        BOTÓN FINALIZAR
        ------------------------------------------------------------
        -->

        <a
            id="finish"
            href="../index.php"
            class="finish-button"
            style="display:none;"
        >
            Finalizar migración →
        </a>
   
</div>

<button id="go">Iniciar migración</button>


       


    </main>

</div>


<script>

const go =
    document.getElementById('go');

const finish =
    document.getElementById('finish');

const fill =
    document.getElementById('fill');

const cur =
    document.getElementById('current');

const log =
    document.getElementById('log');

const sum =
    document.getElementById('summary');


/*
|--------------------------------------------------------------------------
| Iniciar migración
|--------------------------------------------------------------------------
*/

go.onclick = async () => {

    go.disabled = true;

    go.textContent =
        'Migrando...';


    try {

        let r =
            await fetch(
                '../api/migrar.php',
                {
                    method: 'POST'
                }
            );


        if (!r.ok) {

            throw Error(
                'HTTP ' + r.status
            );

        }


        let reader =
            r.body.getReader();

        let dec =
            new TextDecoder();

        let buf =
            '';


        while (1) {

            let z =
                await reader.read();


            if (z.done)
                break;


            buf +=
                dec.decode(
                    z.value
                );


            let a =
                buf.split('\n');


            buf =
                a.pop();


            a.forEach(
                line => {

                    if (
                        line.trim()
                    ) {

                        event(
                            JSON.parse(line)
                        );

                    }

                }
            );

        }


        if (
            buf.trim()
        ) {

            event(
                JSON.parse(buf)
            );

        }


    } catch (e) {

        cur.className =
            'current error';

        cur.textContent =
            'Error: ' +
            e.message;


        go.disabled =
            false;

        go.textContent =
            'Reintentar';

    }

};


/*
|--------------------------------------------------------------------------
| Procesar eventos de migración
|--------------------------------------------------------------------------
*/

function event(e)
{

    /*
    |--------------------------------------------------------------------------
    | Fase
    |--------------------------------------------------------------------------
    */

    if (
        e.type === 'phase'
    ) {

        document
            .getElementById('p1')
            .classList
            .toggle(
                'active',
                e.phase === 1
            );


        document
            .getElementById('p2')
            .classList
            .toggle(
                'active',
                e.phase === 2
            );


        document
            .getElementById('title')
            .textContent =
            e.title;


        document
            .getElementById('sub')
            .textContent =
            e.message;

    }


    /*
    |--------------------------------------------------------------------------
    | Progreso
    |--------------------------------------------------------------------------
    */

    if (
        e.type === 'progress'
    ) {

        fill.style.width =
            e.percent + '%';


        cur.textContent =
            e.message;

    }


    /*
    |--------------------------------------------------------------------------
    | Log
    |--------------------------------------------------------------------------
    */

    if (
        e.type === 'log'
    ) {

        let d =
            document.createElement(
                'div'
            );


        d.className =
            e.level;


        d.textContent =
            (
                e.level === 'success'
                    ? '✓ '
                    : e.level === 'error'
                        ? '✗ '
                        : '→ '
            )
            +
            e.message;


        log.appendChild(d);


        log.scrollTop =
            log.scrollHeight;

    }


    /*
    |--------------------------------------------------------------------------
    | MIGRACIÓN COMPLETADA
    |--------------------------------------------------------------------------
    */

    if (e.type === 'summary') {

    fill.style.width = '100%';

    sum.classList.remove('hide');

    sum.innerHTML =
        '<h2>✓ Migración completada</h2>' +
        '<p>' +
        'Usuarios creados: <b>' + e.usersCreated + '</b> · ' +
        'Existentes: <b>' + e.usersExisting + '</b> · ' +
        'Vehículos creados: <b>' + e.vehiclesCreated + '</b> · ' +
        'Relaciones: <b>' + e.relations + '</b> · ' +
        'Errores: <b>' + e.errors + '</b>' +
        '</p>' +
        '<p>Paquete: <code>' + e.package + '</code></p>';

    cur.textContent =
        'Proceso finalizado correctamente';

    go.style.display = 'none';

    const acciones =
        document.getElementById('final-actions');

    const descarga =
        document.getElementById('download-package');

    descarga.href =
        '../packages/' +
        encodeURIComponent(e.package);

    acciones.classList.remove('hide');
}


        /*
        * Mostrar botón finalizar
        */

        finish.style.display =
            'inline-block';

    }


    /*
    |--------------------------------------------------------------------------
    | Error fatal
    |--------------------------------------------------------------------------
    */

    if (
        e.type === 'fatal'
    ) {

        cur.className =
            'current error';


        cur.textContent =
            e.message;

    }



</script>


<style>

/*
|--------------------------------------------------------------------------
| Botón finalizar
|--------------------------------------------------------------------------
*/

.finish-button {

    display: inline-block;

    margin-top: 15px;

    padding: 12px 22px;

    background: #222;

    color: #fff;

    text-decoration: none;

    border-radius: 7px;

    font-size: 15px;

    cursor: pointer;

    transition:
        background .2s ease,
        transform .1s ease;

}


.finish-button:hover {

    background: #444;

}


.finish-button:active {

    transform: translateY(1px);

}

</style>


</body>

</html>