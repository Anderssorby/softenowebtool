/**
 *
 */
var timeout = 500;
var closetimer = 0;
var ddmenuitem = 0;
var rightmenuitem = 0;
var rcollapsed = true;

//open hidden layer
function mopen(id) {
	// cancel close timer
	mcancelclosetime();

	// close old layer
	if(ddmenuitem)
		ddmenuitem.hide("fast");

	// get new layer and show it
	ddmenuitem = $('#'+id);
	ddmenuitem.show("fast");
	
}

//close showed layer
function mclose() {
	if(ddmenuitem)
		ddmenuitem.hide("fast");
}

//go close timer
function mclosetime() {
	closetimer = window.setTimeout(mclose, timeout);
}

//cancel close timer
function mcancelclosetime() {
	if(closetimer) {
		window.clearTimeout(closetimer);
		closetimer = null;
	}
}

//close layer when click-out
document.onclick = mclose;

function Menu(id) {
	document.getElementById(id);
}

function ropen(id) {
	ddmenuitem = document.getElementById(id);
	ddmenuitem.style.visibility = 'visible';
	ddmenuitem.style.height = 'auto';
	ddmenuitem.parentNode.style.height = 'auto';
}

function rclose(id) {
	ddmenuitem = document.getElementById(id);
	ddmenuitem.style.visibility = 'hidden';
	ddmenuitem.style.height = '0px';
	ddmenuitem.parentNode.style.height = '20px';
}

function rcollapse(id) {
	if(rcollapsed) {
		rightmenuitem = document.getElementById(id);
		rightmenuitem.style.visibility = 'visible';
		rightmenuitem.style.height = 'auto';
		rightmenuitem.parentNode.style.height = 'auto';
		rcollapsed = false;
	} else {
		rightmenuitem = document.getElementById(id);
		rightmenuitem.style.visibility = 'hidden';
		rightmenuitem.style.height = '0px';
		rightmenuitem.parentNode.style.height = '20px';
		rcollapsed = true;
	}
}

function Hover(ide, title) {
	var body = document.getElementsByTagName("body")[0];
	var wrapper = document.createElement("div");
	this.wrapper = wrapper;
	var classes = document.createAttribute("class");
	classes.value = "Hover center";
	wrapper.setAttributeNode(classes);
	var id = document.createAttribute("id");
	id.value = ide;
	wrapper.setAttributeNode(id);
	var head = document.createElement("h1");
	head.appendChild(document.createTextNode(title));
	//close button
	var close = document.createElement("img");
	var src = document.createAttribute("src");
	src.value = "bilder/kryss.png";
	close.setAttributeNode(src);
	var alt = document.createAttribute("alt");
	alt.value = "lukk vindu";
	close.setAttributeNode(alt);
	var onclick = document.createAttribute("onclick");
	onclick.value = this.changeVisibility;
	close.setAttributeNode(onclick);
	head.appendChild(close);
	wrapper.appendChild(head);
	var cont = document.createElement("div");
	this.content = cont;
	wrapper.appendChild(cont);
	body.appendChild(wrapper);
}

Hover.prototype.changeVisibility = function() {
	if(this.visible) {
		this.wrapper.style.visibility = 'hidden';
	} else {
		this.wrapper.style.visibility = 'visible';
	}
	this.visible = !this.visible;
}
function Selector(id) {
	var selector = document.getElementById(id);
	var children = selector.childNodes;
	for(var i = 0; i < children.length; i++) {
		var child = children[i];
	}
}

function addClass(element, classes) {
	var attrs = element.attributes;
	for(var i = 0; i < attrs.length; i++) {
		if(attrs[i].name == "class") {
			attrs[i].value = attrs[i].value + " " + classes;
		}
	}
}

function DataDisplay() {

	var sel = document.getElementById("chooser");
	var det = document.getElementById("details");
	sel.getSelected = function() {
		var c = this.children;
		for(var i = 0, item; item = c[i++]; ) {
			var attrs = item.attributies;
			console.log(item);
			for(var j = 0, att; att = attrs[i++]; ) {
				console.log(att.name);
				if(att.name == "selected") {
					return item;
				}
			}
		}
		return false;
	};
	var wa = this;
	sel.onclick = function() {
		var value = this.value;
		if(value) {
			wa.display(value);
		}
	};
	this.elements = {
		selector : sel,
		details : det
	};
}

DataDisplay.prototype.fireResult = function(req) {
	var par = document.createElement("ul");
	var res = JSON.parse(req.result);
	for(var key in res) {
		var sub = res[key];
		var li = document.createElement("li");
		var tli = document.createTextNode(key);
		var und = document.createElement("ul");
		for(var iti in sub) {
			var lie = document.createElement("li");
			var litx = document.createTextNode(iti + ": " + sub[iti]);
			lie.appendChild(litx);
			und.appendChild(lie);
		}
		li.appendChild(tli);
		par.appendChild(li);
		par.appendChild(und);
	}
	this.elements.details.innerHTML = par.outerHTML;
};

DataDisplay.prototype.elements = {};

DataDisplay.prototype.display = function(id) {
	var wa = this;
	var req = new Request("?action=readmenu&id=" + id, function() {
		wa.fireResult(req);
	});
	req.makeRequest();
	req.go();
};
function Request(url, listner) {
	this.url = url;
	this.listner = listner;
}

Request.prototype.httpRequest = null;

Request.prototype.url = "";

Request.prototype.makeRequest = function() {
	if(window.XMLHttpRequest) {// Mozilla, Safari, ...
		this.httpRequest = new XMLHttpRequest();
	} else if(window.ActiveXObject) {// IE
		try {
			this.httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				this.httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {
			}
		}
	}
	if(!this.httpRequest) {
		logger.log('Cannot create an XMLHTTP instance', logger.type.error, 'httpRequest');
		return false;
	}
	return true;
}

Request.prototype.go = function(post) {
	var wa = this;
	this.httpRequest.onreadystatechange = function() {
		wa.checkStatus()
	};
	if(post) {
		this.httpRequest.open('POST', this.url);
		this.httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		//this.httpRequest.setRequestHeader("Content-length", params.length);
		this.httpRequest.setRequestHeader("Connection", "close");
	} else {
		this.httpRequest.open('GET', this.url);
	}
	this.httpRequest.send(post);
}

Request.prototype.checkStatus = function() {
	if(this.httpRequest.readyState === 4) {
		if(this.httpRequest.status === 200) {
			this.result = this.httpRequest.responseText;
			this.listner();
			return true;
		} else {
			logger.log('There was a problem with the request.');
			return false;
		}
	}
	return false;
}
function checkLogin() {
	var req = new Request("?action=checklogin", function() {
		var body = document.getElementTagName("body")[0];
		body.appendChild(this.result);
	});
	req.makeRequest();
	req.go();
}

function msg(x) {
	var log = x['log'];
	for (var sx in log) {
		if(sx === "message")
			alert(y[sx]);
	}
}