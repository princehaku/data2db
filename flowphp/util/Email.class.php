<?php
/**email发信模块
 *
 */
//载入模版引擎
import("core.view.View");

class Email extends View {

    public function send($from, $to, $subject, $content) {
        
        $from = strip($from);
        $to = strip($to);
        
        $headers = 'MIME-Version: 1.0' . "\n";
        $headers .= 'Content-Type: text/html; charset=utf-8' . "\n";
        //$headers .= "To: $to \n";
        $headers .= 'From: ' . $from . "\n";
        
        $subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
        
        $result = mail($to, $subject, $content, $headers);
        
        if ($result) {
            return true;
        } else {
            L()->w($result);
            return false;
        }
    }
}
?>