var gsk_comment=0;
$( document ).ready(function() {
	$("#btn_save").html('<i class="fa fa-send-o"></i> Verificación total');
	$("#btn_save_partial").html('<i class="fa fa-save"></i> Verificación parcial');
	

	$('#form_fill').on('keyup change paste', 'input:not(:disabled):not([readonly]), select:not(:disabled):not([readonly]), textarea:not(:disabled):not([readonly])', function(){
		console.log($(this));
		$("#btn_custom_close").addClass("disabled");
		if(checkCommentCompulsory($(this))){
			$("#form_fill").append('<input type="hidden" name="gsk_comment" value="1" />');
		}

		/* Si imputamos un campo, reflejamos que tiene que ser atribuíble a la siguiente firma */
		index=$(this).attr('name').replace(/^(u_)/,"in_");
		index=index.replace(/^(verchk_)/,"in_verchk_");
		if(index!=$(this).attr('name')){
			$('input[name ="'+index+'"]').val("gsk_id_firm");
		}
		$("input[name='gsk_percent']").val($(".progress_document").html());
	});

	$("#form_fill").append('<input type="hidden" name="gsk_percent" value="'+$(".progress_document").html()+'" />');

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

	
	$("#btn_cancel").after('<button type="button" id="btn_return" class="btn btn-warning" style="margin-left:4px"><i class="fa fa-arrow-circle-o-left"></i> Devolver</button>');

	$(document).on("click","#btn_return",function() {
		if(!$(this).hasClass("disabled")){
			send_form("return");
		}
	});

	$(".btn_option_table").hide();
});

// Alertamos al usuario que se ha cambiado un campo previamente cargado por otro usuario y por tanto se va a pedir justificación
function checkCommentCompulsory(element){
    if(element.hasClass("change_prefill") && !gsk_comment){
    	gsk_comment=1;
        swal({
	        title: "Se ha modificado un valor cumplimentado anteriormente. ",
	        text: "Está modificando un dato guardado previamente, se le solicitará la justificación del cambio al guardar y firmar el registro",
	        type: "warning"
	      });
        return true;
    }
    return false;
}