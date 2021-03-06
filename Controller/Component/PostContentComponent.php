<?php
App::uses("FlyLoaderComponent", "Controller/Component");
App::uses("AbstractWidgetComponent", "Urg.Lib");
App::uses("ImgLibComponent", "ImgLib.Controller/Component");

/**
 * The Post Content widget will add the content of the specified post within a view.
 *
 * Parameters: post_id The id of the post whose content is to be outputted.
 *             title   The title of the widget. Defaults to the post's title.
 */
class PostContentComponent extends AbstractWidgetComponent {
    var $POST_IMAGES = "/app/Plugin/UrgPost/webroot/img";
    var $post = null;

    function build_widget() {
        $this->controller->loadModel("UrgPost.Post");
        $this->controller->loadModel("UrgPost.AttachmentType");
        $images_type = $this->controller->AttachmentType->findByName("Images");
        $banner_type = $this->controller->AttachmentType->findByName("Banner");
        $audio_type = $this->controller->AttachmentType->findByName("Audio");
        $this->post = $this->controller->Post->findById($this->widget_settings["post_id"]);
        CakeLog::write("debug", "post for post content widget: " . Debugger::exportVar($this->post, 3));
        if (isset($this->post["Attachment"])) {
            $this->setup_images($this->post["Attachment"]);
        }

        if (isset($this->widget_settings["comments"]))
            $this->set("comments", $this->widget_settings["comments"]);

        $this->set("post", $this->post);
        $this->set("images_type", $images_type);
        $this->set("banner_type", $banner_type);
        $this->set("audio_type", $audio_type);
        $this->set("title", isset($this->widget_settings["title"]) ? 
                            $this->widget_settings["title"] : $this->post["Post"]["title"]);
        $this->set("id", isset($this->widget_settings["id"]) ?
                            $this->widget_settings["id"] : "post-content-" . $this->post["Post"]["id"]);
        $this->set("can_edit", $this->can_edit());
        $this->set("can_delete", $this->can_delete());
        $this->set("group_slug", $this->get_group_slug());
        $this->set("post_id", $this->widget_settings["post_id"]);

        if (isset($this->widget_settings["ustream"]))
            $this->set("ustream", $this->widget_settings["ustream"]);

        if (!isset($this->widget_settings["social"]))
            $this->widget_settings["social"] = false;

        $this->set("social", $this->widget_settings["social"]);

        if (!isset($this->widget_settings["attachments"]))
            $this->widget_settings["attachments"] = false;

        $this->set("list_attachments", $this->widget_settings["attachments"]);
    }

    function get_group_slug() {
        $group = $this->controller->Group->findById($this->post["Post"]["group_id"]);
        return $group["Group"]["slug"];
    }

    function can_edit() {
        return $this->controller->Urg->has_access(array("plugin"=>"urg_post", 
                                                        "controller"=>"posts", 
                                                        "action"=>"edit"), 
                                                  $this->post["Post"]["group_id"]);
    }

    function can_delete() {
        return $this->controller->Urg->has_access(array("plugin"=>"urg_post", 
                                                        "controller"=>"posts", 
                                                        "action"=>"delete"), 
                                                  $this->post["Post"]["group_id"]);
    }

    function setup_images(&$attachments) {
        Configure::load("config");
        foreach ($attachments as &$attachment) {
            $ext = pathinfo($attachment["filename"], PATHINFO_EXTENSION);
            if (strcmp($ext, "jpeg") != 0 && strcmp($ext, "jpg") != 0 && strcmp($ext, "png") != 0 && strcmp($ext, "gif") != 0 && strcmp($ext, "bmp") != 0) {
                continue;
            }
            $this->log("getting image for " . $attachment["filename"], LOG_DEBUG);
            $images = array("thumb" => $this->get_post_image_path($attachment, Configure::read("PostContentImage.defaultThumbWidth")), "view" => $this->get_post_image_path($attachment, Configure::read("PostContentImage.defaultWidth")));

            $attachment["filename"] = $images;
            CakeLog::write("debug", "Attachment filename: " . Debugger::exportVar($attachment["filename"], 2));
        }

        CakeLog::write("debug", "images for post content widget: " . Debugger::exportVar($attachments, 3));
    }

    function get_post_image_path($attachment, $width, $height = 0) {
        $this->controller->FlyLoader->load("Component", "ImgLib.ImgLib");
        //TODO fix FlyLoader... should refer to it within component.
        $img_dir = $this->controller->ImgLib->get_doc_root($this->POST_IMAGES) .  "/" . 
                $this->widget_settings["post_id"];
        $image = $this->controller->ImgLib->get_image("$img_dir/" . $attachment["filename"], 
                                                      $width, 
                                                      $height,
                                                      'landscape'); 
        return $image["filename"];
    }
}
