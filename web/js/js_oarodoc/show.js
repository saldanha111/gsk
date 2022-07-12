var gsk_comment=0;
$( document ).ready(function() {
	$("#btn_save").hide();
	$("#btn_save_partial").hide();

	$("span.view_date").each(function( index ) {
		custom_date=$(this).html().toUpperCase();
		custom_date=custom_date.replace(/\.\//,"/");
		$(this).html(custom_date);
	});

	$('#form_fill').html($('#form_fill').html().replace("GSKNOCUMPLE", "NO CUMPLE"));
	$('#form_fill').html($('#form_fill').html().replace("GSKCUMPLE", "CUMPLE"));
	$('#form_fill').html($('#form_fill').html().replace("GSKNOAPLICA", "NO APLICA"));
	
	/* Ocultamos los input pertenecientes a los ids de las firmas de la imputaciones */
	$("input[class*='var_in_']").each(function( index ) {
		if($("#export_to_pdf").length==0){
			$(this).after("<span class='view_index_cumpl'>"+$(this).val()+"</span>");
		}
		$(this).remove();
	});

	$(".btn_option_table").hide();
});