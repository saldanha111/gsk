var navigation=false;
$( document ).ready(function() {
    $(document).on('click','a:not([target="_blank"]):not([href="#"]):not([data-toggle]),button:not(.dropdown-toggle):not([data-toggle]),input[type="submit"],input[type="button"]:not(.dropdown-toggle):not([data-toggle])',function() {
        click_button=$(this)
        var imgloading = $('<img id="dynamic_loading">');
        imgloading.attr('src', '/img/loadinggsk.svg');
        imgloading.height(click_button.height()+20);
        click_button.after(imgloading);
        click_button.hide();
        intervalId = setInterval(isRunning, 200, click_button, imgloading);
    });
});

$(window).on('beforeunload', function () {
    navigation=true;
});

var isRunning = function(click_button,imgloading){
    console.log($.active+"**"+navigation);
    if ($.active == 0 && !navigation) {
        click_button.show();
        imgloading.hide();
        clearInterval(intervalId);
    }
};

//Logout when user is AFK
var timerID, ms = 1200000;
$(window).bind( "mousemove keypress mousedown scroll touchmove touchstart", function() {
  	clearTimeout(timerID);
	timerID = setTimeout(function(){ window.location.replace("/users/logout"); }, ms);
});

function checkIfArrayIsUnique(arr) {
    var map = {}, i, size;

    for (i = 0, size = arr.length; i < size; i++){
        if (arr[i] in map){
            //console.log(i+ "=>" +arr[i]+" => REPETIDO");
            return false;
        }

        map[arr[i]] = true;
    }

    return true;
}

function isJson(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

function validURL(str) {
    var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
    '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
    '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
    '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
    '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
    '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
    return !!pattern.test(str);
}

function getObjects(obj, key, val) {
    var objects = [];
    for (var i in obj) {
        if (!obj.hasOwnProperty(i)) continue;
        if (typeof obj[i] == 'object') {
            objects = objects.concat(getObjects(obj[i], key, val));
        } else if (i == key && obj[key] != val) {
            objects.push(val);
        }
    }
    return objects;
}

function handleFiles(files) {
    var o = [];
    for (var i = 0; i < files.length; i++) {
        var reader = new FileReader();
        reader.onload = (function (theFile) {
            return function (e) {
                qrcode.decode(e.target.result);
            };
        })(files[i]);
        // Read in the image file as a data URL.
        reader.readAsDataURL(files[i]);
    }
}