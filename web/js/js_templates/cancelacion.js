function customOnFullyLoaded() { 
    /***** Create new buttons ******/
    var sendAndSignButton = $('#download');
    $('#download').hide();
    $('#cancel').hide();
    ocultar_indices();

    var divButtons = sendAndSignButton.parents('div:first');

    var htmlButton = '<button type="submit" id="download2" class="btn btn-warning btn-sm btn-small" onclick="return false;" style="margin-left: 8px;"><i class="fa fa-save" aria-hidden="true"> </i> <span class="buttonTop">Rechazar cancelación</span></button>';
    divButtons.append(htmlButton);
    htmlButton = '<button type="submit" id="gskclose" class="btn btn-danger btn-sm btn-small" onclick="return false;" style="margin-left: 8px"><i class="fa fa-times" aria-hidden="true"> </i> <span class="buttonTop">Cerrar</span></button>';
    divButtons.prepend(htmlButton);
    htmlButton = '<button type="submit" id="downloadsend" class="btn btn-success btn-sm btn-small" onclick="return false;" style="margin-left: 8px"><i class="fa fa-save" aria-hidden="true"> </i> <span class="buttonTop">Aprobar cancelación</span></button>';
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


    /**** ACTIVITY *****/

    //we have to set a timeout to start recording real user activity
    //because selects are "clicked" on change by default to sync with the corresponding data span
    setTimeout(registerActivity, 500);


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

function customOnClone(){
    $("span[data-name='u_date']" ).each(function() {
        if($(this).html()=="Fecha"){
            var today = new Date();
            var dd = today.getDate();
            var mm = today.getMonth()+1; 
            var yyyy = today.getFullYear();
            if(dd<10) 
            {
                dd='0'+dd;
            } 

            if(mm<10) 
            {
                mm='0'+mm;
            } 

            $(this).html(dd+'/'+mm+'/'+yyyy);
        }
    });

    $("span[data-name='u_hora']" ).each(function() {
        if($(this).html()=="NA"){
            var today = new Date();
            var seconds = today.getSeconds();
            var minutes = today.getMinutes();
            var hour = today.getHours();

            if(seconds<10) 
            {
                seconds='0'+seconds;
            } 

            if(minutes<10) 
            {
                minutes='0'+minutes;
            } 
            
            if(hour<10) 
            {
                hour='0'+hour;
            } 

            $(this).html(hour+':'+minutes+':'+seconds);
        }
    });
}


function customOnValidate(val, name) {
    //console.log(refData);

    // Validación estandar docxpresso
    return true;
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
        //window.location = responseURL;
        window.location.replace(responseURL);

    });

    $('body').on('click', 'button[id="download2"]', function () {
        console.log("Boton guardar y firmar de GSK");
        var auxValuePercentage = $('#percentComp').val();
        console.log(auxValuePercentage);


        // Realizar envío parcial
        // Comprobar si ha habido actividad:
        console.log(window.onActivityChange);

        var responseURL = $('#responseURL').val();
        responseURL = limpiarResponseUrl(responseURL);
        responseURL += 'rechazar';

        $('#responseURL').val(responseURL);
        $('#download').trigger('click');


    });

    $('body').on('click', 'button[id="downloadsend"]', function () {
        console.log("Boton enviar y firmar de GSK");
        var auxValuePercentage = $('#percentComp').val();
        console.log(auxValuePercentage);

        // Hacer un click sobre download
        // Poner el action cancelar
        var responseURL = $('#responseURL').val();
        responseURL = limpiarResponseUrl(responseURL);
        responseURL += 'aprobar';

        $('#responseURL').val(responseURL);
        console.log(responseURL);
        $('#download').trigger('click');


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

function ocultar_indices(){
    $("span[data-name*='in_']" ).each(function() {
        if($(this).html()=="Índice"){
            $(this).hide();
        }
    });
}




