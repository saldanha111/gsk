var gsk_comment=0;
$( document ).ready(function() {
	$("#btn_save").html('<i class="fa fa-send-o"></i> Enviar y firmar');
	$("#btn_save_partial").html('<i class="fa fa-save"></i> Guardar y firmar');
	
	$('input[name ="gsk_comment"]').remove();
	$('#form_fill').on('keyup change paste', 'input:not(:disabled):not([readonly]), select:not(:disabled):not([readonly]), textarea:not(:disabled):not([readonly])', function(){
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

	//$("#form_fill").append('<input type="hidden" name="gsk_percent" value="'+$(".progress_document").html()+'" />');

	/* Ocultamos los input pertenecientes a los ids de las firmas de la imputaciones */
	$("input[class*='var_in_']").each(function( index ) {
		if(!$(this).is('[class^="var_in_verchk_"], [class*=" var_in_verchk_"]') ) {
			$(this).after("<span class='view_index_cumpl'>"+$(this).val()+"</span>");
		}
		$(this).hide();
	});

	$("#btn_close").attr("id","btn_custom_close");

	$(document).on("click","#btn_custom_close",function() {
		if(!$(this).hasClass("disabled")){
			send_form("close");
		}
	});

	manual_fill=0;
	$('#form_fill').find('input[readonly="readonly"][required="required"]:visible, select[readonly="readonly"][required="required"]:visible, textarea[readonly="readonly"][required="required"]:visible').each(function() {
		if(!$(this).val()){
			$(this).attr("readonly", false); 
			manual_fill=1;
			if(!$("#form_fill").find("[name='gsk_manual_fill\[\]'][html='"+$(this).attr('name')+"']").length){
				$("#form_fill").append('<input type="hidden" name="gsk_manual_fill[]" value="'+$(this).attr('name')+'" />');
			}
		}
	});

	

	$('#form_fill').find('[name^="gsk_manual_fill"]').each(function() {
		$('[name="'+$(this).val()+'"]').attr("readonly", false);
	});

	if(manual_fill){
		is_manual_fill();
	}
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

function is_manual_fill(){
	$("#form_fill").append('<input type="hidden" name="gsk_is_manual_fill" value="1" />');
	swal({
        title: "Error en la carga de datos",
        text: "Uno de los campos diseñado para ser cumplimentado automáticamente requiere de su imputación manual y por tanto de justificación",
        type: "warning"
    });
}