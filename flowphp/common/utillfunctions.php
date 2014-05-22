<?php

/**包含js文件
 * 
 * @param string $name js文件名
 */
function jsinclude($namearray){
	if(!is_array($namearray))
	return "<script type='text/javascript' src='".C('SITE_URL').C("JS_DIR")."$namearray.js'></script>\n";
	else{
		$res="";
		foreach($namearray as $i=>$j){
			$res.=jsinclude($j);
		}
		return $res;
	}
}

/**包含css文件
 * @param string $name css文件名
*/
function cssinclude($namearray){
	if(!is_array($namearray))
	return '<link rel="stylesheet" type="text/css" href="'.C('SITE_URL').C("CSS_DIR").$namearray.'.css"/>'."\n";
	else{
		$res="";
		foreach($namearray as $i=>$j){
			$res.=cssinclude($j);
		}
		return $res;
	}
}
/**页面跳转 <meta> 方式的重定向
 * @param $url 跳转到的路径
* @param time 间隔跳转的时间
*/
function gotourl($url,$time){
	echo "<meta http-equiv='refresh' content=$time;url='$url'> ";
	//echo "please wait";
	return;
}
/**根具时间戳得到一个标准化的datetime
 *
* @param string $timeStamp
*/
function datetime($timeStamp){
	$timeStamp=(int)$timeStamp;
	$datetime=date("Y-m-d H:i:s",$timeStamp);
	return $datetime;
}
/**得到系统内目录
 * 如果存在返回本身
 * 如果不存在返回相对$basedir的路径
 * 注意:此路径依然可能不存在
 * @param string $dirname
 */
function getDirPath($dirname,$basedir=""){
	if($basedir==""){
		$basedir=C("DOC_BASE");
	}
	file_exists($dirname) ? $dirpath=$dirname : $dirpath=$basedir . $dirname;
	return $dirpath;
}
/**转义所有  预定字符 addslashes
 * 转义html字符 htmlspecialchars
* @param string|array $s
*/
function strip($s){
	if(!is_array($s)){
		if(ini_get('register_globals')!=1){
			$s=addslashes($s);
		}

		$s=htmlspecialchars($s);

		return $s;
	}else{
		foreach($s as $i=>$j){
			$s[$i]=strip($j);
		}
		return $s;
	}
}
/**加上引号
 *  对数组也有用
* @param string|array $value
*/
function addslashes_deep($value)
{
	$value = is_array($value) ?
	array_map('addslashes_deep', $value) :
	addslashes($value);
	return $value;
}
/**反过滤被加上的引号
 *
* @param string|array $value
*/
function stripslashes_deep($value)
{
	$value = is_array($value) ?
	array_map('stripslashes_deep', $value) :
	stripslashes($value);

	return $value;
}

function error_page($code) {
	if (!headers_sent()) {
		header("HTTP/1.1 $code NOT FOUND");
	}
	if (C("PAGE_$code")) {
		$v = new View();
		$v->display(C("PAGE_$code"));
	}
	exit();
}