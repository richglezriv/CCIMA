<?php

$path_to_root = "../";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/modules/requisitions/includes/modules_db.inc");
$ACCESS = $_REQUEST['po'];
$keyword = md5('po');
$req = $_REQUEST['amp;amp;req'];
$resultado = "";
error_log($req);
if ($keyword == $ACCESS){
    
    if (generate_po($req))
		$resultado = 'Ordenes de compra generadas';
	else
		$resultado = 'Ocurrió un error en la generación de ordenes de compra para la requisicion, verifique con el adminsitrador';
    
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Autorizacion de requisiciones</title>
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
