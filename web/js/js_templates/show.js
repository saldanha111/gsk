function customOnFullyLoaded() {
    /***** Create new buttons ******/
    var sendAndSignButton = $('#download');
    $('#download').hide();
    $('#cancel').hide();
    ocultar_indices();
    var divButtons = sendAndSignButton.parents('div:first');

    htmlButton = '<button type="submit" id="gskclose" class="btn btn-danger btn-sm btn-small" onclick="return false;" style="margin-left: 8px"><i class="fa fa-times" aria-hidden="true"> </i> <span class="buttonTop">Cerrar</span></button>';
    divButtons.prepend(htmlButton);
}

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
        responseURL += 'cerrar%2F0%2F';
        responseURL += historyObj;
        console.log(responseURL);

        responseURL = decodeURIComponent(responseURL);
        //window.location = responseURL;
        window.location.replace(responseURL);
    });
}

function ocultar_indices(){
    $("span[data-name*='in_']" ).each(function() {
        if($(this).html()=="Índice"){
            $(this).hide();
        }
    });
}