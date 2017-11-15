<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,maximum-scale=1,initial-scale=1,minimum-scale=1,user-scalable=no">
    <title>请求支付</title>
    <style>
        .flex-basic{
            display: flex;
            display: -webkit-box;
            display: -webkit-flex;
            flex-direction:row;
            -webkit-flex-direction: row;
            -webkit-box-orient: horizontal;
        }

        .flex-wrap {
            flex-wrap: wrap;
        }

        .flex-vertical{
            display: flex;
            display: -webkit-flex;
            display: -webkit-box;
            flex-direction: column;
            -webkit-flex-direction: column;
            -webkit-box-orient: vertical;
        }

        .flex-jc-sb{
            justify-content:space-between;
            -webkit-justify-content: space-between;
            -webkit-box-pack: justify;
        }

        .flex-jc-c{
            justify-content:center;
            -webkit-justify-content: center;
            -webkit-box-pack: center;
        }

        .flex-jc-e{
            justify-content:flex-end;
            -webkit-justify-content: flex-end;
            -webkit-box-pack: end;
        }

        .flex-jc-s{
            justify-content:flex-start;
            -webkit-justify-content: flex-start;
            -webkit-box-pack: start;
        }

        .flex-jc-sa{
            justify-content: space-around;
            -webkit-justify-content: space-around;
        }

        .flex-ai-c{
            align-items: center;
            -webkit-align-items: center;
            -webkit-box-align: center;
        }
        .flex-ai-s{
            align-items: stretch;
            -webkit-align-items: stretch;
            -webkit-box-align: stretch;
        }

        .flex-1{
            flex:1;
            -webkit-flex: 1;
            -webkit-box-flex: 1;
            -moz-flex-shrink: 1;
            -webkit-flex-shrink: 1;
            flex-shrink: 1;
        }
    </style>
    <style>
        body,html{
            width: 100%;
            height: 100%;
            margin:0;
            padding:0;
        }
        .main{
            height: 100%;
            padding:0 10px;
            text-align: center;
        }
        .pay-icon-wrapper{
            height: 10.67rem;
        }
        .pay-icon{
            height: 2.33rem;
        }
        .pay-hint{
            font-weight: bold;
            font-size:1.5rem;
        }
        .pay-text{
            margin-top:2.92rem;
            font-size:1.33rem;
        }
        .pay-btn{
            display: inline-block;
            line-height: 4.08rem;
            height: 4.08rem;
            width: 15.5rem;
            -webkit-border-radius:2.42rem;
            -moz-border-radius:2.42rem;
            border-radius:2.42rem;
        }
        .b-green{
            margin-top: 2.67rem;
            border:1px solid rgb(94,235,203);
            color: rgb(94,235,203);
        }
        .b-blue{
            margin-top: 1.33rem;
            border:1px solid rgb(39,197,234);
            color: rgb(39,197,234);
        }
        .b-red{
            margin-top: 1.33rem;
            border:1px solid rgb(232,109,111);
            color: rgb(232,109,111);
        }
    </style>
</head>
<script src="js/zepto.min.js"></script>
<script src="js/cookie.js"></script>
<script src="js/main.js"></script>
<body>
    <div class="main flex-vertical flex-ai-s flex-jc-s">
        <div class="pay-icon-wrapper flex-basic flex-ai-c flex-jc-c">
            
        </div>
        <div class="pay-hint flex-basic flex-jc-c">已调起支付控件</div>
        <div class="pay-text">
            你看到此页面表示你测试模式下的代码是正确的，
            点击下面的按钮可以模拟各种支付功能.
        </div>
        <a id = 'pay' class="pay-btn b-green flex-basic flex-ai-c flex-jc-c">付款</a>
        <a id = 'cancel' class="pay-btn b-blue flex-basic flex-ai-c flex-jc-c">取消</a>
    </div>
</body>
<script>
document.getElementsByTagName('html')[0].style['font-size'] = document.body.clientWidth/375*12+'px';
window.onresize = function () {
    document.getElementsByTagName('html')[0].style['font-size'] = document.body.clientWidth/375*12+'px';
};

/*var uid = $_get().uid;
var appKey = $_get().appKey;
var out_trade_no = $_get().out_trade_no;
var total_fee = $_get().total_fee;
var body = $_get().body;
var detail = $_get().detail;
var good_id = $_get().good_id;
var notify_url = $_get().notify_url;
var sign = $_get().sign;*/

var out_trade_no = $_get().out_trade_no;
var redirect = $_get().redirect;
redirect = decodeURIComponent(redirect);

$(function () {
    $('#pay').on('click', function(){
        var url = "http://payment.coin.ffrj.net/Home/payTest?out_trade_no="+out_trade_no;
        $.ajax({
            type:"get",
            url:url,
            datatype:"json",
            //data:data,
            success:function(data){
                console.log(url)
                console.log(data)
                window.location.href = redirect;
            },
            error:function(data){
                console.log("请求失败")
            }
        })
    })

    $('#cancel').on('click', function(){
        window.location.href = redirect;
    })
})

    function $_get () {//获取拼接在响应头中的数据
        //var href = window.location.href ;
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
</script>
</html>