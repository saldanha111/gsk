//HELPERS
//Some helper functions that may simplify tasks to editors
//1. Limit the number of chars in variables for interactive documents
function limitCharNumber(myvar, limit){
    //first detect if it is a plain text or rich text variable
    var type = dxo[myvar]['type'];
    console.log(type);
    if (type == 'textarea'){
        limitRichTextCharNumber(myvar, limit);
    } else {
        limitTextCharNumber(myvar, limit);
    }
}

function limitTextCharNumber(myvar, limit){
    $('span[data-name="' + myvar + '"]').on('keydown', function(e) {
        var code = e.which;
        if (code != 8 && code !=46) {//only allow to erase chars
            //check the number of chars
            var txt = $(this).text().length;
            if(txt >= limit){
                e.preventDefault();
                return false;
            }
        }
    });
}

function limitRichTextCharNumber(myvar, limit){
    $('#textareaModal').on('keydown', '.note-editable', function(e) {
        var myvariable = $('#textareaVar').text();
        var code = e.which;
        if (code != 8 && code !=46) {//only allow to erase chars
            if (myvariable == myvar){
                //check the number of chars
                var txt = $(this).text().length;
                if(txt >= limit){
                    e.preventDefault();
                    return false;
                }
            }
        }
    });
}

//2. clone a node tagged with a groupId n times
function cloneGroupIdElement(id, ntimes, type, match){
    match = match || 0;
    if (dxoInterface == 'document') {
        if (type == 'table'){
            var mynode = $('table[data-id="' + id + '"]').eq(match).find('tbody.h5p_clone_row_group:first');
            for(var i = 0; i < ntimes; i++){
                mynode.clone().insertBefore(mynode);
            }
        } else if (type == 'bookmark'){
            var mynode = $('div[data-id="' + id + '"]').eq(match);
            for(var i = 0; i < ntimes; i++){
                mynode.clone().insertBefore(mynode);
            }
        } else if (type == 'list'){
            var mynode = $('li[data-id="' + id + '"]').eq(match);
            for(var i = 0; i < ntimes; i++){
                mynode.clone().insertBefore(mynode);
            }
        }
    } else if (dxoInterface == 'form') {
        if (type == 'table'){
            var mynode = $('fieldset[data-id="' + id + '"]').eq(match).find('div[data-type="tbody"]:first');
            for(var i = 0; i < ntimes; i++){
                mynode.clone().insertBefore(mynode);
            }
        } else if (type == 'bookmark'){
            var mynode = $('fieldset[data-id="' + id + '"]').eq(match).find('div[data-type="block"]:first');;
            for(var i = 0; i < ntimes; i++){
                mynode.clone().insertBefore(mynode);
            }
        } else if (type == 'list'){
            var mynode = $('fieldset[data-id="' + id + '"]').eq(match).find('div[data-type="list"]:first');;
            for(var i = 0; i < ntimes; i++){
                mynode.clone().insertBefore(mynode);
            }
        }
    }
}


