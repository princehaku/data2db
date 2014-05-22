<?php

class datafile {

	private $buffer;

	private $handler;
	
	private $readSize = 0;
	
	private $fileSize = 0;
	
	private $encode = 'utf8';
	
	public function setFileEncode($encode) {
		$this->encode = $encode;
	}
	
	public function datafile($handler) {
		$this->handler = $handler;
		$info = fstat($handler);
		$this->fileSize = $info["size"];
	}
	/**弹出第一行
	 * 如果到达文件尾 返回null
	 * @param unknown_type $line_det
	 */
	public function popFrontLine($line_det = "\n") {
		if (strlen($this->buffer)<1024) {
			$this->readToBuffer();
		}
		if (strlen($this->buffer) == 0) {
			return null;
		}
		preg_match("/^([\s\S]*?)$line_det{1,1}/", $this->buffer,$matches);
	
		if (count($matches)==0) {
			$seg = $this->buffer;
			$this->buffer = '';
			return $seg;
		}
		$this->buffer = substr($this->buffer, strlen($matches[0]),strlen($this->buffer));
		return $matches[1];
	}
	/**得到第一行
	 * 
	 * @param unknown_type $line_det
	 */
	public function getFrontLine($line_det = "\n") {
		if (strlen($this->buffer)<1024) {
			$this->readToBuffer();
		}
		if (strlen($this->buffer) == 0) {
			return null;
		}
		preg_match("/^([\s\S]*?)$line_det{1,30}/", $this->buffer,$matches);
	
		if (count($matches)==0) {
			$seg = $this->buffer;
			return $seg;
		}
		return $matches[1];
	}

	/**读取文件一部分到缓冲区
	 *
	* @param unknown_type $length
	*/
	public function readToBuffer($length = 1024) {
		$paragh = '';
		$i=0;
		while (!feof($this->handler) && $i++<$length) {
			$char = fgetc($this->handler);
			$paragh .= $char;
		}
		if ($this->encode != 'utf8') {
			$paragh = mb_convert_encoding($paragh, 'utf8', $this->encode);
		}
		$this->buffer .= $paragh;
	}
}