<?php

class preview extends FlowAction {

    /**预览文件格式设置
     *
     * @param unknown_type $request
     */
    function previewFormat($request) {
        $files = isset($_SESSION['files']) ? $_SESSION['files'] : array();
        if (empty($files)) {
            die(_e("please_upload_datafile_first"));
        }
        $file = $files[0]["tmp_name"];
        $file_encode = $request->getRawString("encode");
        $line_det = $request->getRawString("line_det");
        $row_det = $request->getRawString("row_det");
        $enclosure_det = $request->getRawString("enclosure_det");
        $skip_head = $request->getRawString("skip_head");
        
        if ($file_encode === null || $skip_head === null || $line_det === null || $row_det === null || $enclosure_det === null) {
            die("请填写相关信息");
        }
        
        // 打开文件
        $file = get_file($file);
        if (empty($file)) {
            $_SESSION['files'] = array();
            die(_e("please_upload_datafile_first"));
        }
        using("lib.datafile");
        $datafile = new datafile($file);
        $datafile->setFileEncode($file_encode);
        $r = 1;
        if ($skip_head) {
            $l = $datafile->popFrontLine($line_det);
        }
        $l1 = $datafile->popFrontLine($line_det);
        
        if ($l1 == '') {
            $this->assign("msg", "行分隔符无法从文件中解析");
        }
        using("lib.stringhelper");
        $shelper = new stringhelper($l1);
        $head = array();
        $rows1 = array();
        $rows2 = array();
        $r = 1;
        while (($sec = $shelper->popPreFix($row_det, $enclosure_det)) !== null) {
            $head[] = $r++;
            $rows1[] = $sec;
        }
        $l2 = $datafile->popFrontLine($line_det);
        $shelper = new stringhelper($l2);
        while (($sec = $shelper->popPreFix($row_det, $enclosure_det)) !== null) {
            $rows2[] = $sec;
        }
        $this->assign("head", $head);
        $this->assign("rows1", $rows1);
        $this->assign("rows2", $rows2);
        // 设置四个参数到session中
        $_SESSION['line_det'] = $line_det;
        $_SESSION['row_det'] = $row_det;
        $_SESSION['enclosure_det'] = $enclosure_det;
        $_SESSION['skip_head'] = $skip_head;
        // 设置预览数据到session
        $_SESSION['pre_rows1'] = $rows1;
        $_SESSION['pre_rows2'] = $rows2;
        $_SESSION['file_encode'] = $file_encode;
        
        $this->display("common/table.htpl");
    }

    /**预览修改导入源
     *
     * @param unknown_type $request
     */
    function previewAdjust($request) {
        $rows1 = $_SESSION['pre_rows1'];
        $rows2 = $_SESSION['pre_rows2'];
        $adjust_rule = $request->getRawString("adjustRule");
        
        $rows1 = apply_adjust_rule($rows1, $adjust_rule);
        $rows2 = apply_adjust_rule($rows2, $adjust_rule);
        
        $_SESSION["adjust_rule"] = $adjust_rule;
        $_SESSION["adjusted_row"] = $rows1;
        $head = array();
        $r = 1;
        foreach ($rows1 as $row) {
            $head[] = $r++;
        }
        
        $this->assign("head", $head);
        $this->assign("rows1", $rows1);
        $this->assign("rows2", $rows2);
        
        $this->display("common/table.htpl");
    }

    /**预览数据映射
     * 
     */
    function previewDataMapping($request) {
        $row = $_SESSION["adjusted_row"];
        $mapping_rule = $request->getRawString('mapRule');
        $sql_text = apply_data_mapping_rule($row, $mapping_rule);
        $_SESSION["mapping_rule"] = $mapping_rule;
        echo nl2br(htmlspecialchars($sql_text, ENT_QUOTES));
    }
}