<?php
App::import("Lib", "Urg.AbstractWidgetComponent");

/**
 * The RecentActivityComponent widget displays a list of the most recent posts within a specific group.
 *
 * Parameters: group_id The group id of the recent posts to retrieve.
 *             title    The name of the widget (defaults to "Recent Activity")
 */
class RecentActivityComponent extends AbstractWidgetComponent {
    function build_widget() {
        $activity = $this->get_recent_activity($this->widget_settings["group_id"]);
        $this->set("recent_activity", $activity);

        if (!isset($this->widget_settings["title"])) {
            $this->widget_settings["title"] = "Recent Activity";
        }
        $this->set("recent_activity_title", $this->widget_settings["title"]);
    }

    function get_recent_activity($group_id) {
        $this->controller->loadModel("Group");
        $this->controller->loadModel("Post");

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
