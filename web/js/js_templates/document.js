function customOnFullyLoaded() {
    /***** Create new buttons ******/
    var sendAndSignButton = $('#download');
    $('#download').hide();
    $('#cancel').hide();

    var divButtons = sendAndSignButton.parents('div:first');

    var htmlButton = '<button type="submit" id="download2" class="btn btn-warning btn-sm btn-small" onclick="return false;" style="margin-left: 8px;"><i class="fa fa-save" aria-hidden="true"> </i> <span class="buttonTop">Guardar</span></button>';
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
        });
        mainDXO.on('keypress', '*[contenteditable], note-editable, input, select', function () {
            window.globalKeypress = true;
            window.lastUpdated = Date.now();
            console.log('keypress');
            onChangeActivity('keypress');
        });
        mainDXO.on('change', 'input, select', function () {
            //this does not detect all possible changes but it is OK by the time being, isn´t it?
            window.globalChange = true;
            window.lastUpdated = Date.now();
            console.log('change');
            onChangeActivity('change');
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

}


function customOnSubmit() {
    var result = true;


    return result;
}

function customOnLoad() {
    $('body').on('click', 'button[id="gskclose"]', function () {
        console.log("Boton cerrar de GSK");
        // Es necesario desbloquear el uso, habría que ir a algún sitio. ¿? Usar un redirect en vez de un back.
        //window.history.back();
        var historyObj = btoa(document.referrer);
        historyObj=historyObj.replace("\/", "--");
        console.log(historyObj);

        var responseURL = $('#responseURL').val();
        responseURL = limpiarResponseUrl(responseURL);
        responseURL += 'cerrar%2F';
        responseURL += historyObj;
        console.log(responseURL);

        responseURL = decodeURIComponent(responseURL);
        console.log("test");
        //window.location = responseURL;
        location.href =responseURL;

    });
    $('body').on('click', 'button[id="gskcancel"]', function () {
        console.log("Boton cancelar de GSK");
        var auxValuePercentage = $('#percentComp').val();

            // OK
            console.log(auxValuePercentage);
            var responseURL = $('#responseURL').val();
            responseURL = limpiarResponseUrl(responseURL);
            responseURL += 'cancelar';
            $('#responseURL').val(responseURL);
            $('#download').trigger('click');
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
            $('#responseURL').val(responseURL);
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



