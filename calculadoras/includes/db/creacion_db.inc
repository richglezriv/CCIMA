<?php
function get_sql_for_calculadora_search($id_calculadora=0)
{
	$sql = "SELECT
		bloque,
		posicion,
		tipo,
		nombre,
		valor,
		estilo_de_font,
		posicion_xy,
		editable
		FROM ".TB_PREF."detalles_calculadora 
				WHERE id_calculadora = " . db_escape($id_calculadora) . " 
		ORDER BY bloque, posicion"; 

	return $sql;
}

function get_component_from_calculadora($id_campo)
{
	$sql = "SELECT
		id_calculadora,
		bloque,
		posicion,
		tipo,
		nombre,
		valor,
		estilo_de_font,
		posicion_xy,
		editable
		FROM ".TB_PREF."detalles_calculadora "; 

	if (($id_campo != null) && $id_campo != "")
	{
		$sql .= "WHERE id_campo = ".db_escape($id_campo);
	}

	$result = db_query($sql, "could not get campo");
	return db_fetch($result);
}

function get_next_id_calculadora()
{
	$sql = "SELECT MAX(id_calculadora) as id FROM ".TB_PREF."cabecera_calculadora "; 
	$result = db_query($sql, "Could not retrieve calculadoras");
	$myrow = db_fetch($result);
	if ($myrow === false)
		return 1;
	else
		return $myrow['id'] + 1;
}

function add_calculadora($id_calculadora, $nombre_calculadora, $controles)
{

	begin_transaction();
	
	$sql = "INSERT INTO ".TB_PREF."cabecera_calculadora (id_calculadora, nombre_calculadora, indicador_borrado) " 
				."VALUES (".db_escape($id_calculadora).",".db_escape($nombre_calculadora).",".db_escape(' ').")";
	$result1 = db_query($sql, "No fue posible registrar la calculadora.");
		
	foreach($controles as $control) {
		$sql = "INSERT INTO ".TB_PREF."detalles_calculadora (id_calculadora, bloque, posicion, tipo,
				nombre, valor, estilo_de_font, posicion_xy, editable) "
				."VALUES (".db_escape($id_calculadora).",".db_escape($control['bloque']).","
					.db_escape($control['posicion']).",".db_escape($control['tipo']).","
					.db_escape($control['nombre']).",".db_escape($control['valor']).","
					.db_escape($control['estilo_de_font']).",".db_escape($control['posicion_xy']).","
					.db_escape($control['editable']).")";
		$result2 = db_query($sql, "No fue posible registrar alguno de los componentes de la calculadora.");
		$result1 = $result1 && $result2;
	}

	commit_transaction();
	
	return $result1;
}

?>
