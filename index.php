<?php
session_start();

$e = $_SESSION['wizard_error'] ?? null;
unset($_SESSION['wizard_error']);
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Antiloss Migration Tool</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>

<body>
    <div id="initial-loader" class="initial-loader">
    <div class="spinner"></div>
    </div>
    <div class="app">

        <header class="topbar">
            <div class="brand">
                <div class="brand-icon">A</div>

                <div>
                    <div class="brand-title">Traccar</div>
                    <div class="brand-subtitle">Migration Tool</div>
                </div>
            </div>

            <div class="version">v1.0</div>
        </header>


        <!-- PASOS DEL ASISTENTE -->

        <div class="steps">

            <div class="step active">
                <span class="step-number">1</span>
                <span>Origen</span>
            </div>

            <i></i>

            <div class="step">
                <span class="step-number">2</span>
                <span>Usuarios</span>
            </div>

            <i></i>

            <div class="step">
                <span class="step-number">3</span>
                <span>Destino</span>
            </div>

            <i></i>

            <div class="step">
                <span class="step-number">4</span>
                <span>Migración</span>
            </div>

        </div>


        <!-- TARJETA PRINCIPAL -->

        <main class="card">

            <div class="card-header">

                <div class="section-icon">⌁</div>

                <div>
                    <small>ASISTENTE DE MIGRACIÓN</small>
                    <h1>Servidor Origen</h1>
                </div>

            </div>


            <p class="description">
                Conecta el servidor Traccar que contiene la información
                que deseas trasladar.
            </p>


            <!-- ERROR -->

            <?php if ($e): ?>

                <div class="error">
                    <?= htmlspecialchars($e) ?>
                </div>

            <?php endif; ?>


            <!-- FORMULARIO -->

            <form id="origen-form"
                  method="post"
                  action="api/conectar_origen.php">


                <label>API del Servidor Origen</label>

                <input
                    name="url"
                    type="url"
                    placeholder="https://servidor-origen.com"
                    required
                >


                <label>Usuario administrador</label>

                <input
                    name="user"
                    required
                    autocomplete="username"
                >


                <label>Contraseña</label>

                <input
                    name="pass"
                    type="password"
                    required
                    autocomplete="current-password"
                >


                <!-- PIE DEL FORMULARIO -->

                <div class="form-footer">

                    <div id="loading" class="loading">

                        <div class="spinner"></div>

                        <span>
                            Conectando con el servidor...
                        </span>

                    </div>


                    <div id="connection-status"
                         class="connection-info">

                        <span class="status-dot"></span>

                        Conexión mediante API

                    </div>


                    <button id="connect-button"
                            type="submit">

                        Conectar con Origen

                        <span>→</span>

                    </button>

                </div>


            </form>

        </main>


        <!-- FOOTER -->

        <footer class="footer">

            <span>
                Antiloss Migration Tool
            </span>

            <span>
                Traccar API
            </span>

        </footer>

    </div>


    <!-- SPINNER AL ENVIAR -->

    <script>

        document
            .getElementById('origen-form')
            .addEventListener('submit', function () {

                const loading =
                    document.getElementById('loading');

                const status =
                    document.getElementById('connection-status');

                const button =
                    document.getElementById('connect-button');


                // Mostrar spinner

                loading.classList.add('active');


                // Ocultar estado normal

                status.style.display = 'none';


                // Desactivar botón

                button.disabled = true;

                button.style.opacity = '0.6';

                button.style.cursor = 'wait';


                // Cambiar texto

                button.firstChild.textContent =
                    ' Conectando... ';

                button.querySelector('span').textContent = '';

            });

    </script>

    <script>
window.addEventListener('load', function () {

    const loader = document.getElementById('initial-loader');

    setTimeout(function () {
        loader.classList.add('hide');
    }, 250);

});
</script>

</body>

</html>