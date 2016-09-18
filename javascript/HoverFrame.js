/**
 * 
 */
/*var req = new XMLHttpRequest();*/

function close(targetID) {
	var target = document.getElementById(targetID);
	target.style.visibility = 'hidden';
}
function open(targetID) {
	var target = document.getElementById(targetID);
	target.style.visibility = 'visible';
	target.focus();
}
function Hover() {
	
} 
Hover.prototype.show = function() {
	this.style.visibility = 'visible';
}

Hover.prototype.hide = function() {
	this.style.visibility = 'hidden';
}