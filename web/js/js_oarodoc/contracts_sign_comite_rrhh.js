$( document ).ready(function() {
	$("#btn_save").html('<i class="fa fa-send-o"></i> Firmar');
	$("#btn_save_partial").hide();

	//las variables que se deban ocultar en la vista del comite deben tener un name que empiece por 'dat_tra_   y con la siguiente linea las oculto
	$(":input[name^='dat_tra_']").css("display","none");
	$("p").css('margin-top','5px');
	$("p").css('margin-bottom','5px');
	$("td > p").css('margin','0');
});