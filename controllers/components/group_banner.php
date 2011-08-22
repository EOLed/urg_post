<?php
App::import("Component", "ImgLib.ImgLib");
App::import("Lib", "Urg.AbstractWidgetComponent");
App::import("Component", "FlyLoader");
/**
 * The GroupBanner widget can be used to add a banner of the specified group to views.
 *
 * Parameters: group_id        The id of the group whose banner you wish to display.
 */
class GroupBannerComponent extends AbstractWidgetComponent {
    var $IMAGES = "/app/plugins/urg_post/webroot/img";
    var $components = array("FlyLoader");

    function build_widget() {
        $this->bindModels();
        $group_id = $this->widget_settings["group_id"];
        $banners = $this->get_banners($group_id);

        while ($banners === false) {
            $parent = $this->controller->Group->getparentnode($group_id);

            if ($parent !== false) {
                $group_id = $parent["Group"]["id"];
                $banners = $this->get_banners($group_id);
            } else {
                break;
            }
        }

        $this->set_banners($banners);
    }

    function get_banners($group_id) {
        $banners = false;
        $config = $this->controller->Group->find("first", 
                array("conditions" => array("ParentGroup.id" => $group_id, 
                                            "I18n__name__" . $this->controller->Group->locale[0] . ".content" => __("Config", true))));

        CakeLog::write("debug", "getting config for group ($group_id): " . Debugger::exportVar($config, 3));

        if ($config !== false) {
            $banners = $this->controller->Post->find("first", 
                    array("conditions" => array("Post.group_id" => $config["Group"]["id"],
                                                "I18n__title.content" => __("Banners", true))));
        }

        return $banners;
    }
    function bindModels() {
        $this->controller->loadModel("Attachment");
        $this->controller->loadModel("UrgPost.Post");
    }

    function set_banners($post) {
        $attachments = $this->controller->Attachment->find("all", 
                array(  "conditions" => array("AND" => array(
                                "AttachmentType.name" => "Banner",
                                "Attachment.post_id" => $post["Post"]["id"])
                        ),
                        "joins" => array(   
                                array("table" => "attachment_types",
                                      "alias" => "AttachmentType",
                                      "type" => "LEFT",
                                      "conditions" => array(
                                          "AttachmentType.id = Attachment.attachment_type_id"
                                      )
                                )
                        )
               )
        );

        Configure::load("config");
        foreach ($attachments as &$attachment) {
            $this->log("getting banner for " . $attachment["Attachment"]["filename"], LOG_DEBUG);
            $attachment["Attachment"]["filename"] = $this->get_image_path($attachment, Configure::read("Banner.defaultWidth"));
            CakeLog::write("debug", "Attachment filename: " . $attachment["Attachment"]["filename"]);
        }

        $this->set("banners", $attachments);

        CakeLog::write("debug", "attachments for Group Banner widget: " . Debugger::exportVar($attachments, 3));
    }

    function get_image_path($attachment, $width, $height = 0) {
        $this->controller->FlyLoader->load("Component", "ImgLib.ImgLib");
        //TODO fix FlyLoader... should refer to it within component.
        $full_image_path = $this->controller->ImgLib->get_doc_root($this->IMAGES) .  "/" .  
                $attachment["Attachment"]["post_id"];
        $image = $this->controller->ImgLib->get_image("$full_image_path/" . $attachment["Attachment"]["filename"], 
                                          $width, 
                                          $height, 
                                          'landscape'); 
        return $image["filename"];
    }
}
