<?php

/**从xml中解析出左边栏
 * 
 * @param string $now
 */
function leftbar($now) {
    $xml = simplexml_load_file($_SERVER['DOCUMENT_ROOT'] . APP_PATH . '/data/leftbar.xml');
    
    $data = "<ul>";
    $found = 0;
    foreach ($xml->xpath('enty') as $enty) {
        if ($now == $enty->lang) {
            $data .= "<li class=\"current\"><a href=\"$enty->url\" class=\"dashboard\"><img
								src=\"$enty->imgurl\"/><span>" . _e($enty->lang) . "</span>
						</a>
						</li>";
            $found = 1;
        } else {
            if ($found) {
                $url = '#';
            } else {
                $url = $enty->url;
            }
            
            $data .= "<li><a href=\"$url\" class=\"dashboard\"><img
								src=\"$enty->imgurl\"/><span>" . _e($enty->lang) . "</span>
						</a>
						</li>";
        }
    }
    $data .= "</ul>";
    
    return $data;

}

/**得到文件(包含sae读取操作)
 * 
 * @param string $tmp_filename
 */
function get_file($tmp_filename) {
    $file = null;
    if (C("IS_SAE")) {
        if (file_exists(SAE_TMP_PATH . '/' . $tmp_filename)) {
            $file = fopen(SAE_TMP_PATH . '/' . $tmp_filename, "rb");
        } else {
            $s = new SaeStorage();
            $content = $s->read("data2db", $tmp_filename);
            if ($content !== false) {
                file_put_contents(SAE_TMP_PATH . '/' . $tmp_filename, $content);
                $file = fopen(SAE_TMP_PATH . '/' . $tmp_filename, "rb");
            }
        }
    } else {
        if (file_exists(C("DOC_BASE") . "/uploads/" . $tmp_filename)) {
            $file = fopen(C("DOC_BASE") . "/uploads/" . $tmp_filename, "rb");
        }
    }
    return $file;
}

/**转义转义符
 * 
 * @param string $string
 */
function apply_convert(&$string) {
    $string = str_replace("\\r", "\r", $string);
    $string = str_replace("\\n", "\n", $string);
    $string = str_replace("\\t", "\t", $string);
}

/**以一个json输出结束脚本
 * 
 */
function die_json($arry) {
    echo json_encode($arry);
    die();
}

/**使用指定的$alpha合并$source_array数组中key为$key_idxs的项
 * 注意: 会重建数组数字索引
 * @param array $source_array
 * @param array $key_idxs
 * @param string $alpha
 */
function apply_adjust_combine($source_array, $key_idxs, $alpha) {
    $st = -1;
    foreach ($key_idxs as $i => $key) {
        if ($st == -1) {
            $st = $key;
            continue;
        }
        if (isset($source_array[$st]) && isset($source_array[$key])) {
            $source_array[$st] = $source_array[$st] . $alpha . $source_array[$key];
        } else {
            continue;
        }
        if ($st != -1) {
            unset($source_array[$key]);
        }
    }
    reset($source_array);
    return $source_array;
}

/**使用指定的$alpha拆分$source_array数组中key为$key_idxs的项
 * 注意: 会重建数组索引
 * @param array $source_array
 * @param array $key_idxs
 * @param string $alpha
 */
function apply_adjust_divide($source_array, $key_idxs, $alpha) {
    $new_array = array();
    foreach ($source_array as $key => $value) {
        if (in_array($key, $key_idxs)) {
            $arrs = explode($alpha, $value);
            foreach ($arrs as $val) {
                $new_array[] = $val;
            }
        } else {
            $new_array[] = $value;
        }
    }
    
    return $new_array;
}

/**使用adjust规则对行进行拆分或者合并
 * `区分拆分合并类型 索引 位
 * ~区分索引位
 * 
 * @param unknown_type $row
 * @param unknown_type $adjust_rule
 */
function apply_adjust_rule($row, $adjust_rule) {
    
    $rules = explode("`", $adjust_rule);
    
    foreach ($rules as $i => $dec) {
        if ($i % 4 == 0 && $i != 0) {
            $rule = $rules[$i - 3];
            $idx = $rules[$i - 2];
            $idxs = explode("~", $idx);
            $alpha = $rules[$i - 1];
            if ($alpha == "") {
                $alpha = " ";
            }
            if ($rule == "divide") {
                $row = apply_adjust_divide($row, $idxs, $alpha);
            } else {
                $row = apply_adjust_combine($row, $idxs, $alpha);
            }
        }
    }
    
    return $row;
}

/**使用映射数据对数据进行映射
 * 
 * @param unknown_type $row
 * @param unknown_type $mapping_rule
 */
function apply_data_mapping_rule($row, $mapping_rule) {
    $output = '';
    $rules = explode("|", $mapping_rule);
    $idx = 1;
    foreach ($rules as $i => $dec) {
        if ($i % 2 == 0 && $i != 0) {
            $formname = $rules[$i - 2];
            $cols_mapping_rule_string = $rules[$i - 1];
            $cols_mapping_rules = explode("%", $cols_mapping_rule_string);
            $headarray = array();
            $valuearray = array();
            foreach ($cols_mapping_rules as $cols_mapping_rule) {
                $rule = explode("@", $cols_mapping_rule);
                if (count($rule) == 3) {
                    switch ($rule[0]) {
                        case '$' :
                            $headarray[] = '`' . $rule[2] . '`';
                            // 注意这里是key - 1 因为前段显示的是第x行
                            $valuearray[] ="'" . addslashes($row[$rule[1] - 1]) ."'";
                            break;
                        case '#' :
                            $headarray[] = '`' . $rule[2] . '`';
                            $valuearray[] = '@key' . $rule[1];
                            break;
                    }
                }
            }
            $output .= "INSERT INTO `$formname` (" . implode(",", $headarray) . ") values (" . implode(",", $valuearray) . ");\n";
            
            $output .= "SET @key" . $idx++ . " =  LAST_INSERT_ID();\n";
        }
    }
    return $output;
}