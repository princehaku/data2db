<?php

/**模板框架
 * 用于输出模版
 * @author princehaku
 * @site http://3haku.net
 */
class View {

    /**资源列表
     *
     */
    private $resource;

    /**模板路径
     *
     */
    private $tplpath;

    protected $taglibs = array(
        "Base"
    );

    /**登记资源到列表
     * 如果是一个类 会被强制转换成一个数组
     * @param string $key
     * @param string|array|class $res
     */
    public function assign($key, $res) {
        
        if (is_object($res)) {
            $res = (array)$res;
        }
        $this->resource[$key] = $res;
        return $res;
    }

    /**得到应该输出的结果串
     *
     * @param unknown_type $viewname
     * @return string
     */
    public function getHtml($viewname) {
        $oldcache = ob_get_contents();
        if (false === $oldcache) {
        
        }
        ob_start();
        $this->display($viewname);
        $content = ob_get_contents();
        ob_clean();
        return $content;
    }

    /**打印输出
     *
     * @param $viewname
     */
    public function display($viewname) {
        
        global $_res, $L;
        
        $_res = $this->getR();
        
        //$viewname=str_replace(".html",".tpl",$viewname);

        $cachedir = getDirPath(C("CACHE_DIR"));
        
        $tpldir = getDirPath(C("VIEW_DIR"));
        
        //检测缓存文件夹是否存在
        if (!file_exists($cachedir)) {
            L()->w("缓存文件夹不存在 自动创建");
            if (!mkdir(C("CACHE_DIR"))) {
                L()->w("缓存文件夹" . C("CACHE_DIR") . "创建失败");
            }
        }
        //模板文件
        $tplfile = $tpldir . $viewname;
        
        $this->tplpath = $tplfile;
        
        //搜索模板文件是否存在
        if (file_exists($tplfile)) {
            L()->i("模版文件载入完毕 " . C("VIEW_DIR") . $viewname);
        } else {
            throw new FlowException("模版文件不存在  " . C("VIEW_DIR") . $viewname);
            return;
        }
        //缓存文件处理
        $cachefile = $cachedir . str_replace("/", "__", $viewname) . "__cache.php";
        
        //如果缓存文件不比templatefile新  而且缓存文件存在 而且没有开启debug 直接包含缓存
        if (file_exists($cachefile) && (filemtime($cachefile) > filemtime($tplfile)) && !C("DEBUG")) {
            include_once ($cachefile);
            return;
        }
        L()->i("缓存过期 重新编译");
        //读取模板文件
        $c = file_get_contents($tplfile);
        //读取tag
        foreach ($this->taglibs as $tagi => $tagj) {
            L()->i("标签库载入完成");
            $tagname = $tagj . "Tags";
            import("core.view.tags.$tagname");
            $tagfilter = new $tagname();
            $c = $tagfilter->apply($c);
        }
        //转换成linux换行方式
        if (C("FORCE_UNINX_BR")) {
            $c = preg_replace("/\r\n/", "\n", $c);
        }
        
        if (!C("DEBUG")) {
            $c = preg_replace("/<!--(.*?)-->/", "", $c);
        }
        //存储编译后的到文件
        if (file_put_contents($cachefile, $c)) {
            include_once ($cachefile);
        } else {
            throw new FlowException("缓存文件创建失败" . $viewname);
        }
    
    }

    protected function getR() {
        return $this->resource;
    }
}