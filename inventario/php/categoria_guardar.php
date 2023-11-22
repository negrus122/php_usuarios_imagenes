<?php

require_once "main.php";

//almacenando datos
$nombre = limpiar_cadena($_POST['categoria_nombre']);
$ubicacion = limpiar_cadena($_POST['categoria_ubicacion']);

//verificando campos obligatorios
if ($nombre == '') {
    echo '
    <div class="notification is-danger is-light">
        <strong>¡Ocurrio un error inesperado!</strong><br>
        No has llenado todos los campos obligatorios.
    </div>
    ';
    exit();
}

//verificando integridad de los datos
if (verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{4,50}", $nombre)) {
    echo '
    <div class="notification is-danger is-light">
        <strong>¡Ocurrio un error inesperado!</strong><br>
        El Nombre no coincide con el formato solicitado.
    </div>
    ';
    exit();
}

if ($ubicacion !== "") {
    if (verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ ]{5,150}", $ubicacion)) {
        echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            La ubicacion no coincide con el formato solicitado.
        </div>
        ';
        exit();
    }
}

//verificando nombre
$check_nombre = coneccion();
$check_nombre = $check_nombre->query("SELECT categoria_nombre FROM categoria WHERE categoria_nombre='$nombre'");
if ($check_nombre->rowCount() > 0) {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            EL Nombre ingresado ya se encuentra registrado, por favor elija otro.
        </div>
        ';
    exit();
}
$check_nombre = null;

//guardando datos
$guardar_categoria = coneccion();
$guardar_categoria = $guardar_categoria->prepare("INSERT INTO categoria(categoria_nombre,categoria_ubicacion) VALUES(:nombre,:ubicacion)");

$marcadores = [
    ":nombre" => $nombre,
    ":ubicacion" => $ubicacion
];

$guardar_categoria->execute($marcadores);

if($guardar_categoria->rowCount()==1){
    echo '
        <div class="notification is-info is-light">
            <strong>¡Categoria Registrada!</strong><br>
            La Categoria se registro con exito
        </div>
        ';
}else{
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            No se pudo registrar la categoria, por favor intente nuevamente.
        </div>
        ';
}
$guardar_categoria=null;