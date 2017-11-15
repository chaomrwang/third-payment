<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<title>订单支付中</title>
</head>
<body>
	<img src="http://d.fenfenriji.com/web/wxapp/pinkshop/inbetweening2.png" alt="" style="display: block;padding-top: 4.2667rem;margin: 0 auto; width: 10.24rem">
	<p id='txt1' style="font-size: 0.6827rem;color: #555555;letter-spacing: 0;line-height: 1.024rem;text-align: center;">订单支付中</p>
	<p id='txt2' style="font-size: 0.5973rem;color: #999999;letter-spacing: 0;line-height: 0.896rem;text-align: center;margin-top: 0.1707rem;margin-bottom: 2.304rem;">粉娘帮您支付中,请耐心等待哟~</p>
</body>
<script src="<?=base_url().'static/js/zepto.min.js'?>"></script>
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
	function $_get () {//获取拼接在响应头中的数据
	        var href = window.location.search;
	        href = href.split("?")[1];  
	        information = href.split("&");
	        var getData = {};
	        $.each(information,function(index,item) {
	            item = item.split("=")
	            var key = item[0];
	            var item = item[1];
	            getData[key] = item;
	        })
	        return getData
	}
	var out_trade_no = '<?php echo $out_trade_no ?>';//$_get().out_trade_no;
	var redirect = '<?php echo $redirect ?>';//$_get().redirect;
	//redirect = decodeURIComponent(redirect);
	var url = '<?=base_url()?>';
	url = url + "Home/xiangPay?out_trade_no="+out_trade_no;
	var shKey =  window.sessionStorage.getItem('cdaa1dffc')
	if (shKey !== out_trade_no) {
		window.sessionStorage.setItem('cdaa1dffc', out_trade_no);
		PinkApi.ready(function () {
			$.ajax({
			    type:"get",
			    url:url,
			    datatype:"json",
			    success:function(data){
			        PinkJSBridge.callHandler('startPingxxPay', data, function (res) {
			        	console.log(res)
			        	if (res.result || res.payStatusResult === 1) {
			        		PinkJSBridge.callHandler('alert', {
			        			title: '支付成功~',
			        			emotion: 'happy',
			        			otherBtns: ['确认']
			        		}, function (res) {
			        			window.location.href = redirect;
			        		})
			        	} else {
			        		PinkJSBridge.callHandler('alert', {
			        			title: '支付失败~',
			        			emotion: 'cry',
			        			otherBtns: ['确认']
			        		}, function (res) {
			        			window.location.href = redirect;
			        		})
			        	}
			        } )

			    },
			    error:function(data){
			        console.log("请求失败")
			    }
			})
		})
	}else{
		window.location.href = redirect;
	}
	
</script>
</html>