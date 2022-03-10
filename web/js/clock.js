var udateTime = function() {
    let currentDate = new Date(),
        hours = currentDate.getHours(),
        minutes = currentDate.getMinutes(), 
        seconds = currentDate.getSeconds(),
        weekDay = currentDate.getDay(), 
        day = currentDate.getDate(), 
        month = currentDate.getMonth(), 
        year = currentDate.getFullYear();


    const weekDays = [
        'Domingo',
        'Lunes',
        'Martes',
        'Miércoles',
        'Jueves',
        'Viernes',
        'Sabado'
    ];

    document.getElementById('weekDay').textContent = weekDays[weekDay];

    if (day < 10) {
        day = "0" + day;
    }

    document.getElementById('day').textContent = day;
    
    const months = [
        'Enero',
        'Febrero',
        'Marzo',
        'Abril',
        'Mayo',
        'Junio',
        'Julio',
        'Agosto',
        'Septiembre',
        'Octubre',
        'Noviembre',
        'Diciembre'
    ];

    document.getElementById('month').textContent = months[month];
    document.getElementById('year').textContent = year;

    document.getElementById('hours').textContent = hours;

    if (minutes < 10) {
        minutes = "0" + minutes
    }

    if (seconds < 10) {
        seconds = "0" + seconds
    }

    document.getElementById('minutes').textContent = minutes;
    document.getElementById('seconds').textContent = seconds;
    var gmt = currentDate.getTimezoneOffset()/-60;
    if(gmt==0){
        gmt="";
    }
    else{
        gmt="+"+gmt;
    }
    document.getElementById('gmt').textContent = "GMT "+gmt;
    //document.getElementById('gmt').textContent = currentDate.toString().match(/([A-Z]+[\+-][0-9]+)/)[1];
};

udateTime();

setInterval(udateTime, 1000);