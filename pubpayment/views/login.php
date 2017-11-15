<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<title>登录</title>
</head>
<body>
	<img src="https://img.fenfenriji.com//FE/B4/E5/Image/E89F9EEC-30E4-FC91-0A62-59CCB6882B21.png" alt="" style="display: block;width: 40%;margin: 0 auto;padding-top: 1.0667rem;padding-bottom: 0.4267rem">
	<p style="text-align: center;font-size: 0.5973rem;letter-spacing: 0.5px">登录享受更多特权哟~</p>
	<span id="login" data-login='1' data-go='1' style="display: block; width: 4.096rem;height: 1.5rem;line-height: 1.5rem;background: url('https://img.fenfenriji.com//FE/B4/E5/Image/5F3DFF62-6036-F7DB-1602-59CCB76E9E33.png');background-size: 100% 100%; text-align: center;color: #fff;font-size: 0.768rem;font-weight: bold;margin: 0.64rem auto 0">登&nbsp;&nbsp;录</span>
</body>
<script>
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

	    fn();
	}(document, window));
	var PinkApi = (function () {
	    function pinkJsBridgeReady(readyCallback) {
	        if (readyCallback && typeof readyCallback == 'function') {
	            var Api = this;
	            var pinkReadyFunc = function () {
	                readyCallback(Api);
	            };
	            if (typeof window.PinkJSBridge == "undefined") {
	                if (document.addEventListener) {
	                    document.addEventListener('PinkJSBridgeReady', pinkReadyFunc, false);
	                }else if(document.attachEvent) {
	                    document.attachEvent('PinkJSBridgeReady', pinkReadyFunc);
	                    document.attachEvent('onPinkJSBridgeReady', pinkReadyFunc);
	                }
	            }else{
	                pinkReadyFunc();
	            }
	        }
	    }
	    return {
	        version: "0.1",
	        ready: pinkJsBridgeReady
	    };
	})();
	var bool = 1
	var redirect = '<?php echo $redirect ?>';
	function goPage () {
		var url = 'http://payment.coin.ffrj.net/Home/target?signature=1&o=native&redirect='+redirect
		PinkJSBridge.callHandler('signatureUrl', {url: url}, function (res) {
			window.location.href = res.url;
		})
	}
	function login () {
		PinkApi.ready(function () {
			PinkJSBridge.callHandler('getUserInfo', {}, function (res) {
				if (!res.uid) {
					if (bool === 1) {
						PinkJSBridge.callHandler('appJump', {'action': 'pinksns://user/login?type=qq'}, function () {})
						bool = 0
					}
					setTimeout(function () {
						login()
					}, 1000)
				} else {
					goPage()
				}
			})
		})
	}
	login()
	document.getElementById('login').onclick = function () {
		PinkJSBridge.callHandler('appJump', {'action': 'pinksns://user/login?type=qq'})
	}
</script>
</html>