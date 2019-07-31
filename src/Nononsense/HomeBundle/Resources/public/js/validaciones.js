function customOnValidate(val, name){
	switch(name){
		case "c5_fecha1":
			return checkDates();
			break;
		case "c5_fecha2":
			return checkDates();
			break;
		default:
			return true;
	}
}

//custom behaviour on submit
//if the method does not return true the submission is cancelled
function customOnSubmit(){
	var result = true;
	if(!checkDates()){
		var title = "Fechas incoherentes";
		var message = "<p>Las fechas no est&aacute;n correctamente formadas (<em>d&iacute;a</em> de <em>mes</em> de <em>a&ntilde;o</em>) o la fecha seleccionada para <strong>c5_fecha1</strong> tiene que ser posterior que la seleccionada para <strong>c5_fecha2</strong>.</p>";
		launchMessage(title, message);
		return false;
	}
	return result;
}



//specific validations
function checkDates(){
	var fecha_posterior = $('span[data-name="c5_fecha1"]:first').text();
	var fecha_anterior = $('span[data-name="c5_fecha2"]:first').text();
	//normalize the date formats
	var afecha_p = normalizeDate(fecha_posterior);
	//first check that the format of c5_fecha1 is correct
	if (afecha_p[0] == 0 || afecha_p[1] == 0){
		return false;
	}
	if (afecha_p[0] == 1 || afecha_p[1] == 1){
		//in this case the value of the second date is irrelevant
		return true;
	}
	//if we have got to this point we have to procede with the second date
	var afecha_a = normalizeDate(fecha_anterior);
	console.log('segunda fecha');
	console.log(fecha_anterior);
	console.log(afecha_a);
	//first check that the format of c5_fecha2 is correct
	if (afecha_a[0] == 0 || afecha_a[1] == 0){
		return false;
	}
	return compareDates(afecha_p, afecha_a);
}

//compare two dates
function compareDates(fecha1, fecha2){
	if (fecha1[2] != fecha2[2]){
		//the year must be the same
		return false;
	}
	if (fecha1[1] > fecha2[1]){
		return true;
	} else if (fecha1[1] < fecha2[1] ){
		return false;
	} else {
		//same month check the day
		if (fecha1[0] > fecha2[0]){
			return true;
		} else {
			return false;
		}
	}
	
}

//normalize dates
function normalizeDate(fecha){
	var valformat = fecha.match(/\s*([0-9]{1,2})\s*de\s*([a-zA-Z]+)\s*de\s*([0-9]{4})/);
	if (valformat === null || valformat.length < 4){
		//incorrect formats
		var res = [0, 0, 0];
		return res;
	} else {
		var res = [parseInt(valformat[1]), getMonth(valformat[2]), parseInt(valformat[3])];
		return res;
	}
}

function getMonth(month){
	var month = month.toLowerCase();
	switch(month){
		case "enero":
			return 1;
			break;
		case "febrero":
			return 2;
			break;
		case "marzo":
			return 3;
			break;
		case "abril":
			return 4;
			break;
		case "mayo":
			return 5;
			break;
		case "junio":
			return 6;
			break;
		case "julio":
			return 7;
			break;
		case "agosto":
			return 8;
			break;
		case "septiembre":
			return 9;
			break;
		case "setiembre":
			return 9;
			break;
		case "octubre":
			return 10;
			break;
		case "noviembre":
			return 11;
			break;
		case "diciembre":
			return 12;
			break;
		default:
			return 0;
	}
}