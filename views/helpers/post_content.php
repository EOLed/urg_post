<?php
App::import("Helper", "Markdown.Markdown");
class PostContentHelper extends AppHelper {
    var $helpers = array("Html", "Time", "Markdown");
    var $widget_options = array("post", "title");

    function build($options = array()) {
        CakeLog::write(LOG_DEBUG, "building Post Content widget");
        $this->Html->css("/urg_post/css/urg_post.css", null, array("inline"=>false));
        return $this->post_content($options["title"], $options["post"]);
    }

    function post_content($title, $post) {
        $content = "";

        if ($title === false) {
            $content = $this->Html->tag("h2", $title);
        }

        $content .= $this->Markdown->html($post["Post"]["content"]);
        return $this->Html->div("", $content, array("id" => "post-content"));
    }
}

