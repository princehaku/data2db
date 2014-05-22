<?php
class stringhelper {
	
	private $source;
	
	public function stringhelper($string) {
		$this->source = $string;
	}
	/**分离出第一段
	 * 
	 * @param unknown_type $det 字段分隔符
	 * @param unknown_type $enclosure 字段环绕符
	 * @param int $recv 用于递归搜索判断
	 */
	public function popPreFix($det, $enclosure, $recv = 1) {
		if (strlen($this->source) == 0) {
			return null;
		}
		preg_match("/^([\s\S]*?)$det{1,1}/", $this->source,$matches);
		
		if (count($matches)==0) {
			$seg = $this->source;
			$this->source = '';
			return $seg;
		}
		$this->source = substr($this->source, strlen($matches[0]),strlen($this->source));
		if ($recv==1) {
			$content = $matches[1];
		} else {
			$content = $matches[0];
		}
		// 如果发现有字段环绕符
		if ($recv==1 && strpos($content, $enclosure) !== false) {
			$content = $matches[0];
			while (true) {
				$line = $this->popPreFix($det, $enclosure, 0);
				$content .= $line;
				// 到达结尾或者发现另一半 终止读取
				if ($line ===null || strpos($line, $enclosure)) {
					break;
				}
			}
		}
		return $content;
	}
	
}