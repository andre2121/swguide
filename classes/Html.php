<?php
    class Html{

        private $root;
        private $params;
        private $title;
        private $tpage;
        private $curNav;

        public function __construct($root, $params, $curNav="Home", $tpage="")
        {
            $this->root = $root;
            $this->params = $params;
            $this->curNav = $curNav;
            $this->tpage = $tpage;
            $this->title = $this->setTitle();
            $this->getHeader();
        }

        private function setTitle(){
            return $this->params['app_name']." - ".($this->tpage!="" ? ucfirst($this->tpage).($this->tpage=="info" ? " about " : " of ") : "").$this->curNav;

        }

        private function getHeader(){
            $navLinks = [
                'Home' => 'index.php',
                'People' => 'info.php?info=list-people-1',
                'Planets' => 'info.php?info=list-planets-1',
                'Films' => 'info.php?info=list-films-1',
                'Species' => 'info.php?info=list-species-1',
                'Vehicles' => 'info.php?info=list-vehicles-1',
                'Starships' => 'info.php?info=list-starships-1',
                'About' => 'about.php',
                'Contact' => 'contact.php'
            ];

            echo "<!DOCTYPE html>";
            echo "<html lang=\"en\">";
            echo "<head>";
            echo "<meta charset=\"UTF-8\">";
            echo $this->getCss();
            echo $this->getJs();
            echo "<title>".$this->title."</title>";
            echo "</head>";
            echo "<body>";
            echo "<div id=\"wrapper\">";
            echo "<div id=\"container\">";
            echo "<div id=\"header\">";
            echo "<div class=\"header-logo\">";
            echo "<h1><a href=\"index.php\">Star Wars Guide</a></h1>";
            echo "<p>This is a guide in all SEVEN Star Wars films</p>";
            echo "</div>";
            echo "<div id=\"navigation\">";
            echo "<ul class=\"pg\">";
            foreach ($navLinks as $key => $value) {
                echo "<li class=\"".($key == $this->curNav ? "current_page_item" : "page_item")."\"><a href=\"".$value."\" title=\"".$key."\">".$key."</a></li>";
            }
            echo "</ul>";
            echo "</div>";
            echo "</div>";
            echo "<div id=\"content\">";
            echo "<div id=\"left-content\">";
        }

        public function getFooter(){
            echo "</div>";
            echo "</div>";
            echo "<div id=\"footer\">";
            echo "<div id=\"footer-cp\">";
            echo "<div class=\"dv-feet\">";
            echo "&copy;2017<a href=\"contact.php\"> Suvorov Andrii</a><br />";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            echo "</body>";
            echo "</html>";
        }

        public function builderTable($swarray){
            echo "<h2>".$swarray['name']."</h2>";
            // Search Form
            echo "<div class=\"searchblock\">";
            echo "<form method=\"POST\" id=\"searchform\" action=\"info.php\">";
            echo "<p><input name=\"search\" type=\"text\" class=\"textf\" placeholder=\"".$swarray['search']."\">";
            echo "<input name=\"type\" type=\"hidden\" value=\"".lcfirst($this->curNav)."\">";
            echo "<input type=\"submit\" name=\"searchbut\" value=\"Search\" class=\"textb\"/></p>";
            echo "</form>";
            echo "</div>";

            if (isset($swarray['swtext']) && strlen(trim($swarray['swtext']))>0) {
                echo "<p>".$swarray['swtext']."</p>";
            }

            echo "<table>";
            foreach ($swarray['swdate'] as $parvalue) {
                echo "<tr>";
                echo "<td><strong>".$parvalue['title']."</strong></td>";
                echo "<td".($parvalue['comment'] != "" ? " title=\"".$parvalue['comment']."\"" : "").">";
                if (count($parvalue['listvalue'])>0){ // Don't empty array of value
                    # Field may have multiple values and links
                    $type = $parvalue['type'];
                    if ($type=="") {  // No links
                            echo implode(", ", $parvalue['listvalue']);
                    }else{ // with links
                        $curvalue=0;
                        foreach ($parvalue['listvalue'] as $key => $value) {
                            if ($curvalue>0) echo ", ";
                            echo Html::swlink($value, "info", $type, $key);
                            $curvalue++;
                        }
                    }
                }
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
            # page navigation
            $type = strtolower($swarray['name']);
            if ($this->tpage=="list") {                
                list($curpage, $allpage) = explode("/", $swarray['curpage']);
                echo "<div id=\"post-navigator\">";
                echo "<div class=\"wp-pagenavi\">";
                echo "<span class=\"pages\">Page ".$curpage." of ".$allpage."</span>";
                echo $curpage>1 ? Html::swlink("&laquo;", $this->tpage, $type, $curpage-1) : "";
                for ($i=1; $i < $allpage+1; $i++) {
                    echo $curpage==$i ? "<span class=\"current\">".$i."</span>" : Html::swlink($i, $this->tpage, $type, $i);
                }
                echo $curpage>1 ? Html::swlink("&raquo;", $this->tpage, $type, $curpage-1) : "";
                echo "</div>";
                echo "</div>";
            }
        }

        private function getCss(){
            $css = "";
            foreach($this->params['css'] as $link){
                $css .= "<link rel='stylesheet' href='".$link."'>";
            }
            return $css;
        }

        private function getJs(){
            $js = "";
            foreach($this->params['js'] as $link){
                $js .= "<script src='".$link."'></script>";
            }
            return $js;
        }

        public static function link($title, $href){
            return $link = "<a href='".$href."'>".$title."</a>";
        }

        public static function swlink($title, $tpage, $type, $id){
            return $href = Html::link($title,"info.php?info=".$tpage."-".$type."-".$id);
        }


    }
?>