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

    <title>Comunicación - Antiloss Migration Tool</title>

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


        <div class="step done">

            <span class="step-number">
                ✓
            </span>

            <span>
                Migración
            </span>

        </div>


        <i></i>


        <div class="step active">

            <span class="step-number">
                5
            </span>

            <span>
                Comunicación
            </span>

        </div>


    </div>


    <!-- ==================================================
         TARJETA
    ================================================== -->

    <main class="card">


        <div class="card-header">

            <div class="section-icon">
                ✉
            </div>


            <div>

                <small>
                    PASO 5
                </small>

                <h1 id="title">
                    Comunicación con los usuarios
                </h1>

            </div>

        </div>


        <p
            id="sub"
            class="description"
        >
            La migración ha finalizado.
            Ahora puedes comunicar a los usuarios el cambio de plataforma.
        </p>


        <!-- ==================================================
             INFORMACIÓN
        ================================================== -->

        <div class="info-box">

            <strong>
                ¿Qué ocurrirá?
            </strong>

            <br>

            Se enviará un correo electrónico a los usuarios
            seleccionados informándoles que su cuenta ha sido
            migrada al nuevo servidor.

        </div>


        <!-- ==================================================
             MENSAJE
        ================================================== -->

        <label for="subject">
            Asunto del correo
        </label>

        <input
            id="subject"
            type="text"
            value="Tu cuenta ha sido migrada - Antiloss GPS"
        >


        <label for="body">
            Mensaje
        </label>

        <textarea
            id="body"
            rows="9"
            style="
                width:100%;
                padding:13px;
                border:1px solid #cfd5da;
                border-radius:3px;
                resize:vertical;
                font-family:Arial,Helvetica,sans-serif;
                font-size:14px;
                line-height:1.5;
                color:#263238;
                outline:none;
            "
        >Hola [NOMBRE],

Tu cuenta de Antiloss GPS ha sido migrada correctamente a nuestra nueva plataforma.

Para continuar utilizando el servicio debes ingresar al nuevo sistema y establecer una nueva contraseña.

Tu usuario es:

[EMAIL]

Si necesitas ayuda para ingresar, puedes comunicarte con nuestro equipo de soporte.

Saludos,
Antiloss Technologies</textarea>


        <!-- ==================================================
             PROGRESO
        ================================================== -->

        <div class="bar">

            <span id="fill"></span>

        </div>


        <!-- ==================================================
             ESTADO
        ================================================== -->

        <div
            id="current"
            class="current"
        >
            Listo para enviar la comunicación.
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
             ACCIONES
        ================================================== -->

        <div
            id="final-actions"
            class="final-actions hide"
        >

            <a
                href="../api/finalizar.php"
                class="finish-button"
            >
                Finalizar → 
            </a>

        </div>


        <!-- ==================================================
             ENVIAR
        ================================================== -->

        <button
            id="go"
            type="button"
        >
            Enviar comunicación
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

const fill =
    document.getElementById('fill');

const cur =
    document.getElementById('current');

const log =
    document.getElementById('log');

const sum =
    document.getElementById('summary');

const subject =
    document.getElementById('subject');

const body =
    document.getElementById('body');


/* ==================================================
   OCULTAR LOADER
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
   ENVIAR COMUNICACIÓN
================================================== */

go.onclick = async () => {

    go.disabled = true;


    go.innerHTML = `

        <span class="button-spinner"></span>

        Enviando...

    `;


    cur.className =
        'current';


    cur.textContent =
        'Preparando comunicación...';


    try {

        const response =
            await fetch(
                '../api/comunicacion.php',
                {
                    method: 'POST',

                    headers: {
                        'Content-Type':
                            'application/json'
                    },

                    body: JSON.stringify({

                        subject:
                            subject.value,

                        body:
                            body.value

                    })
                }
            );


        if (!response.ok) {

            throw Error(
                'HTTP ' +
                response.status
            );

        }


        const reader =
            response.body.getReader();


        const decoder =
            new TextDecoder();


        let buffer =
            '';


        while (true) {

            const result =
                await reader.read();


            if (result.done)
                break;


            buffer +=
                decoder.decode(
                    result.value
                );


            const lines =
                buffer.split('\n');


            buffer =
                lines.pop();


            lines.forEach(
                line => {

                    if (
                        line.trim()
                    ) {

                        try {

                            event(
                                JSON.parse(line)
                            );

                        }

                        catch (e) {

                            console.error(
                                e
                            );

                        }

                    }

                }
            );

        }


        if (
            buffer.trim()
        ) {

            event(
                JSON.parse(buffer)
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

        const d =
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
       COMPLETADO
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

            '<h2>✓ Comunicación completada</h2>' +

            '<p>' +

            'Correos enviados: <b>' +
            (e.sent ?? 0) +
            '</b> · ' +

            'Omitidos: <b>' +
            (e.skipped ?? 0) +
            '</b> · ' +

            'Errores: <b>' +
            (e.errors ?? 0) +
            '</b>' +

            '</p>';


        cur.className =
            'current success';


        cur.textContent =
            'Proceso finalizado correctamente';


        go.style.display =
            'none';


        document
            .getElementById(
                'final-actions'
            )
            .classList
            .remove(
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