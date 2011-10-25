<?php
App::import("Lib", "Urg.AbstractWidgetComponent");

/**
 * The RecentActivityComponent widget displays a list of the most recent posts within a specific group.
 *
 * Parameters: group_id The group id of the recent posts to retrieve.
 *             title    The name of the widget (defaults to "Recent Activity")
 */
class RecentActivityComponent extends AbstractWidgetComponent {
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
