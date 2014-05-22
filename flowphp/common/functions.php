<?php
/**系统函数库
 *
 * @author princehaku
 * @site http://3haku.net
 */


/**设置和获得配置的选项
 * 如果$value不为空 则是设置选项 并返回value
 * 注意 设置的配置非持久
 * @param $param
 * @param $value default null
 * @param &$config default null
 */
function C($param,$value=null,&$config=null){

	global $config;
	
	if ($value!==null) {
		$config[$param]=$value;
		return $value;
	}
	
	if (is_array($param)) {
		$config=$param;
	}
	
	if (isset($config[$param])) {
		return $config[$param];
	}
	
	return null;

}
/**包含数据模型
 * @param $modleName 模型的名字 结尾需要是.class.php
 */
function M($modelName){
	global $m;
	
	isset($m)?$m:$m=array();
	
	if(!array_key_exists($modelName, $m)){
		$t=include_once(C("DOC_BASE")."/model/".$modelName.".class.php");
		
		if(!$t){
			throw new FlowException("模型".$modelName."载入失败, 没有文件 ".C("DOC_BASE")."/module/".$modelName.".class.php");
		}else{
			L()->i("模型".$modelName."载入成功");
		}
		$m[$modelName]=new $modelName($modelName);
	}
	//$m->setFormName($modleName);
	
	return $m[$modelName];
	
}
/**得到一个数据库连接
 * 唯一 每次都是同一个数据库连接
 * @return $db
 */
function D($config=null){
	global $db;
	if($db==null){
		$db=new DB($config);
	}
	return $db;
}
/**多语言支持
 * @param $phrase 词根
 * @param $phrase 语言标识符
 */
function _e($phrase,$loc="cn"){

	$langtmp=array();
	
	global $langtmp;
	//伪单例模式  只载入一次语言文件
	if (empty($langtmp[$loc])) {
		$langtmp[$loc]=array();
		
		if (file_exists(C("APP_BASE")."/lang/lang_$loc.php")){
			include_once(C("APP_BASE")."/lang/lang_$loc.php");
			$langtmp[$loc]=array_merge($langtmp[$loc],$lang);
			$lang=array();
		}
		
		if (file_exists(C("DOC_BASE")."/lang/lang_$loc.php")) {
			include_once(C("DOC_BASE")."/lang/lang_$loc.php");
			$langtmp[$loc]=array_merge($langtmp[$loc],$lang);
			$lang=array();
		}
	}	
	$phrase = (string)$phrase;
	
	if (isset($langtmp[$loc][$phrase])) {
		return $langtmp[$loc][$phrase];
	} else {
		return "No Such Phrase";
	}
	
	//打开语言文件

}
/**得到日志类
 * 单例模式
 * @return $L
 */
function L(){
	global $L;
	if($L==null){
		//初始化日志类
		$L=new Log();
	}
	return $L;
}
/**包含一个基类库
 * 如果不存在 会从googlecode下载至本地
 * @param string $path 定义为xx.xxx.xx  结尾需要是.class.php
 */
function import($path){

	$r=explode(".",$path);

	$url="";
	foreach($r as $i=>$j){
		$url=$url."/".$j;
	}
	$t=include_once(C("APP_BASE").$url.".class.php");
	if(!$t){
		L()->w("系统库".$path."载入错误, 没有文件 ".C("APP_BASE").$url.".class.php");
		//如果没有找到 自动从googlecode下载
		$file=file_get_contents("http://flowphp.googlecode.com/svn/trunk/flowphp".$url.".class.php");
		file_put_contents(C("APP_BASE").$url.".class.php",$file);
		//延迟
	}else{
		L()->i("系统库".$path."载入成功");
	}
}
/**包含用户定义的库
 * 
 * @param string $path 定义为xx.xxx.xx  结尾需要是.class.php
 */
function using($path){

	$r=explode(".",$path);

	$url="";
	foreach($r as $i=>$j){
		$url=$url."/".$j;
	}
	$t=include_once(C("DOC_BASE")."/".$url.".class.php");
	
	if(!$t){
		L()->w("用户库".$path."载入失败, 没有文件 ".C("DOC_BASE").$url.".class.php");
	}else{
		L()->i("用户库".$path."载入成功");
	}
}