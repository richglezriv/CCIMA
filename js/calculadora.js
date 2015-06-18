
function cargaCombos() {
	
	var strCampos = "";
	$.each($('input'),function(i,val) {
	    if($(this).attr("tipo") == "calculo") {
	    	if (strCampos.length != 0) {
	    		strCampos += ":--:";
	    	}
	    	strCampos += $(this).attr("name") + "=" + $(this).val();
	    }
	});
	
	var $varArrStr = $('input[name=varArrStr]').val();
	
	$.ajax({
		dataType: "json",
        data: {"strCampos": strCampos,
    		"varArrStr": $varArrStr},
        url:   "/CCIMA/calculadoras/aplicarCalculos.php",
        type:  "post",
        beforeSend: 
        	function() {
            },
        success: 
        	function(respuesta) {
        		$.each(respuesta, function(index) {
        			var variable = respuesta[index].variable;
        			var control = respuesta[index].control;
        			var tipo = respuesta[index].tipo;
        			
        			if (tipo == 'SEL') {
        				$("#"+variable).html(control);
        			} else {
        				$("input[name="+variable+"]").val(control);
        			} 
        		});
        	},
        error:
        	function(xhr,err) {
            	alert("readyState: "+xhr.readyState+"\nstatus: "+xhr.status+"\n \n responseText: "+xhr.responseText);
        	}
    });	
}
