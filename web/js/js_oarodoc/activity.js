var gsk_comment=0;
$( document ).ready(function() {
	$("#btn_save").html('<i class="fa fa-send-o"></i> Enviar y firmar');
	$("#btn_save_partial").html('<i class="fa fa-save"></i> Guardar y firmar');
	

	$('#form_fill').on('keyup change paste', 'input, select, textarea', function(){
		$("#btn_close").addClass("disabled");
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