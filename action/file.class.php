<?php

/**文件操作模块
 * 
 * @author princehaku
 *
 */
class file extends FlowAction {

    /**上传操作
     * 
     * @param unknown_type $request
     */
    function upload($request) {
        if (!isset($_FILES["Filedata"]) || !is_uploaded_file($_FILES["Filedata"]["tmp_name"]) || $_FILES["Filedata"]["error"] != 0) {
            echo "ERROR:REQUEST ERROR";
            exit(0);
        }
        // 用于修复sessionid不一致的情况
        if (isset($_COOKIE['PHPSESSID']) && $_COOKIE['PHPSESSID'] != $request->getString("PHPSESSID")) {
            setcookie("PHPSESSID", $request->getString("PHPSESSID"));
        }
        session_id($request->getString("PHPSESSID"));
        $ext = pathinfo($_FILES["Filedata"]["name"], PATHINFO_EXTENSION);
        $tmp_filename = md5(uniqid()) . ".$ext";
        if (C("IS_SAE")) {
            // 如果是sae 先移动到临时文件目录
            move_uploaded_file($_FILES["Filedata"]["tmp_name"], SAE_TMP_PATH . '/' . $tmp_filename);
            $s = new SaeStorage();
            // 写入sae storage
            $res = $s->write("data2db", $tmp_filename, file_get_contents(SAE_TMP_PATH . '/' . $tmp_filename));
            if ($res < 0) {
                die("上传失败  错误代码 " . $res);
            }
        } else {
            move_uploaded_file($_FILES["Filedata"]["tmp_name"], 'uploads/' . $tmp_filename);
        }
        // 把文件名放入session中
        $arry = isset($_SESSION['files']) ? $_SESSION['files'] : array();
        $new_file = array();
        $new_file["source_name"] = $_FILES["Filedata"]["name"];
        $new_file["tmp_name"] = $tmp_filename;
        array_push($arry, $new_file);
        $_SESSION['files'] = $arry;
        die();
    }

    /**移除已经上传的文件
     * 
     * @param unknown_type $request
     */
    function delUploaded($request) {
        $result['status'] = 1;
        $result['msg'] = '删除失败';
        $source_filename = $request->getString("filename");
        if ($source_filename == '') {
            die_json($result);
        }
        $files = $_SESSION['files'];
        $tmp_filename = '';
        foreach ($files as $i => $file) {
            if ($file["source_name"] == $source_filename) {
                $tmp_filename = $file["tmp_name"];
                // 从session中移除
                unset($_SESSION['files'][$i]);
                break;
            }
        }
        if ($tmp_filename == '') {
            die_json($result);
        }
        if (C("IS_SAE")) {
            $s = new SaeStorage();
            $res = $s->delete("data2db", $tmp_filename);
            if ($res < 0) {
                $result['msg'] = "删除失败  错误代码 " . $res;
                die_json($result);
            }
        } else {
            if (file_exists(C('DOC_BASE') . 'uploads/' . $tmp_filename)) {
                unlink(C('DOC_BASE') . 'uploads/' . $tmp_filename);
            }
        }
        
        $result['status'] = 1;
        $result['msg'] = "删除成功";
        die_json($result);
    }
    
}