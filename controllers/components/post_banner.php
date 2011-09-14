<?php
App::import("Component", "ImgLib.ImgLib");
App::import("Lib", "Urg.AbstractWidgetComponent");
App::import("Component", "FlyLoader");
App::import("Component", "Urg.GroupBanner");
/**
 * The PostBanner widget can be used to add a banner of the specified group to views.
 *
 * Parameters: group_id        The id of the group whose banner you wish to display.
 */
class PostBannerComponent extends GroupBannerComponent {
    var $POST_BANNERS = "/app/plugins/urg_post/webroot/img";

    function build_widget() {
        $this->bindModels();
        $post_id = $this->widget_settings["post_id"];
        $this->setup_banners($post_id);

        $this->set("post_id", $post_id);
    }

    function get_post_image_path($attachment, $width, $height = 0) {
        $this->controller->FlyLoader->load("Component", "ImgLib.ImgLib");
        //TODO fix FlyLoader... should refer to it within component.
        $full_image_path = $this->controller->ImgLib->get_doc_root($this->POST_BANNERS) .  "/" .  $this->widget_settings["post_id"];
        $image = $this->controller->ImgLib->get_image("$full_image_path/" . $attachment["Attachment"]["filename"], 
                                          $width, 
                                          $height, 
                                          'landscape'); 
        return $image["filename"];
    }

    function set_post_banners($attachments) {
        Configure::load("config");
        foreach ($attachments as &$attachment) {
            $this->log("getting banner for " . $attachment["Attachment"]["filename"], LOG_DEBUG);
            $attachment["Attachment"]["filename"] = $this->get_post_image_path($attachment, Configure::read("Banner.defaultWidth"));
            CakeLog::write("debug", "Attachment filename: " . $attachment["Attachment"]["filename"]);
        }

        $this->set("banners", $attachments);

        CakeLog::write("debug", "attachments for Group Banner widget: " . Debugger::exportVar($attachments, 3));
    }

    function setup_banners($post_id) { 
        $this->controller->loadModel("Urg.AttachmentMetadatum");
        $this->controller->loadModel("Urg.Attachment");
        $meta = $this->controller->AttachmentMetadatum->find("all", array("conditions" => array("key" => "post_id",
                                                                                    "value" => $post_id)));
        CakeLog::write("debug", "meta found: " . Debugger::exportVar($meta, 3));
        $attachments = array();
        if (!empty($meta)) {
            $attachment_ids = array();

            foreach ($meta as $current) {
                array_push($attachment_ids, $current["AttachmentMetadatum"]["attachment_id"]);
            }

            $attachments = $this->controller->Attachment->findAllById($attachment_ids);
            $this->set_post_banners($attachments);
        } else {
            $attachments = $this->get_banners($this->widget_settings["group_id"]);
            $this->set_banners($attachments);
        }

        CakeLog::write("debug", "banners found: " . Debugger::exportVar($attachments, 3));

        return $attachments;
    }
}
