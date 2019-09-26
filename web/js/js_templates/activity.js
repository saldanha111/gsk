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


function customOnFullyLoaded() {
    $('html, body').css('overscroll-behavior', 'auto'); 
    /***** Create new buttons ******/
    var sendAndSignButton = $('#download');
    $('#download').hide();
    $('#cancel').hide();
    ocultar_indices();
    ocultar_validaciones();
    var divButtons = sendAndSignButton.parents('div:first');

    var htmlButton = '<button type="submit" id="download2" class="btn btn-warning btn-sm btn-small" onclick="return false;" style="margin-left: 8px;"><i class="fa fa-save" aria-hidden="true"> </i> <span class="buttonTop">Guardar y firmar</span></button>';
    divButtons.append(htmlButton);
    htmlButton = '<button type="submit" id="gskclose" class="btn btn-danger btn-sm btn-small" onclick="return false;" style="margin-left: 8px"><i class="fa fa-times" aria-hidden="true"> </i> <span class="buttonTop">Cerrar</span></button>';
    divButtons.prepend(htmlButton);
    htmlButton = '<button type="submit" id="downloadsend" class="btn btn-success btn-sm btn-small" onclick="return false;" style="margin-left: 8px"><i class="fa fa-save" aria-hidden="true"> </i> <span class="buttonTop">Enviar y firmar</span></button>';
    divButtons.append(htmlButton);
    htmlButton = '<button type="submit" id="gskcancel" class="btn btn-danger btn-sm btn-small" onclick="return false;" style="margin-left: 8px"><i class="fa fa-times" aria-hidden="true"> </i> <span class="buttonTop">Cancelar</span></button>';
    divButtons.append(htmlButton);

    //This script takes into account the activity of the end user in a document preview
    //first define some global variables
    window.globalClick = false;
    window.globalChange = false;
    window.globalKeypress = false;
    window.onActivityChange = true;
    window.lastUpdated = Date.now();

    //to start parse the json encoded custom data
    var customValueString = decodeURI($('#custom').val());
    console.log(customValueString);
    var customData = JSON.parse(customValueString);

    // Demo values
    //var customData = JSON.parse("{\"activate\": \"deactivate\",\"sessionTime\" : \"1200\", \"sessionLocation\" : \"https://www.docxpresso.com/\"}");


    var activate = customData.activate;
    var sessionTime = customData.sessionTime; //in seconds
    var sessionLocation = decodeURIComponent(customData.sessionLocation); //URL rawurlencoded where to redirect the user after session time has expired

    if (activate == 'activate') {
        LaunchActivityChanges('activate');
        window.onActivityChange = false;
    } else {
        LaunchActivityChanges('deactivate');
    }

    /**** ACTIVITY *****/

    //we have to set a timeout to start recording real user activity
    //because selects are "clicked" on change by default to sync with the corresponding data span
    setTimeout(registerActivity, 500);

    function registerActivity() {
        //register clicks
        var mainDXO = $('#mainDXO');
        console.log('registering activity');
        mainDXO.on('click', '*[contenteditable], span[data-name], button, input, select, img[data-image]', function () {
            window.globalClick = true;
            window.lastUpdated = Date.now();
            console.log('click');
            onChangeActivity('click');
            checkCommentCompulsory($(this));


        });
        mainDXO.on('keypress', '*[contenteditable], note-editable, input, select', function () {
            window.globalKeypress = true;
            window.lastUpdated = Date.now();
            console.log('keypress');
            onChangeActivity('keypress');
            checkCommentCompulsory($(this));

        });
        mainDXO.on('change', 'input, select', function () {
            //this does not detect all possible changes but it is OK by the time being, isn´t it?
            window.globalChange = true;
            window.lastUpdated = Date.now();
            console.log('change');
            onChangeActivity('change');
            checkCommentCompulsory($(this));

        });
    }

    function onChangeActivity(ev) {
        //By the time being we do not distinguish events
        /*
        if(ev == 'change' && !window.onActivityChange){
            window.onActivityChange = true;
        }
        */

        if (window.onActivityChange) {
            //do whatever required
            LaunchActivityChanges('activate');
            //set onActivityChange to false so this only runs once
            window.onActivityChange = false;
        } else {
            //do nothing
        }
    }

    function LaunchActivityChanges(type) {
        if (type == 'activate') {
            /*
             * Deactivate close button
             * btn btn-secondary
             */
            var closeButton = $('#gskclose');
            closeButton.removeClass('btn-danger');
            closeButton.addClass('btn btn-secondary');
            closeButton.attr("disabled", true);


        } else if (type == 'deactivate') {

        }
    }

    /**** SESSION *****/

    setInterval(checkSession, 5000);

    function checkSession() {
        var current = Date.now();
        var delay = sessionTime * 1000;
        if ((current - window.lastUpdated) > delay) {
            //set the global warning variable to false
            warning = false;
            window.location.href = sessionLocation;
        }
    };

    /****** ArrayPreLoad ******/
    var arrayPreLoad = [];
    console.log(refData)

    for(var varName in refData){
        var value = decodeURI(refData[varName]);
        // Sólo hacer caso a aquellos que empiecen por u_ y verchk_

        if(varName.indexOf("u_") == 0  ){
            //console.log("var name: "+varName+ " valor por defecto: "+value);
            if(value == varName ){
                // radio button or checkbox
                value = "";
            }
            if(checkVarValue(varName,value)){
                arrayPreLoad[varName] = false;
            }else{
                arrayPreLoad[varName] = true;
            }

        }

    }
    window.commentCompulsory = false;
    window.arrayPreLoad = arrayPreLoad;
    //console.log(arrayPreLoad);

    $('select[data-list="u_cumple"]').prop('disabled', true);

    $('span[data-name^="u_valor_pesada"]').keypress(function(){
        window.pesada = $(this).attr('data-name');
    });
    $('span[data-name="u_valor_pesada"]').blur(function(){
        /*var format = [',', '.'];
        var valor = string2number($(this).text(), format);
        var newVal = (Math.round(valor * rounder))/rounder;
        var number = newVal.toLocaleString('es-ES', { minimumFractionDigits: 5, maximumFractionDigits: 5 });
        $(this).text(number);*/
        if (typeof window.pesada != 'undefined' && window.pesada == $(this).attr('data-name')){
            var correctValue = validateRange($(this).text());
            var wRex = patronrx.test($(this).text());
            if(wRex && correctValue){
                $('select[data-list="u_cumple"]').val('Sí');
            } else {
                $('select[data-list="u_cumple"]').val('No');
                if(correctValue!=-1){
                    //get the last char of window.pesada
                    var lastChar = window.pesada.replace(/\D/g,'');;
                    var errorMessage = 'La pesada número: ' + lastChar + '('+$(this).text()+') debe estar comprendida entre ' + limit_inf + ' y ' + limit_sup;

                    
                    toastr.error(errorMessage, 'Error formato peso');
                }
            }
        }
    });

}
/*
 * Comprobar si valToCheck está en la variable, el dilema son los checkboxes (estos habría que tratarlos quizás como vacíos.
 */
