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

    <title>Destino</title>

    <link
        rel="stylesheet"
        href="../assets/css/app.css"
    >

    <style>

        .warning-box {

            background: #fff8e6;

            border: 1px solid #e6c96a;

            color: #66520a;

            padding: 18px;

            border-radius: 9px;

            margin: 20px 0;

            line-height: 1.5;

        }

        .warning-box strong {

            color: #514000;

        }

        .warning-box p {

            margin: 8px 0;

            color: #66520a;

        }

        .warning-box code {

            display: inline-block;

            background: #fff;

            border: 1px solid #ddd;

            padding: 6px 10px;

            border-radius: 5px;

            font-family: monospace;

        }

        .info-box {

            background: #f4f6f8;

            border: 1px solid #dde1e5;

            color: #666;

            padding: 10px 12px;

            border-radius: 7px;

            margin-top: 8px;

            font-size: 13px;

        }

    </style>

</head>


<body>

<div class="app">


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

        <b class="on">
            3 Destino
        </b>

        <i></i>

        <b>
            4 Migración
        </b>

    </div>


    <main class="card">


        <small>
            PASO 3
        </small>


        <h1>
            Servidor Destino
        </h1>


        <p>

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


        <!--
        ------------------------------------------------------------
        AVISO SOBRE CONTRASEÑAS
        ------------------------------------------------------------
        -->

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


        <form
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
            >


            <!-- CONTRASEÑA ADMINISTRADOR -->

            <label>
                Contraseña del administrador
            </label>


            <input
                name="pass"
                type="password"
                required
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


            <br>


            <button type="submit">

                Conectar y comenzar →

            </button>


        </form>


        <div class="actions">

            <a href="usuarios.php">

                ← Usuarios

            </a>

        </div>


    </main>


</div>

</body>

</html>