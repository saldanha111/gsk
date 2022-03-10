$( document ).ready(function() {
	$("#btn_save").html('<i class="fa fa-send-o"></i> Enviar y firmar');

	$("span.view_date").each(function( index ) {
		custom_date=$(this).html().toUpperCase();
		custom_date=custom_date.replace(/\.\//,"/");
		$(this).html(custom_date);
	});
	
	$('#form_fill').on('keyup change paste', 'input, select, textarea', function(){
		$("#btn_close").addClass("disabled");
	});
});