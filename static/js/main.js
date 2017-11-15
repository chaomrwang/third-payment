/**
 * 动态计算 rem 基准大小
 */
!(function(doc, win) {
    var docEle = doc.documentElement,
        evt = "onorientationchange" in window ? "orientationchange" : "resize",
        fn = function() {
            var width = docEle.clientWidth;
            width && (docEle.style.fontSize = width / 16 + "px");
        };

    // win.addEventListener(evt, fn, false);
    // doc.addEventListener("DOMContentLoaded", fn, false);
    fn();
}(document, window));
var PinkApi=(function(){
		function pinkJsBridgeReady(a){
			if(a&&typeof a=='function'){
				var b=this;
				var c=function(){
					a(b)
				};
				if(typeof window.PinkJSBridge=="undefined"){
					if(document.addEventListener){
						document.addEventListener('PinkJSBridgeReady',c,false)
					} else if(document.attachEvent){
						document.attachEvent('PinkJSBridgeReady',c);
						document.attachEvent('onPinkJSBridgeReady',c);
					}
				} else {
					c() 
				}
			}
		}
		return {
			version:"0.1",
			ready:pinkJsBridgeReady
		}
	})();
function jump(address){
	if (window['PinkJSBridge']) {
		PinkJSBridge.callHandler('signatureUrl', {url: address}, function(data) {
			if (data['url']) {
				PinkJSBridge.callHandler('openWindow', data);
			}
			// else {
			// 	login();
			// }
		});
	}
	else {
		alert(11)
		window.location.href = address;	
	}
}