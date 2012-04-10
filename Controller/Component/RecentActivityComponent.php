<?php
App::uses("AbstractWidgetComponent", "Urg.Lib");
App::uses("UrgComponent", "Urg.Controller/Component");
/**
 * The RecentActivityComponent widget displays a list of the most recent posts within a specific group.
 *
 * Parameters: group_id The group id of the recent posts to retrieve.
 *             title    The name of the widget (defaults to "Recent Activity")
 */
class RecentActivityComponent extends AbstractWidgetComponent {
    var $components = array("Urg.Urg");
    var $POST_BANNERS = "/app/Plugin/UrgPost/webroot/img";

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
        $this->controller->loadModel("Urg.Group");
        $this->controller->loadModel("UrgPost.Post");

        $children = $this->controller->Group->children($group_id);
        CakeLog::write("debug", "child groups: " . Debugger::exportVar($children, 3));
        $child_ids = array($group_id);

        foreach ($children as $child) {
            if ($child["Group"]["name"] == __("News & Events")) {
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
            $post_banners = $this->get_banners($banner_type, $post);
            if (empty($post_banners)) {
                $parent = $this->controller->Group->getParentNode($post["Group"]["id"]);
                while ($parent && empty($post_banners)) {
                    CakeLog::write("debug", "parent group: " . Debugger::exportVar($parent, 3));
                    $parent_group_id = $parent["Group"]["id"];
                    $widget = $this->controller->Group->Widget->find("first", array(
                            "conditions" => array("Widget.group_id" => $parent_group_id,
                                                  "Widget.action" => "/urg/groups/view",
                                                  "Widget.name" => "UrgPost.PostBanner"),
                            "order" => "Widget.placement"
                    ));

                    if ($widget) {
                        $settings = $this->controller->WidgetUtil->get_settings($widget, array());
                        $post_id = $settings["Component"]["post_id"];
                        $parent_post = $this->controller->Post->findById($post_id);
                        $post_banners = $this->get_banners($banner_type, $parent_post);
                    }

                    $parent = $this->controller->Group->getParentNode($parent_group_id);
                }
            }

            $banners[$post["Post"]["id"]] = $post_banners;
            
            array_push($activity, $post);
        }

        $this->set("feed_banners", $banners);
        
        CakeLog::write("debug", "group activity: " . Debugger::exportVar($activity, 3));

        return $activity;
    }

    function get_banners($banner_type, $post) {
        $post_banners = array();
        foreach ($post["Attachment"] as &$attachment) {
            $attachment["filename"] = $this->get_post_image_path($banner_type, $attachment, 60, 60);

            if ($attachment["filename"]) {
                array_push($post_banners, $attachment);
            }
        }

        return $post_banners;
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