function checkVarValue(name, valToCheck){
    var values = [];
    var resultado = true;

    //var variable = $('span[data-name="' + name + '"]');
    if($('input[data-list="' + name + '"]') == undefined){

        $('span[data-name="' + name + '"]').each(function () {
            var value = $(this).text();
            //console.log("voy a comprobar: "+valToCheck+" con: "+$(this).text());
            if(value != valToCheck){
                //console.log("Variable "+)
                resultado = false;
            }
        });
    }else{

        console.log("Voy a revisar el checkbox o radio: "+name);
        console.log("Valor: "+ $('input[data-list="' + name + '"]').is(':checked'));
        if($('input[data-list="' + name + '"]').is(':checked')){
            resultado = false;
        }else{

        }
    }


    /*
    $('input[data-list="' + name + '"]').each(function () {
        if($(this).is(':checked')){
            // true, do nothing
        }else{

        }
        checkradio = true;
        var value = $(this).text();
        console.log("voy a comprobar: "+valToCheck+" con: "+$(this).val());
        if(value != valToCheck){
            //console.log("Variable "+)
            radioResultado = true;
        }
    });
    $('input[data-list="' + name + '"]').each(function () {
        checkradio = true;
        var value = $(this).text();
        console.log("voy a comprobar: "+valToCheck+" con: "+$(this).val());
        if(value != valToCheck){
            //console.log("Variable "+)
            radioResultado = true;
        }
    });

    if(checkradio){
        resultado = radioResultado;
    }
    */

    return resultado;
}

