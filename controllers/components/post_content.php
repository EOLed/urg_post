<?php
App::import("Component", "FlyLoader");
App::import("Lib", "Urg.AbstractWidgetComponent");
App::import("Component", "ImgLib.ImgLib");

/**
 * The Post Content widget will add the content of the specified post within a view.
 *
 * Parameters: post_id The id of the post whose content is to be outputted.
 *             title   The title of the widget. Defaults to the post's title.
 */
class PostContentComponent extends AbstractWidgetComponent {
    var $POST_IMAGES = "/app/plugins/urg_post/webroot/img";

    function build_widget() {
        $this->controller->loadModel("UrgPost.Post");
        $this->controller->loadModel("UrgPost.AttachmentType");
        $images_type = $this->controller->AttachmentType->findByName("Images");
        $post = $this->controller->Post->findById($this->widget_settings["post_id"]);
        CakeLog::write("debug", "post for post content widget: " . Debugger::exportVar($post, 3));
        $this->setup_images($post["Attachment"]);
        $this->set("post", $post);
        $this->set("images_type", $images_type);
        $this->set("title", isset($this->widget_settings["title"]) ? 
                            $this->widget_settings["title"] : $post["Post"]["title"]);
        $this->set("id", isset($this->widget_settings["id"]) ?
                            $this->widget_settings["id"] : "post-content");
    }

    function setup_images(&$attachments) {
        Configure::load("config");
        foreach ($attachments as &$attachment) {
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
