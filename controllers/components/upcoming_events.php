<?php
App::import("Lib", "Urg.AbstractWidgetComponent");

/**
 * The Upcoming Events widget will add a list of upcoming events within a view.
 *
 * Parameters: group_id The group_id to retrieve upcoming posts for.
 */
class UpcomingEventsComponent extends AbstractWidgetComponent {
    function build_widget() {
        $upcoming = $this->get_upcoming_activity($this->widget_settings["group_id"]);
        $this->set("upcoming_events", $upcoming);
    }

    function get_upcoming_activity($group_id) {
        $children = $this->controller->Group->children($group_id);
        $child_ids = array($group_id);

        foreach ($children as $child) {
            array_push($child_ids, $child["Group"]["id"]);
        }

        $posts = $this->controller->Post->find('all', 
                array("conditions" => array("Post.group_id" => $child_ids,
                                            "Post.publish_timestamp > NOW()"),
                      "limit" => 10,
                      "order" => "Post.publish_timestamp"));
        
        $this->log("upcoming posts: " . Debugger::exportVar($posts, 3), LOG_DEBUG);

        return $posts;
    }
}
