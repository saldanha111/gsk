blockVariableEdition("v_date_verificado");
blockVariableEdition("v_signature_limpieza");

function customOnClone(){
    $("span[data-name='u_date']" ).each(function() {
        if($(this).html()=="Fecha" || $(this).html()=="u_date"){
            var today = new Date();
            var dd = today.getDate();
            var mm = today.getMonth()+1; 
            var yyyy = today.getFullYear();
            if(dd<10) 
            {
                dd='0'+dd;
            } 

            if(mm<10) 
            {
                mm='0'+mm;
            } 

            $(this).html(dd+'/'+mm+'/'+yyyy);
        }
    });

    $("span[data-name='u_hora']" ).each(function() {
        if($(this).html()=="NA" || $(this).html()=="u_hora"){
            var today = new Date();
            var seconds = today.getSeconds();
            var minutes = today.getMinutes();
            var hour = today.getHours();

            if(seconds<10) 
            {
                seconds='0'+seconds;
            } 

            if(minutes<10) 
            {
                minutes='0'+minutes;
            } 
            
            if(hour<10) 
            {
                hour='0'+hour;
            } 

            $(this).html(hour+':'+minutes+':'+seconds);
        }
    });
}