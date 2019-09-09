function customOnFullyLoaded() {
    /***** Create new buttons ******/
    var sendAndSignButton = $('#download');
    $('#download').hide();

    var divButtons = sendAndSignButton.parents('div:first');

    /*var htmlButton = '<button type="submit" id="gskclose" class="btn btn-danger btn-sm btn-small" onclick="return false;" style="margin-left: 8px"><i class="fa fa-times" aria-hidden="true"> </i> <span class="buttonTop">Cerrar</span></button>';
    divButtons.prepend(htmlButton);*/
    /*htmlButton = '<button type="submit" id="downloadsend" class="btn btn-success btn-sm btn-small" onclick="return false;" style="margin-left: 8px"><i class="fa fa-save" aria-hidden="true"> </i> <span class="buttonTop">Enviar y firmar</span></button>';
    divButtons.append(htmlButton);*/
    /*htmlButton = '<button type="submit" id="gskcancel" class="btn btn-danger btn-sm btn-small" onclick="return false;" style="margin-left: 8px"><i class="fa fa-times" aria-hidden="true"> </i> <span class="buttonTop">Cancelar</span></button>';
    divButtons.append(htmlButton);*/


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


function customOnLoad() {
    $('body').on('click', 'button[id="gskclose"]', function () {
        console.log("Boton cerrar de GSK");
        var historyObj = btoa(document.referrer);
        historyObj=historyObj.replace("\/", "--");
        console.log(historyObj);

        var responseURL = $('#responseURL').val();
        responseURL += 'cerrar%2F';
        responseURL += historyObj;
        console.log(responseURL);

        responseURL = decodeURIComponent(responseURL);
        //window.location = responseURL;
        window.location.replace(responseURL);


    });
    $('body').on('click', 'button[id="gskcancel"]', function () {
        console.log("Boton cancelar de GSK");

        var responseURL = $('#responseURL').val();
        responseURL += 'cancelar';
        $('#responseURL').val(responseURL);
        $('#download').trigger('click');


    });

    $('body').on('click', 'button[id="downloadsend"]', function () {
        console.log("Boton verificar de GSK");

        // Poner el action cancelar
        var responseURL = $('#responseURL').val();
        responseURL += 'verificar';
        $('#responseURL').val(responseURL);
        $('#download').trigger('click');

    });

    // gskreturn
    $('body').on('click', 'button[id="gskreturn"]', function () {
        console.log("Boton devolver para edición de GSK");

        // Poner el action cancelar
        var responseURL = $('#responseURL').val();
        responseURL += 'devolver';
        $('#responseURL').val(responseURL);
        $('#download').trigger('click');

    });

}



