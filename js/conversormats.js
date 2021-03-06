/**********************************************************************
 * Calculo de materiales
***********************************************************************/
function focus_materiales(i) {
	save_focus(i);
	i.setAttribute('_last', get_amount(i.name));
}

function blur_materiales(i) {
	
	var ancho = get_amount('Ancho'); 
	var largo = get_amount('Largo'); 
	var naves = get_amount('Naves');
	var diasroladora = get_amount('DiasRoladora');
	var diasgrua = get_amount('DiasGrua');
	var costoflete = get_amount('CostoFlete');
	var porcentajeiu = get_amount('PorcentajeIU');
	
	var calibre = get_amount('calibre');				// se obtiene de la BD en relacion al calibre
	var pesocalibre = get_amount('pesocalibre');			// se obtiene de la BD en relacion al calibre
//	var tipolamina = 'no conocido';	// se obtiene de la BD como lista de opciones
	var costolaminakg = get_amount('costolaminakg');
	
	var porcentajedesc = 15;		// valor de la lista de descuentos
	var costogruahora = 200;		// servicio, se obtiene de la BD
	var costomanoobram2 = 500;		// servicio, se obtiene de la BD
	var costoroladoradia = 400;		// servicio, se obtiene de la BD
	var costoplacasfijacion = 25;	// servicio, se obtiene de la BD
	var costotornillosancla = 38;	// servicio, se obtiene de la BD
	
	var area = ancho*largo;
	var longitudxarco = 1.1035*1.03*ancho;
	var numarcos = largo/0.61;
	var longitudlamina = naves*longitudxarco*numarcos;
	var pesototal = pesocalibre*longitudlamina;
	var costolamina = costolaminakg-(1-costolaminakg*porcentajedesc/100);
	var costodirectolam = pesototal*costolamina;
	
	var costoroladoflete = diasroladora*costoroladoradia+costoflete;
	var costogrua = 8*diasgrua*costogruahora;
	var costomanoobra = area*costomanoobram2;
	var cantidadplacas = 2*numarcos+2;
	var cantidadtornillos = 8*numarcos+16;
	var costopyt = cantidadplacas*costoplacasfijacion+cantidadtornillos*costotornillosancla;
	var costodirectototal = costodirectolam+costoroladoflete+costogrua+costomanoobra+costopyt;
	var c2m2 = costodirectototal/area;
	var cfm2 = c2m2*(1+porcentajeiu/100);

	price_format('Calibre', calibre, 0, 1, 1);
	price_format('Peso', pesocalibre, 0, 1, 1);
	price_format('Area', area, 0, 1, 1);
	price_format('LongitudXArco',longitudxarco, 0, 1, 1);
	price_format('NumArcos',numarcos, 0, 1, 1);
	price_format('LongitudLamina',longitudlamina, 0, 1, 1);
	price_format('PesoTotal',pesototal, 0, 1, 1);
	//price_format('TipoLamina',tipolamina, 0, 1 1);
	price_format('CostoLaminaKg',costolaminakg, 0, 1, 1);
	price_format('CostoDirectoLam',costodirectolam, 0, 1, 1);
	price_format('CostoRoladoFlete',costoroladoflete, 0, 1, 1);
	price_format('CostoGrua',costogrua, 0, 1, 1);
	price_format('CostoManoObra',costomanoobra, 0, 1, 1);
	price_format('CostoRoladoraDia',costoroladoradia, 0, 1, 1);
	price_format('CostaGruaHora',costogruahora, 0, 1, 1);
	price_format('CostoManoObraM2',costomanoobram2, 0, 1, 1);
	price_format('CantidadPlacas',cantidadplacas, 0, 1, 1);
	price_format('CantidadTornillos',cantidadtornillos, 0, 1, 1);
	price_format('CostoPyT',costopyt, 0, 1, 1);
	price_format('CostoPlacasFijacion',costoplacasfijacion, 0, 1, 1);
	price_format('CostoTornillosAncla',costotornillosancla, 0, 1, 1);
	price_format('CostoDirectoTotal',costodirectototal, 0, 1, 1);
	price_format('C2M2',c2m2, 0, 1, 1);
	price_format('CFM2',cfm2, 0, 1, 1);
}

var materiales_calc = {
	'.amount': function(e) {
		e.onfocus = function() {
			focus_materiales(this);
		}
		e.onblur = function() {
			blur_materiales(this);
		}
	}
}

Behaviour.register(materiales_calc);
