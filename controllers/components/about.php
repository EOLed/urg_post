<?php
class AboutComponent extends Object {
    var $controller = null;
    var $settings = null;

    function initialize(&$controller, $settings = array()) {
        $this->controller =& $controller;
        $this->settings = $settings;

        CakeLog::write("debug", "about component settings: " . Debugger::exportVar($settings, 3));
    }

    function build($widget_id) {
        $settings = $this->settings[$widget_id];
        $about = $this->get_about($settings["name"]);
        $this->controller->set("about_title_$widget_id", $about["Post"]["title"]);
        $this->controller->set("about_content_$widget_id", $about["Post"]["content"]);
        //$this->controller->set("about_widget_options", array("about_title", "about_content"));
    }

    function get_about($name) {
        $this->controller->loadModel("Post");
        $this->controller->Post->bindModel(array("belongsTo" => array("Group")));

        $about_group = $this->controller->Group->findByName($name);

        $about = $this->controller->Post->find("first", 
                array("conditions" => 
                        array("OR" => array(
                                "Group.name" => "About", 
                                "Group.parent_id" => $about_group["Group"]["id"]),
                              "AND" => array("Post.title" => $name)
                        ),
                      "order" => "Post.publish_timestamp DESC"
                )
        );

        if ($about === false) {
            $this->controller->Post->bindModel(array("belongsTo" => array("Group")));

            $about = $this->controller->Post->find("first", 
                array("conditions" => 
                        array(
                            "AND" => array("Post.title" => "About", "Group.name" => $name)
                        ),
                      "order" => "Post.publish_timestamp DESC"
                )
            );
        }

        CakeLog::write("debug", "about for group: $name " .  Debugger::exportVar($about, 3));

        return $about;
    }
}
