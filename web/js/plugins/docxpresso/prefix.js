$(document).ready(function(){	
    if (window.prefix != ''){
        var blockprefix = window.prefix . '_';
    } else {
        var blockprefix = '';
    }
    if (blockprefix != '') {
        $alertMessageEdition= "{{'You have no permission to edit this field.'|trans}}";
        //deactivate selects
        $('select[data-list^="' + blockprefix + '"]').each(function(){
            //$(this).prop('disabled', 'disabled');
            $(this).keydown(function(event){
                //solo permitimos navegar con el tab
                if (event.keyCode != 9) {
                    toastr.error($alertMessageEdition, "{{'Warning'|trans}}");
                    event.preventDefault();
                    return false;
                }
            });
            $(this).mousedown(function(event){
                toastr.error($alertMessageEdition, "{{'Warning'|trans}}");
                event.preventDefault();
                return false;
            });
            $(this).css('background-color', '#f0f0f0');
            //deactivate validations
            dxo[$(this).attr('data-list')]['editable'] = false;
        });
        //deactivate variables
        $('span[data-name^="' + blockprefix + '"]').each(function(){
            $(this).prop('contenteditable', 'false');
            $(this).prev().remove();
            $(this).parent().removeClass('spanWrapper');
            $(this).css('border-bottom', 'none');
            $(this).click(function(){
                toastr.error($alertMessageEdition, "{{'Warning'|trans}}");
                return false;
            });
            //deactivate validations
            dxo[$(this).attr('data-name')]['editable'] = false;
        });
        //deactivate images
        $('img[data-image^="' + blockprefix + '"]').each(function(){
            $(this).unbind('click');
            $(this).prev().remove();
            $(this).click(function(){
                toastr.error($alertMessageEdition, "{{'Warning'|trans}}");
                return false;
            });
            //deactivate validations
            dxo[$(this).attr('data-image')]['editable'] = false;
        });
    }
});


