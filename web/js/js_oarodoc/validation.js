//var gsk_comment=0;
var custom_value;
var comment_field = new Array();
var gsk_comment_description="";
var no_cumple_field = new Array();
var gsk_no_cumple_description="";
$('#form_fill').html($('#form_fill').html().replace(/GSKNOCUMPLE/g, "NO CUMPLE"));
$('#form_fill').html($('#form_fill').html().replace(/GSKCUMPLE/g, "CUMPLE"));
$('#form_fill').html($('#form_fill').html().replace(/GSKNOAPLICA/g, "NO APLICA"));
$( document ).ready(function() {
	//$("#btn_save").html('<i class="fa fa-send-o"></i> Verificación total');
	$("#btn_save_partial").html('<i class="fa fa-save"></i> Verificar');
	$("#btn_save_partial").removeClass("disabled");
	$("#btn_save_partial").attr('id','btn_pre_save_partial');
	$('#btn_save').hide();

	$("span.view_date").each(function( index ) {
		custom_date=$(this).html().toUpperCase();
		custom_date=custom_date.replace(/\.\//,"/");
		$(this).html(custom_date);
	});

	$('#form_fill').on('keyup change paste', 'input:not(:disabled):not([readonly]), select:not(:disabled):not([readonly]), textarea:not(:disabled):not([readonly])', function(){
		$("#btn_custom_close").addClass("disabled");
		$("#btn_cancel").removeClass("disabled");

		/* Si imputamos un campo, reflejamos que tiene que ser atribuíble a la siguiente firma */
		index=$(this).attr('name').replace(/^(u_)/,"in_");
		index=index.replace(/^(verchk_)/,"in_verchk_");
		if(index!=$(this).attr('name')){
			$('input[name ="'+index+'"]').val("gsk_id_firm");
		}
		$("input[name='gsk_percent']").val($(".progress_document").html());
	});
	$('#form_fill').on('blur', 'input:not(:disabled):not([readonly]), select:not(:disabled):not([readonly]), textarea:not(:disabled):not([readonly])', function(){
		if(checkCommentCompulsory($(this))){
			//$("#form_fill").append('<input type="hidden" name="gsk_comment" value="1" />');
		}
	});

	//$("#form_fill").append('<input type="hidden" name="gsk_percent" value="'+$(".progress_document").html()+'" />');

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

	$(document).on("change","#finish_verification",function() {
		var finish=false;
		if($(this).is(':checked')){
			if(!$('textarea[name="finish_verification"]').length){
				$("#form_fill").append('<textarea name="finish_verification" style="display:none">1</textarea>');
			}
		}
		else{
			if($('textarea[name="finish_verification"]').length){
				$('textarea[name="finish_verification"]').remove();
			}
		}
	});

	$(document).on("click","#btn_pre_save_partial",function() {
		if(!data.is_final_signature || $("#form_fill").valid()){
			checkbox='<p><input type="checkbox" id="finish_verification"> ¿Desea dar por finalizada su parte de la verificación?</p>';
		}
		else{
			checkbox='<p>No puede finalizar su verificación de forma total ya que es usted el último firmante</p>';
		}
		var html = '<div class="modal" tabindex="-1" role="dialog" id="modal_save_verification">' +
	    '<div class="modal-dialog">' +
	    '<div class="modal-content">' +
	    '<div class="modal-header">' +
	    '<h4 class="modal-title">Finalizar verificación</h4>' +
	    '</div>' +
	    '<div class="modal-body">' +
	    checkbox +
	    '</div>' +
	    '<div class="modal-footer">' +
	    '<button type="button" class="btn btn-primary" id="btn_save_partial">Verificar</button>' +
	    '</div>' +
	    '</div>' +
	    '</div>' +
	    '</div>';

	    var modal = $(html);
	    modal.modal();
	});

	
	/*$("#btn_cancel").after('<button type="button" id="btn_return" class="btn btn-warning" style="margin-left:4px"><i class="fa fa-arrow-circle-o-left"></i> Devolver</button>');

	$(document).on("click","#btn_return",function() {
		if(!$(this).hasClass("disabled")){
			send_form("return");
		}
	});*/

	$(".btn_option_table").hide();

	input_manual="";
	$('#form_fill').find('[name^="gsk_manual_fill"]').each(function() {
		input_manual+=$(this).val()+",";
	});

	if(input_manual!=""){
		swal({
	        title: "Valores automáticos inputados de forma manual",
	        text: "Los siguientes valores fueron inputados de manera manual y requiere de observación por su parte: "+input_manual,
	        type: "warning"
	     });
	}

	$(document.body).on('change', "#choose", function () {
        var value;
        switch($(this).val()){
            case "1": value="EE";$("#box_justification").hide();
                break;
            case "2": value="ET";$("#box_justification").hide();
                break;
            case "3": value="";$("#box_justification").show();
                break;
            default: value="";$("#box_justification").hide();
                break;
        }
        $("#modal_change").val(value);
    });

    $(document.body).on('click', "#save_comment", function () {
		
	    if (!$('#modal_change').val()) {
	        swal({
		        title: "Justificación necesaria",
		        text: "Es obligatorio escribir una justificación",
		        type: "warning"
		      });
	    }
	    else{
		    key=$("#box_comments").data("comment_key");
		    comment_field[key]=$("#box_comments").html()+"<br>"+$("#modal_change").val();
		    $('#modal_gsk_comment').modal('toggle');
		}

		if(!$('textarea[name="gsk_comment_description"]').length){
			$("#form_fill").append('<textarea name="gsk_comment_description" style="display:none"></textarea>');
		}
		gsk_comment_description="<br><b><u>Modificación de datos</u></b><br>";
		Object.keys(comment_field).forEach(function (key){
			gsk_comment_description+=comment_field[key]+"<hr>";
		});
		$('textarea[name="gsk_comment_description"]').val(gsk_comment_description);
    });

    $(document.body).on('hidden.bs.modal', "#modal_gsk_comment", function () {
    	$('#modal_gsk_comment').remove();
    });

    /* Cuando un campo de verificación no cumple */
	var is_return=1;
	$(document).on("change",".fill_radio",function() {
		if($(this).val()=="No cumple"){
			popupNoCumple($(this));
		}
	});

	$(document.body).on('click', "#save_no_cumple", function () {
	    if (!$('#modal_textarea_no_cumple').val()) {
	        swal({
		        title: "Justificación necesaria",
		        text: "Es obligatorio escribir una justificación",
		        type: "warning"
		      });
	    }
	    else{
		    key=$("#box_no_cumple").data("comment_key");
		    comment_field[key]=$("#box_no_cumple").html()+"<br>"+$("#modal_textarea_no_cumple").val();
		    $('#modal_gsk_no_cumple').modal('toggle');
		}

		if(!$('textarea[name="gsk_comment_no_cumple"]').length){
			$("#form_fill").append('<textarea name="gsk_comment_no_cumple" style="display:none"></textarea>');
		}
		gsk_no_cumple_description="<br><b><u>Los siguientes campos no cumplen</u></b><br>";
		Object.keys(comment_field).forEach(function (key){
			gsk_no_cumple_description+=comment_field[key]+"<hr>";
		});
		$('textarea[name="gsk_comment_no_cumple"]').val(gsk_no_cumple_description);
    });

    $(document.body).on('hidden.bs.modal', "#modal_gsk_no_cumple", function () {
    	$('#modal_gsk_no_cumple').remove();
    });
});

// Alertamos al usuario que se ha cambiado un campo previamente cargado por otro usuario y por tanto se va a pedir justificación
function checkCommentCompulsory(element){
    if(element.hasClass("change_prefill") /*&& !gsk_comment*/){
    	var line="";
    	field_original=element.attr('name');
    	if(field_original.match(/\[(\d+)\]/)){
    		key = field_original.match(/\[(\d+)\]/)[1];
    	}
    	else{
    		key = null;
    	}
    	field=field_original.replace(/\[(\d+)\]/ig, '');
    	if(key){
    		line="Linea: <b>"+(parseInt(key)+1)+"</b><br>";
    	}
    	else{
    	}
    	prev_value=prefill_value[field_original];
    	
    	switch(element.data("type")){
			case "input": current_value=element.val();break;
			case "textarea": current_value=element.val();break;
			case "hidden": current_value=element.val();break;
			case "checkbox": if (element.is(":checked")){current_value=element.val();}else{current_value="";}break;
			case "select": current_value=element.val();break;
			case "radio": current_value=element.val();break;
		}

    	show_modal(
    		"Se ha modificado un valor cumplimentado anteriormente","<div class='row'><div class='col-lg-10 col-lg-offset-1'>Está modificando un dato guardado previamente. Con esta acción está obligando al resto de verificadores que ya hubieran terminado su verificación, el volver a revisar su cumplimentación. Justifique esta acción<br><br><div id='box_comments' data-comment_key='"+field_original+"'>"+
    		"Campo: <b>"+field+"</b><br>"+ line +
    		"Valor previo: <b>"+prev_value+"</b><br>"+
    		"Nuevo valor: <b>"+current_value+"</b></div></div></div><br>"+
    		"<div class='row'>"+
                "<div class='col-lg-5 col-lg-offset-1'>"+
                    "<select class='form-control' id='choose' name='choose' required='required'>"+
                        "<option value=''></option>"+
                        "<option value='1'>EE</option>"+
                        "<option value='2'>ET</option>"+
                        "<option value='3'>Otro</option>"+
                    "</select>"+
                "</div>"+
            "</div><br>"+
            "<div class='row' id='box_justification' style='display:none'><div class='col-lg-10 col-lg-offset-1'><textarea id='modal_change' class='form-control' rows='10' cols='91' required='required'></textarea></div></div>"
    		);
        return true;
    }
    return false;
}

function show_modal(title, body) {
	if(!$("#modal_gsk_comment").length){
	    var html = '<div class="modal" tabindex="-1" role="dialog" id="modal_gsk_comment">' +
	    '<div class="modal-dialog">' +
	    '<div class="modal-content">' +
	    '<div class="modal-header">' +
	    '<h4 class="modal-title">' + title + '</h4>' +
	    '</div>' +
	    '<div class="modal-body">' +
	    '<p>' + body + '</p>' +
	    '</div>' +
	    '<div class="modal-footer">' +
	    '<button type="button" class="btn btn-primary" id="save_comment">Guardar</button>' +
	    '</div>' +
	    '</div>' +
	    '</div>' +
	    '</div>';

	    var modal = $(html);
	    modal.modal({backdrop: 'static', keyboard: false});
	}
}

function popupNoCumple(element){
    if(element.val()=="No cumple"){
    	var line="";
    	field_original=element.attr('name');
    	if(field_original.match(/\[(\d+)\]/)){
    		key = field_original.match(/\[(\d+)\]/)[1];
    	}
    	else{
    		key = null;
    	}
    	field=field_original.replace(/\[(\d+)\]/ig, '');
    	if(key){
    		line="Linea: <b>"+(parseInt(key)+1)+"</b><br>";
    	}
    	prev_value=prefill_value[field_original];

    	show_modal_no_cumple(
    		"NO CUMPLE","<div class='row'><div class='col-lg-10 col-lg-offset-1'><p class='error'>El campo <b>"+field+"</b> no cumple la verificación. Justifique el por qué</p><br><br><div id='box_no_cumple' data-comment_key='"+field_original+"'>"+
    		"Campo: <b>"+field+"</b><br>"+ line +"</div></div></div><br>"+
            "<div class='row'><div class='col-lg-10 col-lg-offset-1'><textarea id='modal_textarea_no_cumple' class='form-control' rows='10' cols='91' required='required'></textarea></div></div>"
    		);

    	return true;
    }
    return false;
}

function show_modal_no_cumple(title, body) {
	if(!$("#modal_gsk_no_cumple").length){
	    var html = '<div class="modal" tabindex="-2" role="dialog" id="modal_gsk_no_cumple">' +
	    '<div class="modal-dialog">' +
	    '<div class="modal-content">' +
	    '<div class="modal-header">' +
	    '<h4 class="modal-title">' + title + '</h4>' +
	    '</div>' +
	    '<div class="modal-body">' +
	    '<p>' + body + '</p>' +
	    '</div>' +
	    '<div class="modal-footer">' +
	    '<button type="button" class="btn btn-primary" id="save_no_cumple">Guardar</button>' +
	    '</div>' +
	    '</div>' +
	    '</div>' +
	    '</div>';

	    var modal = $(html);
	    modal.modal({backdrop: 'static', keyboard: false});
	}
}