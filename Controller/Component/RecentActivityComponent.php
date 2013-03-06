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
    var $__newsletter_group = null;
    var $__group = null;

    function build_widget() {
        $this->__group = isset($this->widget_settings["group_slug"]) ? $this->controller->Group->findBySlug($this->widget_settings["group_slug"]) :
                                                                       $this->controller->Group->findById($this->widget_settings["group_id"]);
        $this->__newsletter_group = $this->get_newsletter_group();

        $post_id = false;
        if (isset($this->widget_settings["post_id"])) {
            $post_id = $this->widget_settings["post_id"];
        }
        
        $activity = $this->get_recent_activity($this->__group["Group"]["id"], $post_id);
        $this->set("recent_activity", $activity);

        if (!isset($this->widget_settings["title"])) {
            $this->widget_settings["title"] = "Recent Activity";
        }

        $this->set("recent_activity_title", $this->widget_settings["title"]);
        $this->set("show_thumbs", isset($this->widget_settings["show_thumbs"]) && 
                                  $this->widget_settings["show_thumbs"]);
        $this->set("show_home_link", isset($this->widget_settings["show_home_link"]) && 
                                     $this->widget_settings["show_home_link"]);
        $this->set("group_id", $this->__group["Group"]["id"]);
        $this->set("can_add", $this->can_add());

        $this->set("group_slug", $this->get_group_slug());
    }

    function get_group_slug() {
        return $this->__newsletter_group["Group"]["slug"];
    }

    function get_newsletter_group() {
        if ($this->__group["Group"]["name"] == "Newsletter")
            return $this->__group;

        $children = $this->controller->Group->children($this->__group["Group"]["id"]);
        $newsletter_group = false;

        foreach ($children as $child) {
            if ($child["Group"]["name"] == "Newsletter") {
                $newsletter_group = $child;
                break;
            }
        }

        return $newsletter_group;
    }

    function can_add() {
        return $this->controller->Urg->has_access(array("plugin"=>"urg_post", 
                                                        "controller"=>"posts", 
                                                        "action"=>"add"), 
                                                  $this->__newsletter_group["Group"]["id"]);
    }

    function get_recent_activity($group_id, $post_id = false) {
        $lang = $this->controller->Session->read("Config.language");
        $cached_feed_key = "recentactivity-$group_id-$post_id-$lang";
        $cached_banners_key = "recentactivity-banners-$group_id-$post_id-$lang";
        $cached_feed = Cache::read($cached_feed_key);
        $cached_banners = Cache::read($cached_banners_key);
        if ($cached_feed !== false) {
            CakeLog::write(LOG_DEBUG, "using cached recent activity for group $group_id and post $post_id");
            $this->set("feed_banners", $cached_banners);
            return $cached_feed;
        }

        CakeLog::write(LOG_DEBUG, "generating recent activity for group $group_id and post $post_id");

        $this->controller->loadModel("Urg.Group");
        $this->controller->loadModel("UrgPost.Post");

        $children = $this->controller->Group->children($group_id);
        CakeLog::write("debug", "child groups: " . Debugger::exportVar($children, 3));
        $child_ids = array($group_id);

        foreach ($children as $child) {
            if ($child["Group"]["name"] == __("Newsletter")) {
                array_push($child_ids, $child["Group"]["id"]);
            }
        }

        Configure::load("config");
        $days_of_relevance = Configure::read("ActivityFeed.daysOfRelevance");
        $limit = isset($this->widget_settings["limit"]) ? $this->widget_settings["limit"] : Configure::read("ActivityFeed.limit");
        
        $post_conditions = array("Post.group_id" => $child_ids,
                                 "Post.publish_timestamp BETWEEN SYSDATE() - INTERVAL $days_of_relevance DAY AND SYSDATE()");

        if ($post_id != false) {
            $post_conditions["Post.id !="] = $post_id;
        }
        
        $posts = $this->controller->Post->find('all', 
                array("conditions" => $post_conditions,
                      "limit" => $limit,
                      "order" => "Post.sticky DESC, Post.publish_timestamp DESC",
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
                            "conditions" => array("AND" => array("Widget.group_id" => $parent_group_id,
                                                                 "Widget.action" => "/urg/groups/view"),
                                                  "OR" => array("Widget.name" => "UrgPost.PostBanner",
                                                                "Widget.name" => "UrgPost.I18nPostBanner")),
                            "order" => "Widget.placement"
                    ));

                    CakeLog::write("debug", "recent activity banner widget: " . Debugger::exportVar($widget));

                    if ($widget) {
                        $settings = $this->controller->WidgetUtil->get_settings($widget, array());
                        if (isset($settings[$lang]))
                          $settings = $settings[$lang];

                        $post_id = $settings["post_id"];
                        $parent_post = $this->controller->Post->findById($post_id);
                        $post_banners = $this->get_banners($banner_type, $parent_post);
                    }

                    $parent = $this->controller->Group->getParentNode($parent_group_id);
                }
            }

            $banners[$post["Post"]["id"]] = $post_banners;
            
            array_push($activity, $post);
        }

        Cache::write($cached_banners_key, $banners);
        $this->set("feed_banners", $banners);
        
        CakeLog::write("debug", "group activity: " . Debugger::exportVar($activity, 3));
        Cache::write($cached_feed_key, $activity);

        return $activity;
    }

    function get_banners($banner_type, $post) {
        $post_banners = array();
        foreach ($post["Attachment"] as &$attachment) {
            $attachment["filename"] = $this->get_post_image_path($banner_type, $attachment, 370, 208);

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

    function build_placement($data) {
        return $data["RecentActivity"]["col"] . "|" . $data["RecentActivity"]["row"];
    }

    function build_options($data) {
        $options = array();
        $options["title"] = $data["RecentActivity"]["title"] == "" ? false : $data["RecentActivity"]["title"];
        $options["group_id"] = $data["RecentActivity"]["group"];
        foreach ($data["RecentActivity"]["flags"] as $flag) {
            $options[$flag] = true;
        }
        
        return $options;
    }

    function build_ui_options($controller) {
        $controller->loadModel("Urg.Group");
        $groups = $controller->Group->find("all", array("order" => "Group.name"));
        return array("groups" => $groups);
    }
}
