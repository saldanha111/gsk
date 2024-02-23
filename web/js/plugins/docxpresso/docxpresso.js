$(document).ready(function(){
    /*if(window.matchMedia){
        function handleMobile(mql) {
            if (mql.matches) {
                $('td > p  span > img').css('width', '80%');
                $('td > p  span > img').css('height', '80%');
            } else {
              //do nothing
            }
        }
        var mql = window.matchMedia("(max-width: 480px)");
        mql.addListener(handleMobile);
        handleMobile(mql);
    } else {
        console.log('matchMedia API not supported');
    }*/
    
    //hide empty commentBody divs
    $('div.commentBody').each(function(){
        if ($(this).text().trim() == '') {
            $(this).css('display', 'none');
        }
    });
    $('input[data-date-format]').change(function(){
        var content = $('<div />').html($(this).val()).text();
        $(this).val(content);
    });
});



