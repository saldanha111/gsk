function checkCompulsorydifferentCheckBoxes(check1,check2){
    var check1 = $('input[data-name="' + check1 + '"]');
    var check2 = $('input[data-name="' + check2 + '"]');

    var valido = false;

    check1.each(function () {
        if($(this).is(':checked')){
            valido = true;
        }
    });

    if(!valido){
        check2.each(function () {
            if($(this).is(':checked')){
                valido = true;
            }
        });
    }

    return valido;
}

function customOnValidate(val, name) {
    switch (name) {
        case "u_limpieza":
        case "u_limpieza_semanal":
            if(checkCompulsorydifferentCheckBoxes("u_limpieza","u_limpieza_semanal")){
                showOnValidationPanel('u_limpieza', false);
                showOnValidationPanel('u_limpieza_semanal', false);
                return true;
            }else{
                showOnValidationPanel('u_limpieza', true);
                showOnValidationPanel('u_limpieza_semanal', true);
                return false;
            }
            break;
        default:
            return true;
    }

}

function customOnLoad() {
    $('body').on('change', 'input[data-name="u_limpieza"]', function () {
        refreshValidation('u_limpieza_semanal');
    });
    $('body').on('change', 'input[data-name="u_limpieza_semanal"]', function () {
        refreshValidation('u_limpieza');
    });
}