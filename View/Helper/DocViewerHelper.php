<?php
App::uses("AbstractWidgetHelper", "Urg.Lib");
class DocViewerHelper extends AbstractWidgetHelper {
    var $helpers = array("Html", "Time");
    var $toggle_panel_id = null;

    function build_widget() {
        $this->Html->css("/urg_post/css/urg_post.css", null, array("inline"=>false));
        $this->toggle_panel_id = $this->options["toggle_panel_id"];
        return $this->doc_viewer($this->options["documents"]);
    }

    function doc_viewer($documents) {
        $doc_viewer = "";
        if (isset($documents["Documents"])) {
            $doc_viewer = $this->Html->tag("iframe", 
                                           "",
                                           array("class" => "shadow doc-viewer", 
                                                 "id" => "doc-viewer"));
            $doc_viewer .= $this->Html->link($this->Html->image("/urg_post/img/icons/x.png",
                                                                array("style" => "height: 32px")), 
                                             "#",
                                             array("id" => "close-doc",
                                                   "escape" => false));
        }

        return $this->Html->div("", $doc_viewer, array("id" => "post-docs",
                                                       "style" => "display: none")) . $this->Html->scriptBlock($this->js());
    }

    function js() {
        $js = 
        '$(".gdoc").click(function() {
            $("#doc-viewer").attr("src", 
                                  "http://docs.google.com/gview?embedded=true&url=http://' . $_SERVER['SERVER_NAME'] . '" 
                                  + $(this).attr("href"));';

        if ($this->toggle_panel_id != null) {
            $js .= "$('{$this->toggle_panel_id}').hide();";
        }

        $js .= '$("#post-docs").show("fade");
                return false;
        });

        $("#close-doc").click(function() {
            $("#post-docs").hide();';
            if ($this->toggle_panel_id != null) {
                $js .= "$('{$this->toggle_panel_id}').show('slide');";
            }
            $js .= 'return false;
        });';
        return $js;
    }
}
