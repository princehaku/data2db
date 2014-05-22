<?php

/**入口文件
 * 
 */

//定义你的app路径 注意后面有个/
define("APP_PATH", "/");
//定义flowphp的路径
define("FLOWPHP_PATH", $_SERVER['DOCUMENT_ROOT'] . APP_PATH . "/flowphp/");

include_once (FLOWPHP_PATH . "/core.php");
global $config;
if (isset($_GET['debug']) && $_GET['debug'] == 1) {
    C("DEBUG", 1);
} 

if ($_SERVER['HTTP_HOST'] == 'data2db.sinaapp.com') {
    C("IS_SAE", 1);
    C("CACHE_DIR", SAE_TMP_PATH . "/");
}

$flowphp = new flowphp();

$flowphp->run();
