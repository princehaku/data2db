<?php
/**自动装载lib下的类文件
 * 
 * @param unknown_type $classname
 */
function __autoload($classname) {
  	import("lib.".$classname);
  	using("lib.".$classname);
}
?>