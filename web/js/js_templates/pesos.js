//Variables globales para este script
var numFormatWeight = [',', '.'];
var numDecimals = 5;
var patron = '^[0-9]+,[0-9]{' + numDecimals + '}$';
var patronrx = new RegExp(patron);
var rounder = Math.pow(10, numDecimals)
var limit_inf;
var limit_sup;
var limit_warning;
var pesa_emplear;

function customOnValidate(val, name){
    if (name.slice(0, -1) == 'u_valor_pesada'){
		//esta validación requiere que todas las pesadas
		//se hallen comprendidas entre los valores inf y sup admitidos
		return validateRange(val);
	} else {
		return true;
	}
};

function getValuesFromTemplate(){
	//Esta funciónn extrae los datos del QR para la validación
	//Las variables relevates son:
	//pesa asociada a u_pesa (pero esta creo que no se necesita utilizar)
	//rango asociada a u_limite_control que tenemos que dividir luego en limite_inf y limite_sup
	//aviso asociada a u_limite_aviso y que debe disparar la alerta asociada a la desviación cuadrática media
	//IMPORTANTE: todos los valores númericos deben estar en gramos
	//pesa
	var prepesa_emplear = $('span[data-name="u_pesa"]').text().trim();
	pesa_emplear = string2number(prepesa_emplear, numFormatWeight);
	//rangos
	var rango = $('span[data-name="u_limite_control"]').text().trim();
	var rangoArray = rango.split('-');
	if (rangoArray.length > 1){ //solo para evitar errores en la carga
		limit_inf = string2number(rangoArray[0].trim(), numFormatWeight);
		limit_sup = string2number(rangoArray[1].trim(), numFormatWeight);
	}
	//aviso
	var prelimit_warning = $('span[data-name="u_limite_aviso"]').text().trim();
	limit_warning = string2number(prelimit_warning, numFormatWeight);
}

function validateRange(val){
	//update data
	getValuesFromTemplate();
	console.log('limit_inf: ' + limit_inf);
	console.log('limit_sup: ' + limit_sup);
	var valor = string2number(val, numFormatWeight);
	console.log('valor: ' + valor);
	if (!isNaN(valor) && valor <= limit_sup && valor >= limit_inf){
		//we have to check that the string has 5 decimals
		var wRex = patronrx.test(val);
		console.log('patron: ' + patron);
		console.log(wRex);
		if (wRex){
			return true;
		} else {
			return false;
		}
		
	} else {
		return false;
	}
}

function customOnFullyLoaded (){
	$('select[data-list="u_cumple"]').prop('disabled', true);
	
	$('span[data-name^="u_valor_pesada"]').keypress(function(){
		window.pesada = $(this).attr('data-name');
	});
	$('span[data-name^="u_valor_pesada"]').blur(function(){
		/*var format = [',', '.'];
		var valor = string2number($(this).text(), format);
		var newVal = (Math.round(valor * rounder))/rounder;
		var number = newVal.toLocaleString('es-ES', { minimumFractionDigits: 5, maximumFractionDigits: 5 });
		$(this).text(number);*/
		if (typeof window.pesada != 'undefined' && window.pesada == $(this).attr('data-name')){
			var correctValue = validateRange($(this).text());
			var wRex = patronrx.test($(this).text());
			if (correctValue && wRex){
				//it seems OK
			} else {
				//get the last char of window.pesada
				var lastChar = window.pesada.replace(/\D/g,'');;
				var errorMessage = 'La pesada número: ' + lastChar + ' debe tener ' + numDecimals + ' decimales y estar comprendida entre ' + limit_inf + ' y ' + limit_sup;
				toastr.error(errorMessage, 'Error formato peso');
			}
		}
    });
}
//validar pesos
function validarArrayPesos (p){
	pesos = convert2Numbers(p);
	var error = false;
	var len = pesos.length;
	for (var j = 0; j < len; j++){
		if (errorPeso(pesos[j])){
			error = true;
			break;
		}
	}
	return error;
}

function errorPeso (peso){
	if (isNaN(peso)){
		return true;
	} else {
		return false;
	}
}

function convert2Numbers (w){
	var format = [',', '.'];
	var len = w.length;
	var res = [];
	for (var j = 0; j < len; j++){
		//usamos string2number que esta definido en Docxpresso
		valnum = string2number(w[j], format);
	    res[j] = (Math.round(valnum * rounder))/rounder;
	}
	return res;
}

function getSum(total, num) {
  return total + num;
}

$(document).ready(function () {
	
	blockVariableEdition('u_desviacion');
	
	setInterval(desviacionCuadratica, 1000);
	//main function
	function desviacionCuadratica(){
		var base = 'u_valor_pesada';
		var pesos = [];
		for (var k = 0; k < 10; k++){
			pesos[k] = $('span[data-name="u_valor_pesada' + (k + 1) + '"]').text();
		}
		//console.log(pesos);
		var error = validarArrayPesos(pesos);
		//console.log(error);
		if (error){
			//tenemos que dar error e imprimirlo
			//console.log('error');
			$('span[data-name="u_desviacion"]').text('-----');
			$('select[data-list="u_cumple"]').val('No');
		} else {
			//lets compute the mean value
			var weights = convert2Numbers(pesos);
			var mean = weights.reduce(getSum)/10;
			//console.log('mean: ' + mean);
			var deviation = 0;
			for (var j = 0; j < 10; j++){
				deviation += Math.pow(weights[j] - mean, 2);
			}
			var prequad = Math.sqrt(deviation/10);
			//aqui 6 decimales
			var quad = (Math.round(prequad * rounder))/rounder;
			//console.log('quad: ' + quad);
			$('span[data-name="u_desviacion"]').text(quad.toLocaleString('es-ES', { minimumFractionDigits: numDecimals + 1, maximumFractionDigits: numDecimals + 1 }));
			if (quad > limit_warning || isNaN(quad)){
				$('select[data-list="u_cumple"]').val('No');
			} else {
				$('select[data-list="u_cumple"]').val('Sí');
			}
		}
	}
	
});