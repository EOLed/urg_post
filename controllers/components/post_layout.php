<?php
class PostLayoutComponent extends Object {
    var $controller = null;
    var $settings = null;
    var $columns = array();

    function initialize(&$controller, $settings = array()) {
        $this->controller =& $controller;
        $this->settings = $settings;
    }

    function build($widget_id) {
        $settings = $this->settings[$widget_id];

        $options = array();

        for ($i = 0; $i < $settings["columns"]; $i++) {
            $columns["post-col-$i"] = $settings["col-$i"];
        }

        $this->controller->set("columns_$widget_id", $columns);
    }
}
