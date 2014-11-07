<?php

$path_to_root = "../..";
$ACCESS = "f6122c971aeb03476bf01623b09ddfd4";
$keyword = md5('po');
$resultado = "";

include_once($path_to_root . "/modules/requisitions/includes/modules_db.inc");


if ($keyword == $ACCESS){
    if (generate_po())
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
