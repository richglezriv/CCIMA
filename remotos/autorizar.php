<?php
$path_to_root = "../";
include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/modules/requisitions/includes/modules_db.inc");
$req = $_REQUEST['re'];

$resultado = 'autorizado';

confirmaRequisicion($req);



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