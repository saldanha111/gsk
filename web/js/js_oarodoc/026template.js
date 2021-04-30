$(function(){
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


