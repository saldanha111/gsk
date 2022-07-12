var gsk_comment=0;
$( document ).ready(function() {
	$("#btn_save").hide();
	$("#btn_save_partial").hide();

	$("span.view_date").each(function( index ) {
		custom_date=$(this).html().toUpperCase();
		custom_date=custom_date.replace(/\.\//,"/");
		$(this).html(custom_date);
	});

	$('#fill_html').html($('#fill_html').outerHTML().replace(/GSKNOCUMPLE/g, "NO CUMPLE"));
	$('#fill_html').html($('#fill_html').outerHTML().replace(/GSKCUMPLE/g, "CUMPLE"));
	$('#fill_html').html($('#fill_html').outerHTML().replace(/GSKNOAPLICA/g, "NO APLICA"));
	
	/* Ocultamos los input pertenecientes a los ids de las firmas de la imputaciones */
	$("input[class*='var_in_']").each(function( index ) {
		if($("#export_to_pdf").length==0){
			$(this).after("<span class='view_index_cumpl'>"+$(this).val()+"</span>");
		}
		$(this).remove();
	});

	$(".btn_option_table").hide();
});