<?php

session_start();

if (empty($_SESSION['selected_users'])) {

    header('Location: usuarios.php');

    exit;
}

$e = $_SESSION['wizard_error'] ?? null;

unset($_SESSION['wizard_error']);

?>

<!doctype html>

<html lang="es">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width,initial-scale=1"
    >

    <title>Destino - Antiloss Migration Tool</title>

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


<div class="app">


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


        <div class="step active">

            <span class="step-number">
                3
            </span>

            <span>
                Destino
            </span>

        </div>


        <i></i>


        <div class="step">

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
                    PASO 3
                </small>

                <h1>
                    Servidor Destino
                </h1>

            </div>

        </div>


        <p class="description">

            Se migrarán

            <strong>
                <?= count($_SESSION['selected_users']) ?>
            </strong>

            usuario(s), incluyendo sus vehículos.

        </p>


        <?php if ($e): ?>

            <div class="error">

                <?= htmlspecialchars($e) ?>

            </div>

        <?php endif; ?>


        <!-- ==================================================
             AVISO SOBRE CONTRASEÑAS
        ================================================== -->

        <div class="warning-box">

            <strong>
                ⚠️ Importante sobre las contraseñas
            </strong>


            <p>

                Por seguridad, Traccar no permite obtener la
                contraseña actual de los usuarios mediante la API.

            </p>


            <p>

                Por lo tanto,

                <strong>
                    las contraseñas originales del Servidor Origen
                    no serán exportadas.
                </strong>

            </p>


            <p>

                Los usuarios nuevos recibirán la contraseña temporal
                que establezcas a continuación.

            </p>


            <p>

                Posteriormente podrán cambiarla desde:

            </p>


            <code>
                /reset-password
            </code>

        </div>


        <!-- ==================================================
             FORMULARIO DESTINO
        ================================================== -->

        <form
            id="destino-form"
            method="post"
            action="../api/conectar_destino.php"
        >


            <!-- SERVIDOR DESTINO -->

            <label>
                API del Servidor Destino
            </label>


            <input
                name="url"
                type="url"
                placeholder="https://servidor-destino.com"
                required
            >


            <!-- USUARIO ADMINISTRADOR -->

            <label>
                Usuario administrador
            </label>


            <input
                name="user"
                required
                autocomplete="username"
            >


            <!-- CONTRASEÑA ADMINISTRADOR -->

            <label>
                Contraseña del administrador
            </label>


            <input
                name="pass"
                type="password"
                required
                autocomplete="current-password"
            >


            <!-- CONTRASEÑA TEMPORAL -->

            <label>
                Contraseña temporal para los usuarios
            </label>


            <input
                name="password_temporal"
                type="password"
                placeholder="Contraseña temporal"
                minlength="6"
                required
            >


            <div class="info-box">

                Esta contraseña será utilizada únicamente para los
                usuarios nuevos creados en el Servidor Destino.

                <br><br>

                <strong>
                    No será guardada en el paquete de migración.
                </strong>

            </div>


            <!-- ==================================================
                 BOTÓN
            ================================================== -->

            <div class="form-footer destino-footer">

                <div class="connection-info">

                    <span class="status-dot"></span>

                    Conexión mediante API

                </div>


                <button
                    id="connect-destination"
                    type="submit"
                >

                    Conectar y comenzar

                    <span>
                        →
                    </span>

                </button>

            </div>


        </form>


        <!-- ==================================================
             NAVEGACIÓN
        ================================================== -->

        <div class="actions">

            <a href="usuarios.php">

                ← Usuarios

            </a>

        </div>


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
   FORMULARIO DESTINO
================================================== */

const destinoForm =
    document.getElementById(
        'destino-form'
    );


const connectDestination =
    document.getElementById(
        'connect-destination'
    );


destinoForm.addEventListener(
    'submit',
    function () {

        connectDestination.disabled =
            true;


        connectDestination.innerHTML = `

            <span class="button-spinner"></span>

            Conectando con destino...

        `;

    }
);

</script>


</body>

</html>