function checkCommentCompulsory(element){
    var varName = "";
    if(element.attr('data-name') == undefined){
        varName = element.attr('data-reference');
    }else{
        varName = element.attr('data-name');
    }

    if(window.arrayPreLoad[varName]){
//        console.log("valor de preload: "+window.arrayPreLoad[varName]);
        window.commentCompulsory = true;
        //console.log("Se ha interactuado con un elemento pre-cargado!!!! El comentario es obligatorio");

        var title = "Se ha interactuado con una variable rellenada anteriormente. ";
        var message = "<p>Ha interactuado con la variable <strong>"+varName+"</strong>. Se le solicitará un comentario obligatorio al firmar</p>";
        message += '<p style="text-align: center"></p>';

        launchMessage(title, message);
    }else{
        //console.log("valor de preload: "+window.arrayPreLoad[varName]);
    }
}

function checkCompulsorydifferentCheckBoxes(check1,check2){
    var check1 = $('input[data-name="' + check1 + '"]');
    var check2 = $('input[data-name="' + check2 + '"]');

    var valido = false;

    check1.each(function () {
        if($(this).is(':checked')){
            valido = true;
        }
    });

    if(!valido){
        check2.each(function () {
            if($(this).is(':checked')){
                valido = true;
            }
        });
    }

    return valido;
}

function customOnValidate(val, name) {
    switch (name) {
        case "u_limpieza":
        case "u_limpieza_semanal":
            if(checkCompulsorydifferentCheckBoxes("u_limpieza","u_limpieza_semanal")){
                showOnValidationPanel('u_limpieza', false);
                showOnValidationPanel('u_limpieza_semanal', false);
                return true;
            }else{
                showOnValidationPanel('u_limpieza', true);
                showOnValidationPanel('u_limpieza_semanal', true);
                return false;
            }
            break;
        case "u_valor_pesada":
            if (name.slice(0, -1) == 'u_valor_pesada'){
                //esta validación requiere que todas las pesadas
                //se hallen comprendidas entre los valores inf y sup admitidos
                return validateRange(val);
            } else {
                return true;
            }
            break;
        default:
            return true;
    }

}

function customOnSubmit() {
    var result = true;


    return result;
}

