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

    const button = $('.u_qr_buttonbutton');
    button.val('Obtener datos');
    button.closest('p').attr('style', 'text-align: left !important');
    button.tooltipster('content', 'Una vez escaneado el código pulsa en el botón para obtener los datos');
    $(document).on('click', '.u_qr_buttonbutton', function(){
        const buttonName = button.data('name');
        const scanName = buttonName.replace('button', 'scan');
        const codeName = buttonName.replace('button', 'code');
        const qrVal = $('input[name="'+scanName+'"]').val();
        const domCode = $('input[name="'+codeName+'"]');
        if(qrVal !== ''){
            const aux_json=import_json_from_string(qrVal,codeName);
            json_to_template(aux_json,domCode);
        }
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
