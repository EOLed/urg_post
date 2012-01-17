<?php
App::import("Lib", "Urg.AbstractWidgetComponent");
App::import("Component", "Urg.Urg");

/**
 * The RecentActivityComponent widget displays a list of the most recent posts within a specific group.
 *
 * Parameters: group_id The group id of the recent posts to retrieve.
 *             title    The name of the widget (defaults to "Recent Activity")
 */
class RecentActivityComponent extends AbstractWidgetComponent {
    var $components = array("Urg");
    var $POST_BANNERS = "/app/plugins/urg_post/webroot/img";

    function build_widget() {
        $activity = $this->get_recent_activity($this->widget_settings["group_id"]);
        $this->set("recent_activity", $activity);

        if (!isset($this->widget_settings["title"])) {
            $this->widget_settings["title"] = "Recent Activity";
        }
        $this->set("recent_activity_title", $this->widget_settings["title"]);
        $this->set("show_thumbs", isset($this->widget_settings["show_thumbs"]) && 
                                  $this->widget_settings["show_thumbs"]);
        $this->set("show_home_link", isset($this->widget_settings["show_home_link"]) && 
                                     $this->widget_settings["show_home_link"]);
        $this->set("group_id", $this->widget_settings["group_id"]);
        $this->set("can_add", $this->can_add());

        $this->set("group_slug", $this->get_group_slug());
    }

    function get_group_slug() {
        $group = $this->controller->Group->findById($this->widget_settings["group_id"]);
        return $group["Group"]["slug"];
    }

    function can_add() {
        return $this->controller->Urg->has_access(array("plugin"=>"urg_post", 
                                                        "controller"=>"posts", 
                                                        "action"=>"add"), 
                                                  $this->widget_settings["group_id"]);
    }

    function get_recent_activity($group_id) {
        $this->controller->loadModel("Group");
        $this->controller->loadModel("Post");

        $children = $this->controller->Group->children($group_id);
        CakeLog::write("debug", "child groups: " . Debugger::exportVar($children, 3));
        $child_ids = array($group_id);

        foreach ($children as $child) {
            if ($child["Group"]["name"] == __("News & Events", true)) {
                array_push($child_ids, $child["Group"]["id"]);
            }
        }

        Configure::load("config");
        $days_of_relevance = Configure::read("ActivityFeed.daysOfRelevance");
        $limit = isset($this->widget_settings["limit"]) ? $this->widget_settings["limit"] : Configure::read("ActivityFeed.limit");
        
        $posts = $this->controller->Post->find('all', 
                array("conditions" => array("Post.group_id" => $child_ids,
                                            "Post.publish_timestamp BETWEEN SYSDATE() - INTERVAL $days_of_relevance DAY AND SYSDATE()"),
                      "limit" => $limit,
                      "order" => "Post.publish_timestamp DESC",
                      "recursive" => 2));
        $activity = array();

        $this->controller->loadModel("UrgPost.AttachmentType");
        $banner_type = $this->controller->AttachmentType->findByName("Banner");
        $banners = array();
        foreach ($posts as $post) {
            $post_banners = array();
            foreach ($post["Attachment"] as &$attachment) {
                $attachment["filename"] = $this->get_post_image_path($banner_type, $attachment, 60, 60);

                if ($attachment["filename"]) {
                    array_push($post_banners, $attachment["filename"]);
                }
            }

            $banners[$post["Post"]["id"]] = $post_banners;
            
            array_push($activity, $post);
        }

        $this->set("feed_banners", $banners);
        
        CakeLog::write("debug", "group activity: " . Debugger::exportVar($activity, 3));

        return $activity;
    }

    function get_post_image_path($banner_type, $attachment, $width, $height = 0) {
        $path = false;
        if ($attachment["attachment_type_id"] == $banner_type["AttachmentType"]["id"]) {
            $this->controller->FlyLoader->load("Component", "ImgLib.ImgLib");
            //TODO fix FlyLoader... should refer to it within component.
            $banner_dir = $this->controller->ImgLib->get_doc_root($this->POST_BANNERS) .  "/" . 
                    $attachment["post_id"];
            $image = $this->controller->ImgLib->get_image("$banner_dir/" . $attachment["filename"], 
                                                          $width, 
                                                          $height, 
                                                          'crop'); 
            $path = $image["filename"];
        }
        return $path;
    }
}
