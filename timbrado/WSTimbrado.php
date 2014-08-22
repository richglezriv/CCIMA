<?php
require_once('/timbrado/nusoap/nusoap.php');

	function set_timbrado($cfdi='')
	{
		//Se asigna el servicio
		$servicio="http://timbrado.expidetufactura.com.mx:8080/pruebas/TimbradoWS?wsdl"; //url del servicio
		//parametros de la llamada
		$parametros=array();
		
		
		//Valores a ocupar
		$usuario="pruebas";
		$contrasena="123456";
		$cfdi = file_get_contents("ccc1.xml");
		
		
		
		//Se preparan los parametros con los valores adecuados
		$parametros['usuario']=$usuario; //String
		$parametros['contrasena']=$contrasena;//String
		$parametros['cfdi']=$cfdi;//String
		
		
		//Se crea el cliente del servicio
		$client = new SoapClient( $servicio, $parametros);
		
		//Se hace el metodo que vamos a probar
		$result = $client->timbrar($parametros);
		echo $parametros;
		
		//Para observar el Dump de lo que regresa, es puramente de debug
		echo "Valor dump del servicio:";
		echo "<BR>";
		var_dump($result);
		echo "<BR>";
		echo "<BR>";
		
		
		
		//Aislar cada valor de lo que regresa, y poder manipularlo como sea
		foreach($result as $key => $value) {
			echo "Codigo:";
			echo "<BR>";
			var_dump($value->codigo);
			echo "<BR>";
			echo "<BR>";
			echo "Mensaje:";
			echo "<BR>";
			var_dump($value->mensaje);
			echo "<BR>";
			echo "<BR>";
			echo "Timbre:";
			echo "<BR>";
			file_put_contents('regresa.xml', $value->timbre);
			var_dump($value->timbre);
			echo "UUID";
			echo "<BR>";
			var_dump($value->uuid);
		}
		
		
		return $result;
	}
?>
