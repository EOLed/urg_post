<?php
class AboutComponent extends Component {
    var $controller = null;
    var $settings = null;

    function initialize(&$controller, $settings = array()) {
        $this->controller =& $controller;
        $this->settings = $settings;

        CakeLog::write("debug", "about component settings: " . Debugger::exportVar($settings, 3));
    }

    function build($widget_id) {
        $settings = $this->settings[$widget_id];

        $options = array();

        $about = $this->get_about($settings["group_id"]);

        if (!isset($settings["title"])) {
            $settings["title"] = $about["Post"]["title"];
        }

        $this->controller->set("about_title_$widget_id", $settings["title"]);
        $this->controller->set("about_content_$widget_id", $about["Post"]["content"]);
    }

    function get_about($group_id) {
        $this->controller->loadModel("UrgPost.Post");
        $this->controller->Post->bindModel(array("belongsTo" => array("Group")));

        $about_group = $this->controller->Group->findById($group_id);

        $name = $about_group["Group"]["name"];

        $about = $this->controller->Post->find("first", 
                array("conditions" => 
                        array("OR" => array(
                                "Group.name" => "About", 
                                "Group.parent_id" => $group_id),
                              "AND" => array("I18n__title.content" => $name)
                        ),
                      "order" => "Post.publish_timestamp DESC"
                )
        );

        if ($about === false) {
            $this->controller->Post->bindModel(array("belongsTo" => array("Group")));

            $about = $this->controller->Post->find("first", 
                array("conditions" => 
                        array(
                            "AND" => array("I18n__title.content" => "About", "Group.name" => $name)
                        ),
                      "order" => "Post.publish_timestamp DESC"
                )
            );
        }

        CakeLog::write("debug", "about for group: $name " .  Debugger::exportVar($about, 3));

        return $about;
    }
}
