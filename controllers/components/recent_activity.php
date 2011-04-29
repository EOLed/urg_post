<?php
class RecentActivityComponent extends Object {
    var $controller = null;
    var $settings = null;

    function initialize(&$controller, $settings = array()) {
        $this->controller =& $controller;
        $this->settings = $settings;
    }

    function build() {
        $activity = $this->get_recent_activity($this->settings["group_id"]);
        $this->controller->set("recent_activity", $activity);
        $this->controller->set("recent_activity_title", $this->settings["title"]);
    }

    function get_recent_activity($group_id) {
        $children = $this->controller->Group->children($group_id);
        CakeLog::write("debug", "child groups: " . Debugger::exportVar($children, 3));
        $child_ids = array($group_id);

        foreach ($children as $child) {
            array_push($child_ids, $child["Group"]["id"]);
        }

        $posts = $this->controller->Post->find('all', 
                array("conditions" => array("Post.group_id" => $child_ids,
                                            "Post.publish_timestamp < NOW()"),
                      "limit" => 10,
                      "order" => "Post.publish_timestamp DESC"));
        $activity = array();
        foreach ($posts as $post) {
            array_push($activity, $post);
        }
        
        CakeLog::write("debug", "group activity: " . Debugger::exportVar($activity, 3));

        return $activity;
    }
}
