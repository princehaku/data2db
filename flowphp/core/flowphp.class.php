<?php
/**flowphp基类
 *
* @author princehaku
* @site http://3haku.net
*/

import("core.request.Dispatcher");
import("core.request.Request");
import("core.db.Module");
import("core.db.SessionModule");
import("core.exception.FlowException");
import("core.action.FlowAction");
import("core.action.FlowActiveAction");
import("core.action.FlowStaticAction");

class flowphp {

	public function run() {
		header('Content-Type: text/html; charset=utf-8');

		$dispatcher = new Dispatcher();
		//加载url分析类 分析url
		if (C("URL_DISPACHER") != "sys") {
			using(C("URL_DISPACHER"));
			$r = explode(".", C("URL_DISPACHER"));
			$dispatcher = new $r[count($r) - 1]();
		}

		$appname = $dispatcher->getAppName();

		$actionname = $dispatcher->getActionName();

		//初始化请求类
		$request = new Request();
		//加载对应的控制类
		if (file_exists(C("DOC_BASE") . "/action/$appname.class.php")) {
			$r = include_once (C("DOC_BASE") . "/action/$appname.class.php");
		} else {
			$r = false;
		}

		$showlog = 1;

		if ($r) {
			L()->i("控制文件加载完成$appname.class.php");

			$action = new $appname();

			$rc = new ReflectionClass($appname);

			if (C("DEBUG") != 0) {
				$beenExtended = $rc->isSubclassOf("FlowStaticAction") || $rc->isSubclassOf("FlowAction") || $rc->isSubclassOf("FlowActiveAction");
			} else {
				$beenExtended = 1;
			}
			$no_such_method = 0;

			if ($beenExtended) {
				// 检测方法
				try {
					$rm = new ReflectionMethod($action, $actionname);
				} catch (Exception $e) {
					L()->e("控制类" . $appname . "没有" . $actionname . "方法");
					$no_such_method = 1;
					if (C("DEBUG") == 0) {
						error_page(404);
					}
				}
				// 检测参数
				if (C("DEBUG") != 0 && isset($rm) && $rm != null && $rm->getNumberOfParameters() != 1) {
					L()->e("控制类" . $appname . "的" . $actionname . "方法必须含有一个request参数");
				} else {
					if ($no_such_method == 0) {
						try {
							$action->__setViewEngine(new View());
							// 执行方法
							$action->$actionname($request);
	
						} catch (Exception $e) {
							L()->e($e->getMessage());
						}
					}
					// 如果是FlowActiveAction或者FlowStaticAction 不打印日志
					if ($rc->isSubclassOf("FlowActiveAction") || $rc->isSubclassOf("FlowStaticAction")) {
						$showlog = 0;
					}
				}
			} else {
				L()->e("控制文件必须继承FlowAction中的一种");
				if (C("DEBUG") == 0) {
					error_page(404);
				}
			}

		} else {
			if (C("DEBUG") == 0) {
				error_page(404);
			}
				
			L()->e("控制文件不存在$appname.class.php");
		}

		//打印日志
		if (C("DEBUG") && C('SHOW_LOG') == 1 && $showlog) {
			$execTime = (microtime(TRUE) - $GLOBALS['_beginTime']);
			L()->i("执行所用时间: " . $execTime);
			L()->i("执行所用内存: " . number_format(memory_get_usage() / 1024, 2) . "kb");
			L()->p();
		}

	}

}

?>