<?php

/**主页
 * @author princehaku
 * @site http://3haku.net
 */
class main extends FlowAction {

    function index($request) {
        gotourl("?action=main&method=uploadDatafile", 0);
    }

    /**上传数据文件
     *
     * @param unknown_type $request
     */
    function uploadDatafile($request) {
        $files = isset($_SESSION['files']) ? $_SESSION['files'] : array();
        $this->assign("uploaded", $files);
        if (count($files) == 0) {
            $this->assign("style", "display:none;");
        }
        $this->display("uploaddatafile.htpl");
    }

    /**设置数据源格式
     *
     * @param unknown_type $request
     */
    function setSourceFormat($request) {
        $files = isset($_SESSION['files']) ? $_SESSION['files'] : array();
        if (empty($files)) {
            $this->error(_e("please_upload_datafile_first"), "?action=main&method=uploadDatafile", 3);
            die();
        }
        
        $line_det = isset($_SESSION['line_det']) ? $_SESSION['line_det'] : '' ;
        $row_det = isset($_SESSION['row_det']) ? $_SESSION['row_det'] : '' ;
        $enclosure_det = isset($_SESSION['enclosure_det']) ? $_SESSION['enclosure_det'] : '' ;
        
        switch (pathinfo($files[0]['source_name'], PATHINFO_EXTENSION)) {
        	case 'csv':
		        $line_det = "\\\\r\\\\n";
		        $row_det = ',';
		        $enclosure_det = '"';
        		break;
        }
        $this->assign('line_det', $line_det);
        $this->assign('row_det', $row_det);
        $this->assign('enclosure_det', $enclosure_det);
        
        $this->display("setsourceformat.htpl");
    }

    /**来源调整
     *
     * @param unknown_type $request
     */
    function adjustSource($request) {
        $files = isset($_SESSION['files']) ? $_SESSION['files'] : array();
        if (empty($files)) {
            $this->error(_e("please_upload_datafile_first"), "?action=main&method=uploadDatafile", 3);
            die();
        }
        $cols1 = isset($_SESSION['pre_rows1']) ? $_SESSION['pre_rows1'] : array();
        $cols2 = isset($_SESSION['pre_rows2']) ? $_SESSION['pre_rows2'] : array();
        
        $head = array();
        $r = 1;
        foreach ($cols1 as $one) {
            $head[] = $r++;
        }
        
        $this->assign("head", $head);
        $this->assign("length", count($head));
        $this->assign("rows1", $cols1);
        $this->assign("rows2", $cols2);
        
        $this->display("adjustsource.htpl");
    }

    /**设置数据映射
     *
     * @param unknown_type $request
     */
    function setDestination($request) {
        $files = isset($_SESSION['files']) ? $_SESSION['files'] : array();
        if (empty($files)) {
            $this->error(_e("please_upload_datafile_first"), "?action=main&method=uploadDatafile", 3);
            die();
        }
        $cols1 = $_SESSION["adjusted_row"];
        $head = array();
        $r = 1;
        $cols = array();
        foreach ($cols1 as $one) {
            $cols[$r - 1]["idx"] = $r;
            $cols[$r - 1]["data"] = $cols1[$r - 1];
            $head[] = $r++;
        }
        $this->assign("head", $head);
        $this->assign("cols1", $cols);
        $this->display("setdestination.htpl");
    }

    function export($request) {
        $files = isset($_SESSION['files']) ? $_SESSION['files'] : array();
        if (empty($files)) {
            $this->error(_e("please_upload_datafile_first"), "?action=main&method=uploadDatafile", 3);
            die();
        }
        header("Content-type:application/octet-stream");
        header("Content-Disposition:attachment;filename=" . $files[0]['source_name'] . date("-Y-m-d-H-i-s", time()) . ".sql");
        $tmp_filename = $files[0]["tmp_name"];
        $file = get_file($tmp_filename);
        if (empty($file)) {
            $_SESSION['files'] = array();
            die(_e("please_upload_datafile_first"));
        }
        
        using("lib.datafile");
        using("lib.stringhelper");
        $datafile = new datafile($file);
        $datafile->setFileEncode($_SESSION['file_encode']);
        $line_det = $_SESSION['line_det'];
        $row_det = $_SESSION['row_det'];
        $enclosure_det = $_SESSION['enclosure_det'];
        $adjust_rule = $_SESSION["adjust_rule"];
        $mapping_rule = $_SESSION["mapping_rule"];
        
        if ($_SESSION['skip_head']) {
            $l = $datafile->popFrontLine($line_det);
        }
        $output = '';
        $i = 0;
        while (($l = $datafile->popFrontLine($line_det)) !== null) {
            $shelper = new stringhelper($l);
            $row = array();
            while (($sec = $shelper->popPreFix($row_det, $enclosure_det)) !== null) {
                $row[] = $sec;
            }
            $row = apply_adjust_rule($row, $adjust_rule);
            $sql_text = apply_data_mapping_rule($row, $mapping_rule);
            $output = $output . $sql_text;
        }
        
        echo $output;
    }
}