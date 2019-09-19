function customOnFullyLoaded() {
    /***** Create new buttons ******/
    var sendAndSignButton = $('#download');
    $('#download').hide();
    ocultar_indices();
}

function ocultar_indices(){
    $("span[data-name*='in_']" ).hide();
}