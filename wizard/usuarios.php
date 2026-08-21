<?php
session_start();

if (empty($_SESSION['sa'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!doctype html>
<html lang="es">

<head>

    <meta charset="utf-8">

    <meta name="viewport"
          content="width=device-width,initial-scale=1">

    <title>Usuarios - Antiloss Migration Tool</title>

    <link rel="stylesheet"
          href="../assets/css/app.css">

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

        <div class="step active">

            <span class="step-number">
                2
            </span>

            <span>
                Usuarios
            </span>

        </div>

        <i></i>

        <div class="step">

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
         TARJETA PRINCIPAL
    ================================================== -->

    <main class="card">


        <div class="head">

            <div>

                <small>
                    PASO 2
                </small>

                <h1>
                    Usuarios del origen
                </h1>

                <p>
                    <?= htmlspecialchars($_SESSION['sa']['url']) ?>
                </p>

            </div>


            <button id="load">

                Cargar usuarios

            </button>

        </div>


        <div id="msg"
             class="msg">
        </div>


        <div id="selectbar"
             class="selectbar hide">

            <label>

                <input
                    id="all"
                    type="checkbox"
                >

                Seleccionar todos

            </label>


            <span id="count">

                0 seleccionados

            </span>

        </div>


        <div id="table"
             class="table hide">
        </div>


        <div class="actions">

            <a href="../index.php">
                ← Volver
            </a>

            <button id="next"
                    class="disabled">

                Continuar →

            </button>

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


<!-- ==================================================
     MODAL EDITAR USUARIO
================================================== -->

<div id="userModal"
     class="modal-backdrop hide">

    <div class="modal">


        <!-- CABECERA -->

        <div class="modal-head">

            <div>

                <h2 id="modalUserName">
                    Editar usuario
                </h2>

                <small id="modalUserEmail"></small>

            </div>


            <button
                id="closeModal"
                class="modal-close"
            >
                ×
            </button>

        </div>


        <!-- PESTAÑAS -->

        <div class="modal-tabs">

            <button
                class="modal-tab active"
                data-tab="vehicles"
            >
                🚗 Vehículos
            </button>


            <button
                class="modal-tab"
                data-tab="alerts"
            >
                🔔 Alertas
            </button>


            <button
                class="modal-tab"
                data-tab="geofences"
            >
                📍 Geozonas
            </button>

        </div>


        <!-- CONTENIDO -->

        <div class="modal-body">


            <!-- VEHÍCULOS -->

            <div
                id="tab-vehicles"
                class="tab-content"
            >

                <div class="vehicle-toolbar">

                    <label>

                        <input
                            id="allVehicles"
                            type="checkbox"
                        >

                        Seleccionar todos

                    </label>


                    <span
                        id="vehicleCount"
                        class="vehicle-count"
                    >
                        0 de 0 seleccionados
                    </span>

                </div>


                <div
                    id="vehicleList"
                    class="vehicle-list"
                >

                    <div class="vehicle-loading">

                        Cargando vehículos...

                    </div>

                </div>

            </div>


            <!-- ALERTAS -->

            <div
                id="tab-alerts"
                class="tab-content hide"
            >

                <div class="vehicle-empty">

                    <strong>
                        Alertas
                    </strong>

                    <br><br>

                    Esta sección estará disponible
                    próximamente.

                </div>

            </div>


            <!-- GEOZONAS -->

            <div
                id="tab-geofences"
                class="tab-content hide"
            >

                <div class="vehicle-empty">

                    <strong>
                        Geozonas
                    </strong>

                    <br><br>

                    Esta sección estará disponible
                    próximamente.

                </div>

            </div>


        </div>


        <!-- BOTONES -->

        <div class="modal-foot">

            <button
                id="cancelModal"
                class="btn-secondary"
            >
                Cancelar
            </button>


            <button
                id="saveModal"
                class="btn-primary"
            >
                Guardar
            </button>

        </div>

    </div>

</div>


<script>

/* ==================================================
   ELEMENTOS PRINCIPALES
================================================== */

const load =
    document.getElementById('load');

const all =
    document.getElementById('all');

const table =
    document.getElementById('table');

const bar =
    document.getElementById('selectbar');

const count =
    document.getElementById('count');

const next =
    document.getElementById('next');


/* ==================================================
   ELEMENTOS DEL MODAL
================================================== */

const userModal =
    document.getElementById('userModal');

const closeModal =
    document.getElementById('closeModal');

const cancelModal =
    document.getElementById('cancelModal');

const saveModal =
    document.getElementById('saveModal');

const modalUserName =
    document.getElementById('modalUserName');

const modalUserEmail =
    document.getElementById('modalUserEmail');

const vehicleList =
    document.getElementById('vehicleList');

const allVehicles =
    document.getElementById('allVehicles');

const vehicleCount =
    document.getElementById('vehicleCount');


/*
|-------------------------------------------------------------------------- 
| Configuración temporal de migración
|-------------------------------------------------------------------------- 
*/

const userMigrationConfig = {};

let currentUser = null;


/* ==================================================
   CARGAR USUARIOS
================================================== */

load.onclick = async () => {

    load.disabled = true;

    load.innerHTML = `
        <span class="button-spinner"></span>
        Cargando usuarios...
    `;


    try {

        let r =
            await fetch(
                '../api/importar_usuarios.php'
            );

        let d =
            await r.json();


        if (!d.ok)
            throw Error(d.mensaje);


        table.innerHTML =

            '<table>' +

            '<thead>' +

            '<tr>' +

            '<th>ID SA</th>' +

            '<th>Nombre</th>' +

            '<th>Email</th>' +

            '<th>Teléfono</th>' +

            '<th>Estado</th>' +

            '<th>Expiración</th>' +

            '<th>Acciones</th>' +

            '<th>✓</th>' +

            '</tr>' +

            '</thead>' +

            '<tbody>' +

            d.usuarios.map(u =>

                '<tr>' +

                '<td>' +
                x(u.id) +
                '</td>' +

                '<td>' +
                x(u.name) +
                '</td>' +

                '<td>' +
                x(u.email) +
                '</td>' +

                '<td>' +
                x(u.phone || '—') +
                '</td>' +

                '<td>' +

                (
                    u.disabled
                    ? 'Deshabilitado'
                    : 'Activo'
                ) +

                '</td>' +

                '<td>' +
                x(
                    u.expirationTime ||
                    '—'
                ) +
                '</td>' +

                '<td>' +

                '<button ' +

                'class="edit-user" ' +

                'title="Editar usuario" ' +

                'data-user-id="' +
                x(u.id) +
                '" ' +

                'data-user-name="' +
                x(u.name) +
                '" ' +

                'data-user-email="' +
                x(u.email) +
                '">' +

                '✏️' +

                '</button>' +

                '</td>' +

                '<td>' +

                '<input ' +

                'class="u" ' +

                'type="checkbox" ' +

                'value="' +
                x(u.id) +
                '">' +

                '</td>' +

                '</tr>'

            ).join('') +

            '</tbody>' +

            '</table>';


        table.classList.remove('hide');

        bar.classList.remove('hide');


        document
            .querySelectorAll('.u')
            .forEach(c => {

                c.onchange =
                    update;

            });


        document
            .querySelectorAll('.edit-user')
            .forEach(btn => {

                btn.onclick = () => {

                    const user = {

                        id:
                            Number(
                                btn.dataset.userId
                            ),

                        name:
                            btn.dataset.userName,

                        email:
                            btn.dataset.userEmail

                    };


                    openUserModal(user);

                };

            });


        update();

    }

    catch (e) {

        document
            .getElementById('msg')
            .textContent =
                e.message;

    }

    finally {

        load.disabled = false;

        load.innerHTML =
            'Cargar usuarios';

    }

};


/* ==================================================
   SELECCIONAR TODOS LOS USUARIOS
================================================== */

all.onchange = () => {

    document
        .querySelectorAll('.u')
        .forEach(c => {

            c.checked =
                all.checked;

        });

    update();

};


function update() {

    let a = [
        ...document.querySelectorAll('.u')
    ];


    let s =
        a.filter(
            c => c.checked
        );


    count.textContent =
        s.length +
        ' seleccionados';


    all.checked =
        a.length > 0 &&
        s.length === a.length;


    next.classList.toggle(
        'disabled',
        !s.length
    );

}


/* ==================================================
   ABRIR MODAL
================================================== */

async function openUserModal(user) {

    currentUser = user;


    modalUserName.textContent =
        'Editar usuario: ' +
        user.name;


    modalUserEmail.textContent =
        user.email || '';


    userModal.classList.remove(
        'hide'
    );


    activateTab('vehicles');


    vehicleList.innerHTML =

        '<div class="vehicle-loading">' +

        '<span class="inline-spinner"></span>' +

        'Cargando vehículos...' +

        '</div>';


    allVehicles.checked =
        false;


    try {

        let r =
            await fetch(
                '../api/importar_usuarios.php?userId=' +
                encodeURIComponent(
                    user.id
                )
            );


        let d =
            await r.json();


        if (!d.ok)
            throw Error(d.mensaje);


        let vehicles =
            d.vehiculos || [];


        if (
            !userMigrationConfig[user.id]
        ) {

            userMigrationConfig[user.id] = {

                vehicles:
                    vehicles.map(
                        v => Number(v.id)
                    )

            };

        }


        renderVehicles(

            vehicles,

            userMigrationConfig[
                user.id
            ].vehicles

        );

    }

    catch (e) {

        vehicleList.innerHTML =

            '<div class="vehicle-empty">' +

            'No se pudieron cargar los vehículos.' +

            '<br><br>' +

            x(e.message) +

            '</div>';

    }

}


/* ==================================================
   MOSTRAR VEHÍCULOS
================================================== */

function renderVehicles(
    vehicles,
    selectedIds
) {

    if (!vehicles.length) {

        vehicleList.innerHTML =

            '<div class="vehicle-empty">' +

            'Este usuario no tiene vehículos asignados.' +

            '</div>';


        allVehicles.checked =
            false;


        updateVehicleCount();

        return;

    }


    vehicleList.innerHTML =

        vehicles.map(v => {

            const id =
                Number(v.id);


            const checked =
                selectedIds.includes(id)
                ? 'checked'
                : '';


            return (

                '<label class="vehicle-row">' +

                '<input ' +

                'class="vehicle" ' +

                'type="checkbox" ' +

                'value="' +
                id +
                '" ' +

                checked +

                '>' +

                '<div class="vehicle-info">' +

                '<div class="vehicle-name">' +

                x(
                    v.name ||
                    'Sin nombre'
                ) +

                '</div>' +

                '<div class="vehicle-id">' +

                'ID: ' +
                x(v.id) +

                (
                    v.uniqueId
                    ? ' · ' +
                      x(v.uniqueId)
                    : ''
                ) +

                '</div>' +

                '</div>' +

                '</label>'

            );

        }).join('');


    document
        .querySelectorAll('.vehicle')
        .forEach(c => {

            c.onchange =
                updateVehicleCount;

        });


    updateVehicleCount();

}


/* ==================================================
   CONTADOR DE VEHÍCULOS
================================================== */

function updateVehicleCount() {

    const vehicles = [

        ...document
            .querySelectorAll(
                '.vehicle'
            )

    ];


    const selected =
        vehicles.filter(
            c => c.checked
        );


    vehicleCount.textContent =

        selected.length +
        ' de ' +
        vehicles.length +
        ' seleccionados';


    allVehicles.checked =

        vehicles.length > 0 &&
        selected.length === vehicles.length;

}


/* ==================================================
   SELECCIONAR TODOS LOS VEHÍCULOS
================================================== */

allVehicles.onchange = () => {

    document
        .querySelectorAll('.vehicle')
        .forEach(c => {

            c.checked =
                allVehicles.checked;

        });


    updateVehicleCount();

};


/* ==================================================
   GUARDAR CONFIGURACIÓN
================================================== */

saveModal.onclick = () => {

    if (!currentUser)
        return;


    const selectedIds = [

        ...document
            .querySelectorAll(
                '.vehicle:checked'
            )

    ].map(
        c => Number(c.value)
    );


    userMigrationConfig[
        currentUser.id
    ] = {

        vehicles:
            selectedIds

    };


    closeUserModal();

};


/* ==================================================
   CERRAR MODAL
================================================== */

function closeUserModal() {

    userModal.classList.add(
        'hide'
    );

    currentUser = null;

}


closeModal.onclick =
    closeUserModal;


cancelModal.onclick =
    closeUserModal;


userModal.onclick = e => {

    if (e.target === userModal) {

        closeUserModal();

    }

};


/* ==================================================
   PESTAÑAS
================================================== */

document
    .querySelectorAll('.modal-tab')
    .forEach(tab => {

        tab.onclick = () => {

            activateTab(
                tab.dataset.tab
            );

        };

    });


function activateTab(name) {

    document
        .querySelectorAll('.modal-tab')
        .forEach(tab => {

            tab.classList.toggle(

                'active',

                tab.dataset.tab === name

            );

        });


    document
        .querySelectorAll('.tab-content')
        .forEach(content => {

            content.classList.toggle(

                'hide',

                content.id !==
                'tab-' + name

            );

        });

}


/* ==================================================
   CONTINUAR
================================================== */

next.onclick = async () => {

    let ids = [

        ...document
            .querySelectorAll(
                '.u:checked'
            )

    ].map(
        c => +c.value
    );


    if (!ids.length)
        return;


    let r =
        await fetch(
            '../api/seleccionar_usuarios.php',
            {
                method: 'POST',

                headers: {
                    'Content-Type':
                        'application/json'
                },

                body: JSON.stringify({
                    ids,
                    vehicles:
                        userMigrationConfig
                })
            }
        );


    let d =
        await r.json();


    if (d.ok) {

        sessionStorage.setItem(

            'userMigrationConfig',

            JSON.stringify(
                userMigrationConfig
            )

        );


        location =
            'destino.php';

    }

    else {

        alert(
            d.mensaje
        );

    }

};


/* ==================================================
   ESCAPE HTML
================================================== */

function x(v) {

    return String(v ?? '')
        .replace(
            /[&<>"']/g,

            c => ({

                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'

            }[c])

        );

}


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

</script>

</body>
</html>