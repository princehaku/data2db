<?php
/**flowphp核心文件
 * 初始化各项参数
 * @author princehaku
 * @site http://3haku.net
 */

$GLOBALS['_beginTime'] = microtime(TRUE);

date_default_timezone_set('Asia/Chongqing');

@session_start();
//加载日志类
include_once ("core/log/Log.class.php");
//定义路径
if (!defined("APP_PATH")) {
    define("APP_PATH", "/");
}
if (!defined("FLOWPHP_PATH")) {
    define("FLOWPHP_PATH", str_replace("core.php", "", __FILE__));
}

//加载系统默认配置文件
include_once (FLOWPHP_PATH . "/config/config.php");

$config = array();
//加载程序默认配置文件
if (file_exists($_SERVER['DOCUMENT_ROOT'] . APP_PATH . "/config/config.php")) {
    include_once ($_SERVER['DOCUMENT_ROOT'] . APP_PATH . "/config/config.php");
}
//合并两个配置文件
$config = array_merge($sysconfig, $config);
//加载数据库类
include_once (FLOWPHP_PATH . "core/db/DB.class.php");

//加载flowphp公用函数
$functionfiles = scandir(FLOWPHP_PATH . "common/");

foreach ($functionfiles as $i => $j) {
    $r = explode(".", $j);
    $ext = $r[count($r) - 1];
    if ($ext == "php" && file_exists(FLOWPHP_PATH . "common/" . $j)) {
        include_once (FLOWPHP_PATH . "common/" . $j);
    }
}

//加载所有程序公共文件
if (file_exists($_SERVER['DOCUMENT_ROOT'] . APP_PATH . "common/")) {
    $functionfiles = scandir($_SERVER['DOCUMENT_ROOT'] . APP_PATH . "common/");
    foreach ($functionfiles as $i => $j) {
        $r = explode(".", $j);
        $ext = $r[count($r) - 1];
        if ($ext == "php" && file_exists($_SERVER['DOCUMENT_ROOT'] . APP_PATH . "common/" . $j)) {
            include_once ($_SERVER['DOCUMENT_ROOT'] . APP_PATH . "common/" . $j);
        }
    }
}
//如果没有siteurl 则设置为访问域名
if (C('SITE_URL') == "") {
    C('SITE_URL', "http://" . $_SERVER['HTTP_HOST']);
    L()->i("没有在config.php指定SITEURL");
}

//定义站点根目录
C("DOC_BASE", $_SERVER['DOCUMENT_ROOT'] . APP_PATH);
//定义flowphp目录
C("APP_BASE", FLOWPHP_PATH);

//配置异常处理
if (C('DEBUG') != "" && C('DEBUG') == 0) {
    error_reporting(0);
    ini_set("display_errors", "0");
} else {
    ini_set("display_errors", "1");
    
    error_reporting(E_ALL);
    
    import("core.exception.FlowErrors");
    
    set_error_handler("FlowErrors::errorHandler");
    
    register_shutdown_function("FlowErrors::fatalHandler");

}
//关闭zend的php4兼容
if (ini_get('zend.ze1_compatibility_mode') == true) {
    ini_set('zend.ze1_compatibility_mode', 0);
}
//加载基类
import("core.flowphp");
//---------初始化完毕-----------
