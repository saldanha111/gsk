function customOnFullyLoaded() {
    /***** Create new buttons ******/
    var sendAndSignButton = $('#download');
    $('#download').hide();
    $('#cancel').hide();

    var divButtons = sendAndSignButton.parents('div:first');

    var htmlButton = '<button type="submit" id="gskclose" class="btn btn-danger btn-sm btn-small" onclick="return false;" style="margin-left: 8px"><i class="fa fa-times" aria-hidden="true"> </i> <span class="buttonTop">Cerrar</span></button>';
    divButtons.prepend(htmlButton);
    htmlButton = '<button type="submit" id="downloadsend" class="btn btn-success btn-sm btn-small" onclick="return false;" style="margin-left: 8px"><i class="fa fa-save" aria-hidden="true"> </i> <span class="buttonTop">Verificación total</span></button>';
    divButtons.append(htmlButton);
    htmlButton = '<button type="submit" id="downloadsave" class="btn btn-warning btn-sm btn-small" onclick="return false;" style="margin-left: 8px"><i class="fa fa-save" aria-hidden="true"> </i> <span class="buttonTop">Verificación parcial</span></button>';
    divButtons.append(htmlButton);
    htmlButton = '<button type="submit" id="gskcancel" class="btn btn-danger btn-sm btn-small" onclick="return false;" style="margin-left: 8px"><i class="fa fa-times" aria-hidden="true"> </i> <span class="buttonTop">Cancelar</span></button>';
    divButtons.append(htmlButton);
    htmlButton = '<button type="submit" id="gskreturn" class="btn btn-warning btn-sm btn-small" onclick="return false;" style="margin-left: 8px"><i class="fa fa-times" aria-hidden="true"> </i> <span class="buttonTop">Devolver para edición</span></button>';
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

    var sessionTime = customData.sessionTime; //in seconds
    var sessionLocation = decodeURIComponent(customData.sessionLocation); //URL rawurlencoded where to redirect the user after session time has expired


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
            checkCommentCompulsory($(this));


        });
        mainDXO.on('keypress', '*[contenteditable], note-editable, input, select', function () {
            window.globalKeypress = true;
            window.lastUpdated = Date.now();
            console.log('keypress');
            checkCommentCompulsory($(this));

        });
        mainDXO.on('change', 'input, select', function () {
            //this does not detect all possible changes but it is OK by the time being, isn´t it?
            window.globalChange = true;
            window.lastUpdated = Date.now();
            console.log('change');
            checkCommentCompulsory($(this));

        });
    }

    /****** ArrayPreLoad ******/
    var arrayPreLoad = [];
    console.log(refData)

    for (var varName in refData) {
        var value = decodeURI(refData[varName]);
        // Sólo hacer caso a aquellos que empiecen por u_ y verchk_

        if (varName.indexOf("verchk_") == 0) {
            //console.log("var name: "+varName+ " valor por defecto: "+value);
            if (value == varName) {
                // radio button or checkbox
                value = "";
            }
            if (checkVarValue(varName, value)) {
                arrayPreLoad[varName] = false;
            } else {
                arrayPreLoad[varName] = true;
            }

        }

    }
    window.commentCompulsory = false;
    window.arrayPreLoad = arrayPreLoad;
    console.log(arrayPreLoad);

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

}

/*
 * Comprobar si valToCheck está en la variable, el dilema son los checkboxes (estos habría que tratarlos quizás como vacíos.
 */
function checkVarValue(name, valToCheck) {
    var values = [];
    var resultado = true;


    console.log("Voy a revisar el checkbox o radio: " + name);
    console.log("Valor: " + $('input[data-list="' + name + '"]').is(':checked'));
    if ($('input[data-list="' + name + '"]').is(':checked')) {
        resultado = false;
    } else {

    }


    return resultado;
}

function checkCommentCompulsory(element) {
    var varName = "";
    if (element.attr('data-name') == undefined) {
        varName = element.attr('data-reference');
    } else {
        varName = element.attr('data-name');
    }

    if (window.arrayPreLoad[varName]) {
//        console.log("valor de preload: "+window.arrayPreLoad[varName]);
        window.commentCompulsory = true;
        //console.log("Se ha interactuado con un elemento pre-cargado!!!! El comentario es obligatorio");

        var title = "Se ha interactuado con una variable rellenada anteriormente. ";
        var message = "<p>Ha interactuado con la variable <strong>" + varName + "</strong>. Se le solicitará un comentario obligatorio al firmar</p>";
        message += '<p style="text-align: center"></p>';

        launchMessage(title, message);
    } else {
        //console.log("valor de preload: "+window.arrayPreLoad[varName]);
    }
}

function customOnLoad() {
    $('body').on('click', 'button[id="gskclose"]', function () {
        console.log("Boton cerrar de GSK");
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

        var responseURL = $('#responseURL').val();
        responseURL = limpiarResponseUrl(responseURL);
        responseURL += 'cancelar';
        $('#responseURL').val(responseURL);
        $('#download').trigger('click');


    });

    $('body').on('click', 'button[id="downloadsend"]', function () {
        console.log("Boton verificar de GSK");
        var auxValuePercentage = $('#percentComp').val();

        if (auxValuePercentage != 100) {
            // No se puede realizar una verificación total
            var title = "No puede realizar esta acción porque el documento no está al 100% verificado";
            var message = "<p>El documento está rellenado al <strong>+auxValuePercentage+%</strong> y no se puede realizar una verificación total. </p>";
            launchMessage(title, message);
            return false;
        } else {
            var responseURL = $('#responseURL').val();
            responseURL = limpiarResponseUrl(responseURL);
            responseURL += 'verificar';
            if (window.commentCompulsory) {
                responseURL += '%2F1';
            } else {
                responseURL += '%2F0';
            }
            $('#responseURL').val(responseURL);
            $('#download').trigger('click');
        }


    });
    $('body').on('click', 'button[id="downloadsave"]', function () {
        console.log("Boton verificación parcial de GSK");
        var auxValuePercentage = $('#percentComp').val();

        if (auxValuePercentage == 100) {
            // No se puede hacer una verificacion parcial
            var title = "No puede realizar esta acción porque el documento está al 100% verificado";
            var message = "<p>El documento está rellenado al <strong>100%</strong> y no se puede guardar una verificación parcial. </p>";
            launchMessage(title, message);
            return false;

        } else {
            var responseURL = $('#responseURL').val();
            responseURL = limpiarResponseUrl(responseURL);
            responseURL += 'verificarparcial';
            if (window.commentCompulsory) {
                responseURL += '%2F1';
            } else {
                responseURL += '%2F0';
            }
            $('#responseURL').val(responseURL);
            $('#download').trigger('click');
        }


    });


    // gskreturn
    $('body').on('click', 'button[id="gskreturn"]', function () {
        console.log("Boton devolver para edición de GSK");

        // Poner el action cancelar
        var responseURL = $('#responseURL').val();
        responseURL = limpiarResponseUrl(responseURL);
        responseURL += 'devolver';
        $('#responseURL').val(responseURL);
        $('#download').trigger('click');

    });


    function limpiarResponseUrl(url){
        console.log("url a llimpiar: "+url)
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
        console.log("url que devuelvo: "+urlLimpia);

        return urlLimpia;
    }
}



