/**
 * 
 */
var Logger = {
		logElements: new Array(),
		log: function (message, type, source){
			var logEl = {
					message: message,
					type: type,
					source: source
			};
			this.logElements.push(logEl);
		},
		type: {log: "log", error: "error"}
};