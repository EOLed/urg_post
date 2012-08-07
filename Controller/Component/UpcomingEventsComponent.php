<?php
App::uses("GroupUtilComponent", "Urg.Controller/Component");
App::uses("AbstractWidgetComponent", "Urg.Lib");

/**
 * The Upcoming Events widget will add a list of upcoming events within a view.
 *
 * Parameters: group_id The group_id to retrieve upcoming posts for.
 */
class UpcomingEventsComponent extends AbstractWidgetComponent {
    var $components = array("Urg.GroupUtil");
    var $upcoming_group = false;

    function build_widget() {
        $this->upcoming_group = $this->get_upcoming_group();
        $upcoming = $this->get_upcoming_activity();
        $this->set("upcoming_events", $upcoming);
        $this->set("can_add", $this->can_add());
        $this->set("can_edit", $this->can_add());
        $this->set("can_delete", $this->can_delete());
        $this->set("upcoming_group", $this->upcoming_group);
    }

    function get_upcoming_group() {
        $group = isset($this->widget_settings["group_slug"]) ? $this->controller->Group->findBySlug($this->widget_settings["group_slug"]) :
                                                               $this->controller->Group->findById($this->widget_settings["group_id"]);

        if ($group["Group"]["name"] == "Schedule")
            return $group;

        $group = $this->GroupUtil->get_closest_home_group($group);
        CakeLog::write(LOG_DEBUG, "home group for upcoming events: " . Debugger::exportVar($group, 3));

        $children = $this->controller->Group->children($group["Group"]["id"]);
        $upcoming_group = false;

        foreach ($children as $child) {
            if ($child["Group"]["name"] == "Schedule") {
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

    function get_upcoming_activity() {
        $group_id = $this->upcoming_group["Group"]["id"];
        $children = $this->controller->Group->children($group_id);
        $child_ids = array($group_id);

        foreach ($children as $child) {
            array_push($child_ids, $child["Group"]["id"]);
        }

        Configure::load("config");
        $days_of_relevance = Configure::read("ActivityFeed.daysOfRelevance");
        $limit = isset($this->widget_settings["limit"]) ? $this->widget_settings["limit"] : Configure::read("ActivityFeed.limit");

        $this->controller->loadModel("UrgPost.Post");
        $posts = $this->controller->Post->find('all', 
                array("conditions" => array("Post.group_id" => $child_ids,
                                            "Post.publish_timestamp BETWEEN SYSDATE() AND SYSDATE() + INTERVAL $days_of_relevance DAY"),
                      "limit" => $limit,
                      "order" => "Post.publish_timestamp"));
        
        $this->log("upcoming posts: " . Debugger::exportVar($posts, 3), LOG_DEBUG);

        return $posts;
    }
}
