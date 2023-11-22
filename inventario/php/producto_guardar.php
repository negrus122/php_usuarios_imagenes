<?php
require_once "../inc/session_start.php";
require_once "main.php";

//almacenando datos
$codigo = limpiar_cadena($_POST['producto_codigo']);
$nombre = limpiar_cadena($_POST['producto_nombre']);

$precio = limpiar_cadena($_POST['producto_precio']);
$stock = limpiar_cadena($_POST['producto_stock']);
$categoria = limpiar_cadena($_POST['producto_categoria']);

//verificando campos obligatorios

if ($codigo == "" || $nombre == "" || $precio == "" || $stock == "" || $categoria == "") {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            No has llenado todos los campos que son obligatorios
        </div>
        ';
    exit();
}

//verificando integridad de los datos 
if (verificar_datos("[a-zA-Z0-9- ]{1,70}", $codigo)) {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            El Codigo de Barras no coincide con el formato solicitado
        </div>
        ';
    exit();
}


if (verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,$#\-\/ ]{1,70}", $nombre)) {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            El Nombre no coincide con el formato solicitado
        </div>
        ';
    exit();
}

if (verificar_datos("[0-9.]{1,25}", $precio)) {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            El Precio no coincide con el formato solicitado
        </div>
        ';
    exit();
}

if (verificar_datos("[0-9]{1,25}", $stock)) {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            El Stock no coincide con el formato solicitado
        </div>
        ';
    exit();
}

//verificando codigo
$check_codigo = coneccion();
$check_codigo = $check_codigo->query("SELECT producto_codigo FROM producto WHERE producto_codigo= '$codigo'");
if ($check_codigo->rowCount() > 0) {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            El Codigo de Barras ingresado ya se encuentra resgistrado, por favor elija otro
        </div>
        ';
    exit();
}
$check_codigo = null;

//verificando nombre
$check_nombre = coneccion();
$check_nombre = $check_nombre->query("SELECT producto_nombre FROM producto WHERE producto_nombre= '$nombre'");
if ($check_nombre->rowCount() > 0) {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            El Nombre ingresado ya se encuentra resgistrado, por favor elija otro
        </div>
        ';
    exit();
}
$check_nombre = null;

//verificando categotia
$check_categoria = coneccion();
$check_categoria = $check_categoria->query("SELECT categoria_id FROM categoria WHERE categoria_id= '$categoria'");
if ($check_categoria->rowCount() <= 0) {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            La Categoria seleccionada no existe.
        </div>
        ';
    exit();
}
$check_categoria = null;

//directorio de imagenes
$img_dir = "../img/producto/";

//comprobar si se selecciono una imagen
if ($_FILES['producto_foto']['name'] != "" && $_FILES['producto_foto']['size'] > 0) {
    //creando directorio de imagenes
    if (!file_exists($img_dir)) {
        if (!mkdir($img_dir, 0777)) {
            echo '
                <div class="notification is-danger is-light">
                    <strong>¡Ocurrio un error inesperado!</strong><br>
                    Error al crear el directorio.
                </div>
            ';
            exit();
        }
    }
    //verificando el formato de imagenes
    if(mime_content_type($_FILES['producto_foto']['tmp_name'])!="image/jpeg" && mime_content_type($_FILES['producto_foto']['tmp_name'])!="image/png") {
        echo '
                <div class="notification is-danger is-light">
                    <strong>¡Ocurrio un error inesperado!</strong><br>
                    La imagen que ha seleccionado es de un formato no permitido.
                </div>
        ';
        exit();
    }
    //verificando peso de de Imagen
    if(($_FILES['producto_foto']['size']/1024)>3072){
        echo '
                <div class="notification is-danger is-light">
                    <strong>¡Ocurrio un error inesperado!</strong><br>
                    La imagen que ha seleccionado supera el peso permitido.
                </div>
        ';
        exit();
    }
    //extension de la imagen
    switch(mime_content_type($_FILES['producto_foto']['tmp_name'])){
        case 'image/jpeg':
            $img_ext=".jpg";
            break;
            case 'image/png':
                $img_ext=".png";
                break;
    }

    chmod($img_dir,0777);
    $img_nombre=renombrar_fotos($nombre);
    $foto=$img_nombre.$img_ext;

    // moviendo imagen al directorio
    if(!move_uploaded_file($_FILES['producto_foto']['tmp_name'],$img_dir.$foto)){
        echo '
                <div class="notification is-danger is-light">
                    <strong>¡Ocurrio un error inesperado!</strong><br>
                    No podemos subir la imagen al sistema en este momento.
                </div>
        ';
        exit();
    }
} else {
    $foto = "";
}

//guardando datos
$guardar_producto=coneccion();
$guardar_producto = $guardar_producto->prepare("INSERT INTO producto(producto_codigo,producto_nombre,producto_precio,producto_stock,producto_foto,categoria_id,usuario_id) VALUES(:codigo,:nombre,:precio,:stock,:foto,:categoria,:usuario)");

$marcadores=[
    ":codigo"=> $codigo,
    ":nombre"=> $nombre,
    ":precio"=> $precio,
    ":stock"=> $stock,
    ":foto"=> $foto,
    ":categoria"=> $categoria,
    ":usuario"=> $_SESSION['id']
];

$guardar_producto->execute($marcadores);

if($guardar_producto->rowCount()==1){
    echo '
        <div class="notification is-info is-light">
            <strong>¡PRODUCTO REGISTRADO!</strong><br>
            EL Producto se registro con exito.
        </div>
        ';
}else{
    if(is_file($img_dir.$foto)){
        chmod($img_dir.$foto,0777);
        unlink($img_dir.$foto);
    }
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrio un error inesperado!</strong><br>
            No se pudo registrar el Producto, por favor intente nuevamente.
        </div>
        ';
}
$guardar_producto = null;