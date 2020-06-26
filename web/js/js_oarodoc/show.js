var gsk_comment=0;
$( document ).ready(function() {
	$("#btn_save").hide();
	$("#btn_save_partial").hide();
	
	/* Ocultamos los input pertenecientes a los ids de las firmas de la imputaciones */
	$("input[class*='var_in_']").each(function( index ) {
		if($("#export_to_pdf").length==0){
			$(this).after("<span class='view_index_cumpl'>"+$(this).val()+"</span>");
		}
		$(this).remove();
	});

	$(".btn_option_table").hide();
});