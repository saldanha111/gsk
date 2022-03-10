$( document ).ready(function() {
	$("#btn_save").html('<i class="fa fa-send-o"></i> Enviar y firmar');
	$("#btn_cancel").hide();
	$("#btn_save_partial").hide();

	$("span.view_date").each(function( index ) {
		custom_date=$(this).html().toUpperCase();
		custom_date=custom_date.replace(/\.\//,"/");
		$(this).html(custom_date);
	});

	$("#btn_close").after('<button type="button" id="btn_return" class="btn btn-warning" style="margin-left:3px"><i class="fa fa-close"></i> Devolver</button>');
	$(document).on("click","#btn_return",function() {
		if(!$(this).hasClass("disabled")){
			send_form("return");
		}
	});

	$('#form_fill').on('keyup change paste', 'input, select, textarea', function(){
		$("#btn_close").addClass("disabled");
	});

	$(".btn_option_table").hide();
});