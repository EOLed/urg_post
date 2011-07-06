<?php
class PostContentComponent extends Object {
    var $controller = null;
    var $settings = null;

    function initialize(&$controller, $settings = array()) {
        $this->controller =& $controller;
        $this->settings = $settings;
    }

    function build($widget_id) {
        $settings = $this->settings[$widget_id];

        $post = $this->controller->Post->findById($settings["post_id"]);
        $this->controller->set("post_$widget_id", $post);
        $this->controller->set("title_$widget_id", 
                               isset($settings["title"]) ? __($settings["title"], true) : 
                                                           $post["Post"]["title"]);
    }
}
