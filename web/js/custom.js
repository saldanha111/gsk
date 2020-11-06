//Logout when user is AFK
var timerID, ms = 1200000;
$(window).bind( "mousemove keypress mousedown scroll touchmove touchstart", function() {
  	clearTimeout(timerID);
	timerID = setTimeout(function(){ window.location.replace("/logout"); }, ms);
});