<?php
$path_to_root = "../";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root.'/sales/includes/db/sales_order_db.inc');

//$rol = $_REQUEST['so'];
$rol = $_SESSION['wa_current_user']->access;
$resultado = 'No puede realizar ningún registro de enterado';
$mega = $_REQUEST['amp;amp;mega']; //BUG de FA, inserta caracteres amp; en liga $_REQUEST['mega'];


$array = array ("ventas" => array("3", md5("3"), "ventas"),
    "compras" => array("6",md5("6"), "compras"),
    "construccion" => array("5",md5("5"), "construccion"),
    "administracion" => array("9",md5("9"), "administracion"),
    "direccion" => array("10",md5("10"),"direccion"),
    "almacen" => array("4",md5("4"),"almacen")
    );


foreach($array as &$value){
    if (in_array($rol, $value)){
        $resultado = 'Enterado departamento '.$value[2].' para el MEGA '.$mega.'. Gracias.';
        registraDeEnterado($value[2], $mega);
    }
}

    
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title></title>
    </head>
    <body>
        <p>
        <?php
            echo($resultado);
        ?>
        </p>
        <a href="#" onclick="window.close();">[ CERRAR ]</a>
    </body>
</html>
