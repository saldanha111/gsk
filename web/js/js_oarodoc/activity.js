//var gsk_comment=0;
var custom_value;
var comment_field = new Array();
var gsk_comment_description="";
var manual_field = new Array();
var gsk_manual_description="";
$('#fill_html').html($('#fill_html').html().replace(/GSKNOCUMPLE/g, ""));
$('#fill_html').html($('#fill_html').html().replace(/GSKCUMPLE/g, ""));
$('#fill_html').html($('#fill_html').html().replace(/GSKNOAPLICA/g, ""));
$( document ).ready(function() {
	$("#btn_save").html('<i class="fa fa-send-o"></i> Enviar y firmar');
	$("#btn_save_partial").html('<i class="fa fa-save"></i> Guardar y firmar');

	$("span.view_date").each(function( index ) {
		custom_date=$(this).html().toUpperCase();
		custom_date=custom_date.replace(/\.\//,"/");
		$(this).html(custom_date);
	});
	
	//$('input[name ="gsk_comment"]').remove();
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


	/* Cuando un valor automático debe ser inputado de forma manual */
	manual_fill=0;
	$('#form_fill').find('input[readonly="readonly"][required="required"]:visible, select[readonly="readonly"][required="required"]:visible, textarea[readonly="readonly"][required="required"]:visible').each(function() {
		if(!$(this).val()){
			$(this).attr("readonly", false); 
			$(this).addClass("gsk_field_manual_fill");
			manual_fill=1;
			var find=0;
			custom_value=$(this).attr('name');
			$("#form_fill").find("[name^='gsk_manual_fill']").each(function(){
				if($(this).val()==custom_value){
					find=1;
				}
			});
			if(find==0){
				$("#form_fill").append('<input type="hidden" name="gsk_manual_fill[]" value="'+$(this).attr('name')+'" />');
			}
		}
	});

	

	$('#form_fill').find('[name^="gsk_manual_fill"]').each(function() {
		$('[name="'+$(this).val()+'"]').attr("readonly", false);
	});

	$('#form_fill').on('blur', '.gsk_field_manual_fill', function(){
		if(popupManualFill($(this))){

		}
	});

	/* FIN Cuando un valor automático debe ser inputado de forma manual */

	init_prefill=0;
	$('#form_fill').find('[name="gsk_init_prefill"]').each(function() {
		$("#btn_custom_close").addClass("disabled");
		$(this).remove();
		init_prefill=1;
	});
	
	if(init_prefill==0 && data && (!data.hasOwnProperty("dxo_gsk_firmas") || data.dxo_gsk_firmas=="")){
		$("#btn_cancel").addClass("disabled");
	}

	if(manual_fill){
		is_manual_fill();
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

    $(document.body).on('click', "#save_manual", function () {
		
	    if (!$('#modal_change_manual').val()) {
	        swal({
		        title: "Justificación necesaria",
		        text: "Es obligatorio escribir una justificación",
		        type: "warning"
		      });
	    }
	    else{
		    key=$("#box_manual").data("comment_key");
		    manual_field[key]=$("#box_manual").html()+"<br>"+$("#modal_change_manual").val();
		    $('#modal_gsk_manual').modal('toggle');
		}

		if(!$('textarea[name="gsk_manual_description"]').length){
			$("#form_fill").append('<textarea name="gsk_manual_description" style="display:none"></textarea>');
		}
		gsk_manual_description="<br><b><u>Inputación manual</u></b><br>";
		Object.keys(manual_field).forEach(function (key){
			gsk_manual_description+=manual_field[key]+"<hr>";
		});
		$('textarea[name="gsk_manual_description"]').val(gsk_manual_description);
    });

    $(document.body).on('hidden.bs.modal', "#modal_gsk_manual", function () {
    	$('#modal_gsk_manual').remove();
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
    		"Se ha modificado un valor cumplimentado anteriormente","<div class='row'><div class='col-lg-10 col-lg-offset-1'>Está modificando un dato guardado previamente, justifique esta acción<br><br><div id='box_comments' data-comment_key='"+field_original+"'>"+
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


// El usuario debe justificar porque imputa un campo de forma manual
function popupManualFill(element){
    if(element.hasClass("gsk_field_manual_fill")){
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
    	switch(element.data("type")){
			case "input": current_value=element.val();break;
			case "textarea": current_value=element.val();break;
			case "hidden": current_value=element.val();break;
			case "checkbox": if (element.is(":checked")){current_value=element.val();}else{current_value="";}break;
			case "select": current_value=element.val();break;
			case "radio": current_value=element.val();break;
		}

    	show_modal_manual(
    		"Inputación manual de campo automático","<div class='row'><div class='col-lg-10 col-lg-offset-1'>Está rellenando manualmente un campo que en principio ha sido diseñado para imputarse de forma automática. Justifique su acción<br><br><div id='box_manual' data-comment_key='"+field_original+"'>"+
    		"Campo: <b>"+field+"</b><br>"+ line +
    		"Valor previo: <b>"+prev_value+"</b><br>"+
    		"Nuevo valor: <b>"+current_value+"</b></div></div></div><br>"+
            "<div class='row' id='box_justification'><div class='col-lg-10 col-lg-offset-1'><textarea id='modal_change_manual' class='form-control' rows='10' cols='91' required='required'></textarea></div></div>"
    		);

    	return true;
    }
    return false;
}

function show_modal_manual(title, body) {
	if(!$("#modal_gsk_manual").length){
	    var html = '<div class="modal" tabindex="-1" role="dialog" id="modal_gsk_manual">' +
	    '<div class="modal-dialog">' +
	    '<div class="modal-content">' +
	    '<div class="modal-header">' +
	    '<h4 class="modal-title">' + title + '</h4>' +
	    '</div>' +
	    '<div class="modal-body">' +
	    '<p>' + body + '</p>' +
	    '</div>' +
	    '<div class="modal-footer">' +
	    '<button type="button" class="btn btn-primary" id="save_manual">Guardar</button>' +
	    '</div>' +
	    '</div>' +
	    '</div>' +
	    '</div>';

	    var modal = $(html);
	    modal.modal({backdrop: 'static', keyboard: false});
	}
}

function is_manual_fill(){
	//$("#form_fill").append('<input type="hidden" name="gsk_is_manual_fill" value="1" />');
	swal({
        title: "Error en la carga de datos",
        text: "Uno de los campos diseñado para ser cumplimentado automáticamente requiere de su imputación manual y por tanto de justificación",
        type: "warning"
    });
}