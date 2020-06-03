$( document ).ready(function() {
	$("#btn_save").html('<i class="fa fa-send-o"></i> Enviar y firmar');

	$('#form_fill').on('keyup change paste', 'input, select, textarea', function(){
		$("#btn_close").addClass("disabled");
	});
});