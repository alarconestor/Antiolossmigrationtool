<?php
session_start();

if(empty($_SESSION['sa'])){
    header('Location: ../index.php');
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Usuarios</title>
<link rel="stylesheet" href="../assets/css/app.css">

<style>
/* ============================
   MODAL
============================ */

.modal-backdrop{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.55);
    display:flex;
    align-items:center;
    justify-content:center;
    z-index:1000;
    padding:20px;
}

.modal-backdrop.hide{
    display:none;
}

.modal{
    width:min(720px,100%);
    max-height:90vh;
    background:#fff;
    border-radius:14px;
    box-shadow:0 20px 60px rgba(0,0,0,.25);
    overflow:hidden;
    display:flex;
    flex-direction:column;
}

.modal-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:20px 22px;
    border-bottom:1px solid #e5e7eb;
}

.modal-head h2{
    margin:0;
    font-size:20px;
}

.modal-head small{
    display:block;
    color:#6b7280;
    margin-top:4px;
}

.modal-close{
    border:0;
    background:transparent;
    font-size:24px;
    cursor:pointer;
    color:#6b7280;
    padding:4px 8px;
}

.modal-tabs{
    display:flex;
    border-bottom:1px solid #e5e7eb;
    padding:0 20px;
    gap:4px;
}

.modal-tab{
    border:0;
    background:transparent;
    padding:13px 16px;
    color:#6b7280;
    cursor:pointer;
    border-bottom:2px solid transparent;
    font-weight:600;
}

.modal-tab.active{
    color:#111827;
    border-bottom-color:#2563eb;
}

.modal-body{
    padding:20px;
    overflow:auto;
}

.tab-content.hide{
    display:none;
}


/* ============================
   VEHÍCULOS
============================ */

.vehicle-toolbar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:15px;
    margin-bottom:14px;
    padding:12px 14px;
    background:#f8fafc;
    border-radius:9px;
}

.vehicle-toolbar label{
    display:flex;
    align-items:center;
    gap:8px;
    font-weight:600;
}

.vehicle-count{
    color:#6b7280;
    font-size:14px;
}

.vehicle-list{
    border:1px solid #e5e7eb;
    border-radius:10px;
    overflow:hidden;
}

.vehicle-row{
    display:flex;
    align-items:center;
    gap:12px;
    padding:13px 15px;
    border-bottom:1px solid #e5e7eb;
    cursor:pointer;
}

.vehicle-row:last-child{
    border-bottom:0;
}

.vehicle-row:hover{
    background:#f8fafc;
}

.vehicle-row input{
    width:18px;
    height:18px;
}

.vehicle-info{
    flex:1;
}

.vehicle-name{
    font-weight:600;
}

.vehicle-id{
    color:#6b7280;
    font-size:12px;
    margin-top:2px;
}

.vehicle-empty,
.vehicle-loading{
    padding:30px;
    text-align:center;
    color:#6b7280;
}


/* ============================
   FOOTER MODAL
============================ */

.modal-foot{
    display:flex;
    justify-content:flex-end;
    gap:10px;
    padding:16px 20px;
    border-top:1px solid #e5e7eb;
}

.btn-secondary{
    border:1px solid #d1d5db;
    background:#fff;
    color:#374151;
    padding:9px 16px;
    border-radius:8px;
    cursor:pointer;
}

.btn-primary{
    border:0;
    background:#2563eb;
    color:#fff;
    padding:9px 18px;
    border-radius:8px;
    cursor:pointer;
}

.btn-primary:hover{
    background:#1d4ed8;
}


/* ============================
   BOTÓN EDITAR
============================ */

.edit-user{
    border:0;
    background:transparent;
    cursor:pointer;
    font-size:18px;
    padding:5px 8px;
    border-radius:6px;
}

.edit-user:hover{
    background:#eef2ff;
}
</style>
</head>

<body>

