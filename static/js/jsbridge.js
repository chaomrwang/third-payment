/**
 * 动态计算 rem 基准大小
 */

_APP_DOMAIN_ = 'http://shop-app.mall.fenfenriji.com';

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

//页面跳转按钮 
var jump = function(action) {
    var ac_pre = action.substr(0, 10);
    if ('pinksns://' == ac_pre) {
        if (window['PinkJSBridge']) {
            PinkApi.ready(function (api) { PinkJSBridge.callHandler('appJump', {'action': action});});
        }
    }

    var url_pre = action.substr(0, 4);
    if ('http' == url_pre) {
        if (window['PinkJSBridge']) {
            PinkJSBridge.callHandler('openWindow', {'url': action});
        }else{
            window.location.href = action;
        }
    }
}

//赠送入口
$(".present").on('click',function(){
	jump("payforfriends.html");
})

//选择好友赠送会员
var chooseFriendPay = function(){
    if (window['PinkJSBridge']) {
        var to_uid_url = _APP_DOMAIN_ + '/vip201703/vip.php';
        to_uid_url = to_uid_url + '?signature=1';
        PinkApi.ready(function (api) {
            PinkJSBridge.callHandler('chooseUser', {type:'meFollow'}, function (user) {
                to_uid_url += '&to_uid=' + user.uid
                    + '&nickname=' + encodeURIComponent(user.nickname);
                PinkJSBridge.callHandler('appJump', {'action': to_uid_url });
                //PinkJSBridge.callHandler('openWindow', {'url': to_uid_url});
            });
        });
    }
}

$('.givefriend').on('click', function (e) {
    e.preventDefault();
    chooseFriendPay();
});




$(".protocol a").on("click",function(){
    window.location.href = "./protocol.html"
})
