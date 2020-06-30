$( document ).ready(function() {
	$("#btn_cancel").hide();
	$("#btn_save_partial").hide();

	//las variables que se deban ocultar en la vista del comite deben tener un name que empiece por 'dat_tra_   y con la siguiente linea las oculto
	$(":input[name^='dat_tra_']").css("display","none");

	$(".Table4").css("display","none");
});