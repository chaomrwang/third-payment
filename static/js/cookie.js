/**
 * cookie 设置
 * @param string  name  cookie名字
 * @param string  value  cookie值
 * @param date  expires  过期时间
 * @param string  path   cookie有效路径
 * @param string  domain   cookie有效域名
*/
function setCookie(name, value, expires, path, domain){
var cookie_string = name+"="+value;
if (expires) {
		cookie_string += ";expires="+expires.toUTCString();	
	}
if (path) {
		cookie_string += ";path="+path;
	}
if (domain) {
		cookie_string += ";domain="+domain;
	}
document.cookie = cookie_string;
}
/**
 * 删除cookie
 @param string name  cookie 的名字
 @param string path cookie的有效路径
 @param string domain cookie的有效域名
*/
function deleteCookie(name, path, domain){
	var d = new Date();
	d.setTime(d.getTime() - 1);
	var cookie_string = name+"="+" ;expires="+d.toUTCString();
if (path) {
		cookie_string += ";path="+path;
	}
if (domain) {
		cookie_string += ";domain="+domain;
	}
document.cookie = cookie_string;
}
/**
 * 读取cookie
 * @param string name	cookie的名字
 * @return sting value cookie的值
*/
function getCookie(name) {
	var cookieList = document.cookie.split("; ");
for (var i = 0; i < cookieList.length; i ++) {
		//把cookieList[i] 分割为数组
		var cookieItem = cookieList[i].split("=");
if (cookieItem[0] === name) {
			return cookieItem[1];
		}
	}
}

