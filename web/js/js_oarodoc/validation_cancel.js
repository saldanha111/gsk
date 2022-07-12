var gsk_comment=0;
$('#form_fill').html($('#form_fill').html().replace(/GSKNOCUMPLE/g, "NO CUMPLE"));
$('#form_fill').html($('#form_fill').html().replace(/GSKCUMPLE/g, "CUMPLE"));
$('#form_fill').html($('#form_fill').html().replace(/GSKNOAPLICA/g, "NO APLICA"));
$( document ).ready(function() {
	$("#btn_save").hide();
	$("#btn_save_partial").html('<i class="fa fa-send-o"></i> Aprobar cancelación');
	$("#btn_cancel").html('<i class="fa fa-close"></i> Rechazar cancelación');

	$("span.view_date").each(function( index ) {
		custom_date=$(this).html().toUpperCase();
		custom_date=custom_date.replace(/\.\//,"/");
		$(this).html(custom_date);
	});
	
	//$("#form_fill").append('<input type="hidden" name="gsk_percent" value="'+$(".progress_document").html()+'" />');

	/* Ocultamos los input pertenecientes a los ids de las firmas de la imputaciones */
	$("input[class*='var_in_']").each(function( index ) {
		$(this).after("<span class='view_index_cumpl'>"+$(this).val()+"</span>");
		$(this).hide();
	});

	$("#btn_close").attr("id","btn_custom_close");

	$(document).on("click","#btn_custom_close",function() {
		if(!$(this).hasClass("disabled")){
			send_form("close");
		}
	});

	$("#btn_save_partial").attr("id","btn_custom_save_partial");
	$("#btn_custom_save_partial").removeClass("disabled");
	$(document).on("click","#btn_custom_save_partial",function() {
		if(!$(this).hasClass("disabled")){
			send_form("save_partial");
		}
	});

	$(".btn_option_table").hide();
});