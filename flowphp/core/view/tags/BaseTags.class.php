<?php

/**基本标签库
 * 
 * @author princehaku
 *
 */
class BaseTags {

    public function apply($source) {
        //%include标签替换
        $this->parseTplInclude($source);
        //LIST标签替换
        $this->parseList($source);
        //替换全局{$}标签为单词
        $this->parseToken($source);
        //替换全局{_}标签为多语言词汇
        $this->parseLang($source);
        //替换全局{#}标签为配置
        $this->parseConfig($source);
        //替换全局(%)标签为函数
        $this->parseFunction($source);
        //替换if标签对
        $this->parseIf($source);
        
        return $source;
    }

    /**解析<if con="xxxx">替换if标签
     *
     * @param mixed $source
     */
    private function parseIf(&$source) {
        
        preg_match_all("/\<if\s*?con=[\'\"](.*?)[\'\"]\s*\>/", $source, $matches, PREG_SET_ORDER);
        
        if ($matches == null) {
            return false;
        }
        
        foreach ($matches as $i => $j) {
            $conditiontag = $j[1];
            $val = "<?php if($conditiontag): ?>";
            $conditiontag = str_replace("$", "\\$", $conditiontag);
            $conditiontag = str_replace("[", "\[", $conditiontag);
            $conditiontag = str_replace("]", "\]", $conditiontag);
            $source = preg_replace("/\<if\s*?con=[\'\"]" . $conditiontag . "[\'\"]\s*\>/", $val, $source);
        }
        
        $source = preg_replace("/<else>/", "<?php else: ?>", $source, 1);
        
        $source = preg_replace("/<\/if>/", "<?php endif; ?>", $source, 1);
        
        return true;
    
    }

    /**解析{#xxx}的内容 替换成config的内容
     *
     * @param mixed $source
     */
    private function parseConfig(&$source) {
        
        //替换{#xxx} 为 config内容
        preg_match_all("/\\{#(.*?)\\}/", $source, $matches, PREG_SET_ORDER);
        
        if ($matches == null) {
            return false;
        }
        foreach ($matches as $i => $j) {
            $keyname = $j[1];
            if ($keyname == null) {
                L()->i("标签" . $keyname . "解析失败");
                continue;
            }
            $val = "<?php echo C(\"" . $keyname . "\"); ?>";
            $source = preg_replace("/\\{#" . $j[1] . "\\}/", $val, $source);
        }
        
        return true;
    
    }

    /**解析{%include *}的内容 
     * 载入其他模板
     *
     * @param mixed $source
     */
    private function parseTplInclude(&$source) {
        
        //替换{#xxx} 为 config内容
        preg_match_all("/\\{%include (.*?)\\}/", $source, $matches, PREG_SET_ORDER);
        
        if ($matches == null) {
            return false;
        }
        foreach ($matches as $i => $j) {
            $keyname = $j[1];
            if ($keyname == null) {
                L()->w("#include标签" . $keyname . "解析失败");
                continue;
            }
            $otplfilepath = C("DOC_BASE") . C("VIEW_DIR") . $keyname;
            
            if (file_exists($otplfilepath)) {
                L()->i("Include模版文件载入完毕 " . C("VIEW_DIR") . $keyname);
            } else {
                L()->e("Include模版文件不存在  " . C("VIEW_DIR") . $keyname);
                throw new FlowException("Include模版文件不存在  " . C("VIEW_DIR") . $keyname . " 由" . $this->tplpath . "载入");
                continue;
            }
            $val = file_get_contents($otplfilepath);
            $j[1] = str_replace("/", "\/", $j[1]);
            $j[1] = str_replace(".", "\.", $j[1]);
            $source = preg_replace("/\\{%include " . $j[1] . "\\}/", $val, $source);
        }
        
        return true;
    
    }

    /**解析{%xxx argv1 argv2...}的内容 替换成function的内容
     *
     * @param mixed $source
     */
    private function parseFunction(&$source) {
        
        preg_match_all("/\\{%([\s\S]*?)\\}/", $source, $matches, PREG_SET_ORDER);
        
        if ($matches == null) {
            return false;
        }
        
        foreach ($matches as $i => $j) {
            if (empty($j[1])) {
                L()->w("标签" . $keyname . "解析失败");
                continue;
            }
            $func = explode(" ", $j[1]);
            
            $keyname = $func[0];
            unset($func[0]);
            $param = implode(",", $func);
            
            $val = "<?php if(function_exists('$keyname')) {echo $keyname($param);} ?>";
            
            $j[1] = str_replace("/", "\/", $j[1]);
            $j[1] = str_replace(".", "\.", $j[1]);
            $source = preg_replace("/\\{%" . $j[1] . "\\}/", $val, $source);
        }
        
        return true;
    
    }

