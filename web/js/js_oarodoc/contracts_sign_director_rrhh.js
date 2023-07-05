$( document ).ready(function() {
	$("span.view_date").each(function( index ) {
		custom_date=$(this).html().toUpperCase();
		custom_date=custom_date.replace(/\.\//,"/");
		$(this).html(custom_date);
	});
	$("#btn_save").html('<i class="fa fa-send-o"></i> Firmar y enviar a comité');
	$("#btn_save_partial").hide();
	$("p").css('margin-top','5px');
	$("p").css('margin-bottom','5px');
	$("td > p").css('margin','0');
	$("input.form-control").css('display','inline-block');
});