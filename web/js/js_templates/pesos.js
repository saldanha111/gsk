

$(document).ready(function () {
	
	blockVariableEdition('u_desviacion');
	
	setInterval(desviacionCuadratica, 1000);
	//main function
	function desviacionCuadratica(){
		console.log("Calcular la desviacion");
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
			var prequad = Math.sqrt(deviation/9);
			//aqui 6 decimales
			//var quad = (Math.round(prequad * rounder))/rounder;
			//console.log('quad: ' + quad);
			// Cambio 2019-09-20

            var prelimit_warning = $('span[data-name="u_limite_control"]').text().trim();
            limit_warning = string2number(prelimit_warning, numFormatWeight);

            quad = prequad;
			$('span[data-name="u_desviacion"]').text(quad.toLocaleString('es-ES', { minimumFractionDigits: numDecimals + 1, maximumFractionDigits: numDecimals + 1 }));
			$('span[data-name="u_desviacion"]').blur(); 
			if (quad > limit_warning || isNaN(quad)){
				console.log("No cumple: "+quad +" > " + limit_warning);
				$('select[data-list="u_cumple"]').val('No');
			} else {
				console.log("Si cumple porque: "+isNaN(quad)+" y "+ quad  +" < "+ limit_warning);
				$('select[data-list="u_cumple"]').val('Sí');
			}
		}
	}
	
});