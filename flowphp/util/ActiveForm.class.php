<?php

/**表单构造器
 * 
 * Enter description here ...
 * @author princehaku
 *
 */
class ActiveForm {

    protected $action;

    function buildSelect($name, $data, $otherproper = "") {
        $sel = "<select name=\"$name\" $otherproper>";
        foreach ($data as $key => $value) {
            $opi = "<option value=\"$value\">$key</option>";
            $sel .= $opi;
        }
        $sel .= "</select>";
        
        return $sel;
    }
}
?>