$( document ).ready(function() {
	$("span.view_date").each(function( index ) {
		custom_date=$(this).html().toUpperCase();
		custom_date=custom_date.replace(/\.\//,"/");
		$(this).html(custom_date);
	});
	$("#btn_cancel").hide();
	$("#btn_save_partial").hide();

	// importante que este vaya antes que el display none, si no, no se oculta ninguna
	$("input.form-control").css('display','inline-block');
	//las variables que se deban ocultar en la vista del comite deben tener un name que empiece por 'dat_tra_   y con la siguiente linea las oculto
	$(":input[name^='dat_tra_']").css("display","none");
	$(":input[name^='dat_tra_fecha_nac']").siblings('.view_date').css('display','none');
	$("p").css('margin-top','5px');
	$("p").css('margin-bottom','5px');
	$("td > p").css('margin','0');
});