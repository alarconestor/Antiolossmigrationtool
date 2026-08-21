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

    <title>Migración - Antiloss Migration Tool</title>

    <link
        rel="stylesheet"
        href="../assets/css/app.css"
    >

</head>


<body>


<!-- ==================================================
     LOADER INICIAL
================================================== -->

<div id="initial-loader" class="initial-loader">

    <div class="spinner"></div>

</div>


<div class="app wide">


    <!-- ==================================================
         CABECERA
    ================================================== -->

    <header class="topbar">

        <div class="brand">

            <div class="brand-icon">
                A
            </div>

            <div>

                <div class="brand-title">
                    Antiloss
                </div>

                <div class="brand-subtitle">
                    Migration Tool
                </div>

            </div>

        </div>


        <div class="version">
            v1.0
        </div>

    </header>


    <!-- ==================================================
         PASOS
    ================================================== -->

    <div class="steps">


        <div class="step done">

            <span class="step-number">
                ✓
            </span>

            <span>
                Origen
            </span>

        </div>


        <i></i>


        <div class="step done">

            <span class="step-number">
                ✓
            </span>

            <span>
                Usuarios
            </span>

        </div>


        <i></i>


        <div class="step done">

            <span class="step-number">
                ✓
            </span>

            <span>
                Destino
            </span>

        </div>


        <i></i>


        <div class="step active">

            <span class="step-number">
                4
            </span>

            <span>
                Migración
            </span>

        </div>


    </div>


    <!-- ==================================================
         TARJETA
    ================================================== -->

    <main class="card">


        <div class="card-header">

            <div class="section-icon">
                ↗
            </div>


            <div>

                <small>
                    PASO 4
                </small>

                <h1 id="title">
                    Listo para migrar
                </h1>

            </div>

        </div>


        <p
            id="sub"
            class="description"
        >
            Fase 1: usuarios.
            Después Fase 2: vehículos y relaciones.
        </p>


        <!-- ==================================================
             PROGRESO
        ================================================== -->

        <div class="bar">

            <span id="fill"></span>

        </div>


        <!-- ==================================================
             FASES
        ================================================== -->

        <div class="phase">

            <b id="p1">
                1. Usuarios
            </b>

            <b id="p2">
                2. Vehículos y relaciones
            </b>

        </div>


        <!-- ==================================================
             ESTADO ACTUAL
        ================================================== -->

        <div
            id="current"
            class="current"
        >
            Pulsa iniciar para comenzar.
        </div>


        <!-- ==================================================
             LOG
        ================================================== -->

        <div
            id="log"
            class="log"
        ></div>


        <!-- ==================================================
             RESUMEN
        ================================================== -->

        <div
            id="summary"
            class="summary hide"
        ></div>


        <!-- ==================================================
             ACCIONES FINALES
        ================================================== -->

        <div
            id="final-actions"
            class="final-actions hide"
        >

            <a
                id="download-package"
                class="button"
                href="#"
                download
            >
                Descargar paquete de migración
            </a>


            <a
                id="finish"
                href="../index.php"
                class="finish-button"
            >
                Finalizar migración →
            </a>

        </div>


        <!-- ==================================================
             INICIAR
        ================================================== -->

        <button
            id="go"
            type="button"
        >
            Iniciar migración
        </button>


    </main>


    <!-- ==================================================
         FOOTER
    ================================================== -->

    <footer class="footer">

        <span>
            Antiloss Migration Tool
        </span>

        <span>
            Traccar API
        </span>

    </footer>


</div>


<script>

/* ==================================================
   ELEMENTOS
================================================== */

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


/* ==================================================
   OCULTAR LOADER INICIAL
================================================== */

window.addEventListener('load', function () {

    const loader =
        document.getElementById(
            'initial-loader'
        );


    setTimeout(function () {

        loader.classList.add(
            'hide'
        );

    }, 250);

});


/* ==================================================
   INICIAR MIGRACIÓN
================================================== */

go.onclick = async () => {

    go.disabled = true;


    go.innerHTML = `

        <span class="button-spinner"></span>

        Migrando...

    `;


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


    }

    catch (e) {

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


/* ==================================================
   PROCESAR EVENTOS
================================================== */

function event(e)
{

    /* ==================================================
       FASE
    ================================================== */

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


    /* ==================================================
       PROGRESO
    ================================================== */

    if (
        e.type === 'progress'
    ) {

        fill.style.width =
            e.percent + '%';


        cur.textContent =
            e.message;

    }


    /* ==================================================
       LOG
    ================================================== */

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


    /* ==================================================
       MIGRACIÓN COMPLETADA
    ================================================== */

    if (
        e.type === 'summary'
    ) {

        fill.style.width =
            '100%';


        sum.classList.remove(
            'hide'
        );


        sum.innerHTML =

            '<h2>✓ Migración completada</h2>' +

            '<p>' +

            'Usuarios creados: <b>' +
            e.usersCreated +
            '</b> · ' +

            'Existentes: <b>' +
            e.usersExisting +
            '</b> · ' +

            'Vehículos creados: <b>' +
            e.vehiclesCreated +
            '</b> · ' +

            'Relaciones: <b>' +
            e.relations +
            '</b> · ' +

            'Errores: <b>' +
            e.errors +
            '</b>' +

            '</p>' +

            '<p>Paquete: <code>' +
            e.package +
            '</code></p>';


        cur.className =
            'current success';


        cur.textContent =
            'Proceso finalizado correctamente';


        go.style.display =
            'none';


        const acciones =
            document.getElementById(
                'final-actions'
            );


        const descarga =
            document.getElementById(
                'download-package'
            );


        descarga.href =
            '../packages/' +
            encodeURIComponent(
                e.package
            );


        acciones.classList.remove(
            'hide'
        );

    }


    /* ==================================================
       ERROR FATAL
    ================================================== */

    if (
        e.type === 'fatal'
    ) {

        cur.className =
            'current error';


        cur.textContent =
            e.message;


        go.disabled =
            false;


        go.textContent =
            'Reintentar';

    }

}

</script>


</body>

</html>