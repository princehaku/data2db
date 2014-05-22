<?php

/**分页类
 * 
 * @author princehaku
 *
 */
class Pager {

    private $urlpagepre;

    private $parameter;

    private $totalpage;

    private $totalrows;

    private $perpage;

    private $nowpage;

    public function __construct($nowpage = 1, $totalrows, $perpage, $parameter = '', $urlpagepre = 'p') {
        if ($nowpage < 1)
            $nowpage = 1;
        $this->nowpage = $nowpage;
        $this->totalrows = $totalrows;
        $this->perpage = $perpage;
        $this->totalpage = (int)($totalrows / $perpage + 1);
        if (($this->totalpage > 1) && ($totalrows % $perpage == 0)) {
            $this->totalpage = (int)($totalrows / $perpage);
        }
        $this->urlpagepre = $urlpagepre;
        $this->parameter = $parameter;
    }

    public function getLimitString() {
        $st = ($this->nowpage - 1) * $this->perpage;
        return " limit $st," . $this->perpage;
    }

    public function getPage() {
        $content = "<div class='pager'>";
        $pageposa = $this->nowpage - 3;
        $pageposb = $this->nowpage + 3;
        if ($pageposa < 1)
            $pageposa = 1;
        if ($pageposb > $this->totalpage)
            $pageposb = $this->totalpage;
        $content .= $this->buildLink($this->urlpagepre, "s");
        for($i = $pageposa; $i <= $pageposb; $i++) {
            $content .= $this->buildLink($this->urlpagepre, $i);
        }
        $content .= $this->buildLink($this->urlpagepre, "e");
        $content .= "</div>";
        return $content;
    }

    private function buildLink($page, $mark) {
        
        if ($mark == 's') {
            $bar = "<a href='?$page=1&$this->parameter'>首页</a>";
        } else if ($mark == 'e') {
            $bar = "<a href='?$page=" . $this->totalpage . "&$this->parameter'>末页</a>";
        } else {
            $bar = "<a href='?$page=$mark&$this->parameter'>$mark</a>";
        }
        if ($mark == $this->nowpage) {
            $bar = "[" . $bar . "]";
        }
        $bar = "<span>" . $bar . "</span>";
        return $bar;
    }

}

?>