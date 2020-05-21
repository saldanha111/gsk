var gsk_comment=0;
$( document ).ready(function() {
	$("#btn_save").hide();
	
	/* Ocultamos los input pertenecientes a los ids de las firmas de la imputaciones */
	$("input[class*='var_in_']").each(function( index ) {
		//$(this).after("<span class='view_index_cumpl'>"+$(this).val()+"</span>");
		$(this).hide();
	});
});