<?php
App::uses("AbstractWidgetComponent", "Urg.Lib");

/**
 * The Upcoming Events widget will add a list of upcoming events within a view.
 *
 * Parameters: group_id The group_id to retrieve upcoming posts for.
 */
class UpcomingEventsComponent extends AbstractWidgetComponent {
    var $upcoming_group = false;
    function build_widget() {
        $upcoming = $this->get_upcoming_activity($this->widget_settings["group_id"]);
        $this->set("upcoming_events", $upcoming);

        $upcoming_group = $this->get_upcoming_group();

        $this->set("can_add", $this->can_add());
        $this->set("can_edit", $this->can_add());
        $this->set("can_delete", $this->can_delete());
        $this->set("upcoming_group", $this->get_upcoming_group());
    }

    function get_upcoming_group() {
        $children = $this->controller->Group->children($this->widget_settings["group_id"]);
        $upcoming_group = false;

        foreach ($children as $child) {
            if ($child["Group"]["name"] == "Upcoming Events") {
                $upcoming_group = $child;
                break;
            }
        }

        return $upcoming_group;
    }

    function can_add() {
        return $this->controller->Urg->has_access(array("plugin"=>"urg_post", 
                                                        "controller"=>"posts", 
                                                        "action"=>"add"), 
                                                  $this->upcoming_group["Group"]["id"]);
    }

    function can_edit() {
        return $this->controller->Urg->has_access(array("plugin"=>"urg_post", 
                                                        "controller"=>"posts", 
                                                        "action"=>"edit"), 
                                                  $this->upcoming_group["Group"]["id"]);
    }

    function can_delete() {
        return $this->controller->Urg->has_access(array("plugin"=>"urg_post", 
                                                        "controller"=>"posts", 
                                                        "action"=>"delete"), 
                                                  $this->upcoming_group["Group"]["id"]);
    }

    function get_upcoming_activity($group_id) {
        $children = $this->controller->Group->children($group_id);
        $child_ids = array($group_id);

        foreach ($children as $child) {
            array_push($child_ids, $child["Group"]["id"]);
        }

        Configure::load("config");
        $days_of_relevance = Configure::read("ActivityFeed.daysOfRelevance");
        $limit = isset($this->widget_settings["limit"]) ? $this->widget_settings["limit"] : Configure::read("ActivityFeed.limit");

        $posts = $this->controller->Post->find('all', 
                array("conditions" => array("Post.group_id" => $child_ids,
                                            "Post.publish_timestamp BETWEEN SYSDATE() AND SYSDATE() + INTERVAL $days_of_relevance DAY"),
                      "limit" => $limit,
                      "order" => "Post.publish_timestamp"));
        
        $this->log("upcoming posts: " . Debugger::exportVar($posts, 3), LOG_DEBUG);

        return $posts;
    }
}
