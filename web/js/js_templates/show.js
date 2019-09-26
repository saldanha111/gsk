function customOnFullyLoaded() {
	$('html, body').css('overscroll-behavior', 'auto'); 
    /***** Create new buttons ******/
    var sendAndSignButton = $('#download');
    $('#download').hide();
    ocultar_indices();
}

function ocultar_indices(){
    $("span[data-name*='in_']" ).each(function() {
        if($(this).html()=="Índice"){
            $(this).hide();
        }
    });
}