function customOnLoad() {
    $('body').on('change', 'input[data-name="u_limpieza"]', function () {
        refreshValidation('u_limpieza_semanal');
    });
    $('body').on('change', 'input[data-name="u_limpieza_semanal"]', function () {
        refreshValidation('u_limpieza');
    });
    $('body').on('click', 'button[id="gskclose"]', function () {
        console.log("Boton cerrar de GSK");
        // Es necesario desbloquear el uso, habría que ir a algún sitio. ¿? Usar un redirect en vez de un back.
        //window.history.back();
        var historyObj = btoa(document.referrer);
        historyObj=historyObj.replace("\/", "--");
        console.log(historyObj);

        var responseURL = $('#responseURL').val();
        responseURL = limpiarResponseUrl(responseURL);
        responseURL += 'cerrar%2F0%2F';
        responseURL += historyObj;
        console.log(responseURL);

        responseURL = decodeURIComponent(responseURL);
        //window.location = responseURL;
        window.location.replace(responseURL);

    });
    $('body').on('click', 'button[id="gskcancel"]', function () {
        console.log("Boton cancelar de GSK");
        var auxValuePercentage = $('#percentComp').val();
        if(!window.onActivityChange){
            // OK
            console.log(auxValuePercentage);
            var responseURL = $('#responseURL').val();
            responseURL = limpiarResponseUrl(responseURL);
            responseURL += 'cancelar';
            $('#responseURL').val(responseURL);
            $('#download').trigger('click');
        }else{
            var title = "No puede realizar esta acción porque no ha rellenado nada del documento";
            var message = "<p>No ha rellenado ningún campo del documento, no puede realizar una cancelación del mismo</p>";
            launchMessage(title, message);
            return false;
        }


    });
    $('body').on('click', 'button[id="download2"]', function () {
        console.log("Boton guardar y firmar de GSK");
        var auxValuePercentage = $('#percentComp').val();
        console.log(auxValuePercentage);

        if(auxValuePercentage == 100){
            // No se puede hacer un guardado parcial
            var title = "No puede realizar esta acción porque el documento ya está al 100%";
            var message = "<p>El documento está rellenado al <strong>100%</strong> y no se puede realizar un guardado parcial. Debe realizar un envío a verificación</p>";
            launchMessage(title, message);
            return false;
        }else{
            // Realizar envío parcial
            // Comprobar si ha habido actividad:
            console.log(window.onActivityChange);
            if(window.onActivityChange){
                var title = "No puede realizar esta acción porque no ha modificado nada del documento";
                var message = "<p>El documento no ha sido modificado entonces no se puede realizar un guardado parcial.</p>";
                launchMessage(title, message);
            }else{
                var responseURL = $('#responseURL').val();
                responseURL = limpiarResponseUrl(responseURL);
                responseURL += 'parcial';
                if(window.commentCompulsory){
                    responseURL += '%2F1';
                }else{
                    responseURL += '%2F0';
                }
                $('#responseURL').val(responseURL);
                $('#download').trigger('click');
            }
        }

    });

    $('body').on('click', 'button[id="downloadsend"]', function () {
        console.log("Boton enviar y firmar de GSK");
        var auxValuePercentage = $('#percentComp').val();
        console.log(auxValuePercentage);

        if(auxValuePercentage == 100){
            // Hacer un click sobre download
            // Poner el action cancelar
            var responseURL = $('#responseURL').val();
            responseURL = limpiarResponseUrl(responseURL);
            responseURL += 'enviar';
            if(window.commentCompulsory){
                responseURL += '%2F1';
            }else{
                responseURL += '%2F0';
            }
            $('#responseURL').val(responseURL);
            console.log(responseURL);
            $('#download').trigger('click');


        }else{
            // mostrar aviso
            var title = "No puede realizar esta acción porque el documento no está aún al 100%";
            var message = "<p>El documento está rellenado al <strong>"+auxValuePercentage+"%</strong> y no se puede realizar un envío a verificación</p>";
            launchMessage(title, message);
            // si pudiera mostrar aquí panel de validación, hacer el click
        }


    });

    function limpiarResponseUrl(url){
        var urlLimpia = url;
        urlLimpia = urlLimpia.replace(/devolver%2F1/g, "");
        urlLimpia = urlLimpia.replace(/devolver%2F0/g, "");
        urlLimpia = urlLimpia.replace(/devolver/g, "");
        urlLimpia = urlLimpia.replace(/cancelar%2F1/g, "");
        urlLimpia = urlLimpia.replace(/cancelar%2F0/g, "");
        urlLimpia = urlLimpia.replace(/cancelar/g, "");
        urlLimpia = urlLimpia.replace(/verificarparcial%2F1/g, "");
        urlLimpia = urlLimpia.replace(/verificarparcial%2F0/g, "");
        urlLimpia = urlLimpia.replace(/verificarparcial/g, "");
        urlLimpia = urlLimpia.replace(/parcial%2F1/g, "");
        urlLimpia = urlLimpia.replace(/parcial%2F0/g, "");
        urlLimpia = urlLimpia.replace(/parcial/g, "");
        urlLimpia = urlLimpia.replace(/enviar%2F1/g, "");
        urlLimpia = urlLimpia.replace(/enviar%2F0/g, "");
        urlLimpia = urlLimpia.replace(/enviar/g, "");

        return urlLimpia;
    }


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

function validateRange(val){
    //update data
    getValuesFromTemplate();
    console.log('limit_inf: ' + limit_inf);
    console.log('limit_sup: ' + limit_sup);
    if(val.includes(".")){
        toastr.error("La acotación decimal debe ser con ','", 'Error');
        return -1;
    }
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
            console.log("error en patron");
            toastr.error("El número debe tener 5 decimales", 'Error');
            return -1;
        }

    } else {
        if(valor <= limit_sup){
            console.log("limite superior correcto");
        }else{
            console.log("error: "+valor+" es mayor que  "+limit_sup);
        }
        if(valor >= limit_inf){
            console.log("limite inferior correcto");
        }else{
            console.log('error: '+valor+" es menor que  "+limit_inf);
        }
        if(!isNaN(valor)){
            console.log("cosa rara funcion isNAN correcta");
        }else{
            console.log("cosa rara funcion isNAN error");

        }
        return false;
    }
}

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

function ocultar_indices(){
    $("span[data-name*='in_']" ).each(function() {
        if($(this).html()=="Índice"){
            $(this).hide();
        }
    });
}

function ocultar_validaciones(){
    $("span[data-name*='verchk_']" ).hide();
}