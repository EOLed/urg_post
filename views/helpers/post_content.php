<?php
App::import("Helper", "Markdown.Markdown");
App::import("Lib", "Urg.AbstractWidgetHelper");
class PostContentHelper extends AbstractWidgetHelper {
    var $helpers = array("Html", "Time", "Markdown");
    var $widget_options = array("post", "title");

    function build_widget() {
        CakeLog::write(LOG_DEBUG, "building Post Content widget with options: " .
                                  Debugger::exportVar($this->options, 3));
        $this->Html->css("/urg_post/css/urg_post.css", null, array("inline"=>false));
        return $this->post_content($this->options["title"], $this->options["post"]);
    }

    function post_content($title, $post) {
        $content = "";

        if ($title !== false) {
            $content = $this->Html->tag("h2", $title);
        }

        $content .= $this->Markdown->html($post["Post"]["content"]);
        return $this->Html->div("", $content, array("id" => "post-content"));
    }
}