    /**解析{$xxx}标签为实体
     *
     * @param mixed $source
     */
    private function parseToken(&$source) {
        //替换{$xxx}标签为实体
        preg_match_all("/\\{\\$(.*?)\\}/", $source, $matches, PREG_SET_ORDER);
        
        if ($matches == null) {
            return false;
        }
        
        foreach ($matches as $i => $j) {
            $val = "if (isset(\$$j) { echo \$$j ;}";
            $tagname = $j[1];
            $pos = strpos($tagname, ".");
            
            if ($pos == null || $pos <= 0) {
                $keyname = "\$_res";
                $keyval = $tagname;
            } else {
                $keyname = "\$" . substr($tagname, 0, $pos);
                $keyval = substr($tagname, $pos + 1, strlen($tagname) - $pos);
            }
            //L()->i("实体".$keyname."解析成功");
            $val = "<?php if (isset(" . $keyname . "['" . $keyval . "'])) {echo " . $keyname . "['" . $keyval . "'];} else if (isset(\$" . $keyval . ") && !is_array(\$" . $keyval . ")) { echo \$" . $keyval . ";} ?>";
            $j[1] = str_replace("/", "\/", $j[1]);
            $j[1] = str_replace(".", "\.", $j[1]);
            $source = preg_replace("/\\{\\$" . $j[1] . "\\}/", $val, $source);
        }
        
        return true;
    }

    /**解析{_xxx}标签为多语言词汇
     *
     * @param mixed $source
     */
    private function parseLang(&$source) {
        //替换{$xxx}标签为实体
        preg_match_all("/\\{\\_(.*?)\\}/", $source, $matches, PREG_SET_ORDER);
        
        if ($matches == null) {
            return false;
        }
        
        foreach ($matches as $i => $j) {
            $val = "echo _e(\$$j)";
            $tagname = $j[1];
            //L()->i("实体".$tagname."解析成功");
            $val = "<?php echo _e(\"" . $tagname . "\"); ?>";
            $j[1] = str_replace("/", "\/", $j[1]);
            $j[1] = str_replace(".", "\.", $j[1]);
            $source = preg_replace("/\\{\\_" . $j[1] . "\\}/", $val, $source);
        }
        
        return true;
    }

    /**
     * 解析<list 列表标签
     *
     * @param mixed $source     html源
     * @param mixed $resource   数据源
     */
    private function parseList(&$source, $n = 0) {
        
        //替换LIST标签
        $matches = array();
        
        preg_match("/<list ([\s|\S]*?)>/", $source, $matches);
        
        if (null == $matches) {
            return false;
        }
        //参数分离
        $parm = $matches[1];
        preg_match("/.*?name=[\"\'\s](.*?)[\"\'\s].*?/", $parm, $tag);
        //list 的name
        $tagname = $tag[1];
        preg_match("/.*?id=[\"\'\s](.*?)[\"\'\s].*?/", $parm, $tag);
        //list 的id
        $tagid = $tag[1];
        
        //按照resource里面的东西替换标签
        //$sour=$this->parseToken($sour,$resource);

        $pos = strpos($tagname, ".");
        
        if ($pos == null || $pos <= 0) {
            $keyname = "\$_res";
            $keyval = $tagname;
        } else {
            $keyname = "\$" . substr($tagname, 0, $pos);
            $keyval = substr($tagname, $pos + 1, strlen($tagname) - $pos);
        }
        //替换一层
        $source = preg_replace("/<list ([\s|\S]*?)>/", "<?php if(isset(" . $keyname . "['" . $keyval . "'])) {foreach (" . $keyname . "['" . $keyval . "'] as \$i$n=>\$$tagid){ ?>", $source, 1);
        
        $source = preg_replace("/<\/list>/", "<?php } } ?>", $source, 1);
        
        while ($this->parseList($source, ++$n)) {
            return true;
        }
    
     //L()->i("List标签".$tagname."解析成功");
    }
}
?>