<div class="app wide">

    <div class="brand">
        Traccar <span>Migration Tool</span>
    </div>

    <div class="steps">
        <b class="done">✓ Origen</b>
        <i></i>
        <b class="on">2 Usuarios</b>
        <i></i>
        <b>3 Destino</b>
        <i></i>
        <b>4 Migración</b>
    </div>

    <main class="card">

        <div class="head">

            <div>
                <small>PASO 2</small>

                <h1>Usuarios del origen</h1>

                <p>
                    <?=htmlspecialchars($_SESSION['sa']['url'])?>
                </p>
            </div>

            <button id="load">
                Cargar usuarios
            </button>

        </div>


        <div id="msg" class="msg"></div>


        <div id="selectbar" class="selectbar hide">

            <label>
                <input id="all" type="checkbox">
                Seleccionar todos
            </label>

            <span id="count">
                0 seleccionados
            </span>

        </div>


        <div id="table" class="table hide"></div>


        <div class="actions">

            <a href="../index.php">
                ← Volver
            </a>

            <button id="next" class="disabled">
                Continuar →
            </button>

        </div>

    </main>

</div>


<!-- ==================================================
     MODAL EDITAR USUARIO
================================================== -->

<div id="userModal" class="modal-backdrop hide">

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

                    <strong>Alertas</strong>

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

                    <strong>Geozonas</strong>

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
|
| Ejemplo:
|
| userMigrationConfig[15] = {
|     vehicles: [10,12,18]
| };
|
*/

const userMigrationConfig = {};

let currentUser = null;


/* ==================================================
   CARGAR USUARIOS
================================================== */

load.onclick = async () => {

    load.disabled = true;

    load.textContent =
        'Cargando...';


    try {

        let r =
            await fetch(
                '../api/importar_usuarios.php'
            );

        let d =
            await r.json();


        if(!d.ok)
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


        /*
         * Checkboxes de usuarios
         */

        document
            .querySelectorAll('.u')
            .forEach(c => {

                c.onchange =
                    update;

            });


        /*
         * Botones de editar
         */

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

    catch(e){

        document
            .getElementById('msg')
            .textContent =
                e.message;

    }

    finally{

        load.disabled = false;

        load.textContent =
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


function update(){

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

async function openUserModal(user){

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
        'Cargando vehículos...' +
        '</div>';


    allVehicles.checked =
        false;


    try {

        /*
         * Consultar vehículos del usuario.
         */

        let r =
            await fetch(
                '../api/importar_usuarios.php?userId=' +
                encodeURIComponent(
                    user.id
                )
            );


        let d =
            await r.json();


        if(!d.ok)
            throw Error(d.mensaje);


        let vehicles =
            d.vehiculos || [];


        /*
         * Primera vez:
         * todos seleccionados.
         */

        if(
            !userMigrationConfig[user.id]
        ){

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

    catch(e){

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
){

    if(!vehicles.length){

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

function updateVehicleCount(){

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

    if(!currentUser)
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

function closeUserModal(){

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

    if(e.target === userModal){

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


function activateTab(name){

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


    if(!ids.length)
        return;


    let r =
        await fetch(
            '../api/seleccionar_usuarios.php',
            {
                method:'POST',

                headers:{
                    'Content-Type':
                        'application/json'
                },

                body: JSON.stringify({
                    ids,
                    vehicles: userMigrationConfig
                    })
            }
        );


    let d =
        await r.json();


    if(d.ok){

        /*
         * Guardar temporalmente
         * las configuraciones.
         *
         * Todavía no afectan
         * la migración.
         */

        sessionStorage.setItem(

            'userMigrationConfig',

            JSON.stringify(
                userMigrationConfig
            )

        );


        location =
            'destino.php';

    }

    else{

        alert(
            d.mensaje
        );

    }

};


/* ==================================================
   ESCAPE HTML
================================================== */

function x(v){

    return String(v ?? '')
        .replace(
            /[&<>"']/g,

            c => ({

                '&':'&amp;',
                '<':'&lt;',
                '>':'&gt;',
                '"':'&quot;',
                "'":'&#039;'

            }[c])

        );

}

</script>

</body>
</html>