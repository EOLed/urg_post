<?php
App::uses("AbstractWidgetHelper", "Urg.Lib");
class PostTitleHelper extends AbstractWidgetHelper {
    var $helpers = array("Html", "Time");

    function build_widget() {
        CakeLog::write(LOG_DEBUG, "building article title  widget with options: " .
                                  Debugger::exportVar($this->options, 3));
        return $this->title_widget($this->options["title"], $this->options["post"]);
    }

    function title_widget() {
        $post = $this->options["post"];
        $title = $this->Html->div("", $post["Post"]["title"]);

        return $this->Html->div("span12", $this->Html->div("page-title", $title));
    }
}


