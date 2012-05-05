<?php
App::uses("ImgLibComponent", "ImgLib.Controller/Component");
App::uses("AbstractWidgetComponent", "Urg.Lib");
App::uses("FlyLoaderComponent", "Controller/Component");
App::uses("GroupBannerComponent", "Urg.Controller/Component");
/**
 * The PostBanner widget can be used to add a banner of the specified group to views.
 *
 * Parameters: group_id        The id of the group whose banner you wish to display.
 */
class PostBannerComponent extends GroupBannerComponent {
    var $POST_BANNERS = "/app/Plugin/UrgPost/webroot/img";

    function build_widget() {
        $this->bindModels();
        $post_id = $this->widget_settings["post_id"];
        $attachments = $this->setup_banners($post_id);
        $parent_group_id = null;

        while (!$attachments) {
            CakeLog::write("debug", "getting post: $post_id");
            $post = $this->controller->Post->findById($post_id);
            $parent = $this->controller->Group->getParentNode($parent_group_id == null ? $post["Group"]["id"] : $parent_group_id);

            CakeLog::write("debug", "parent group: " . Debugger::exportVar($parent, 3));
            // get the banner widget of the parent group
            if ($parent) {
                $parent_group_id = $parent["Group"]["id"];
                CakeLog::write("debug", "new post: " . $post_id);
                CakeLog::write("debug", "getting widget for $parent_group_id");

                $widget = $this->controller->Group->Widget->find("first", array(
                        "conditions" => array("Widget.group_id" => $parent_group_id,
                                              "Widget.action" => "/urg/groups/view",
                                              "Widget.name" => "UrgPost.PostBanner"),
                        "order" => "Widget.placement"
                ));


                if ($widget) {
                    CakeLog::write("debug", "fallback widget: " . Debugger::exportVar($widget, 3));
                    $this->widget_settings = $this->controller->WidgetUtil->get_settings($widget, array("post_id", $post["Post"]["id"]));

                    $post_id = $this->widget_settings["post_id"];
                   
                    CakeLog::write("debug", "using widget settings: " . Debugger::exportVar($this->widget_settings, 3));
                    $attachments = $this->setup_banners($post_id);
                }
            } else {
                break;
            }
        }

        CakeLog::write("debug", "widget settings used: " . Debugger::exportVar($this->widget_settings, 3));

        $this->set("post_id", $this->widget_settings["post_id"]);
    }

    function get_post_image_path($attachment, $width, $height = 0) {
        $this->controller->FlyLoader->load("Component", "ImgLib.ImgLib");
        //TODO fix FlyLoader... should refer to it within component.
        $banner_dir = $this->controller->ImgLib->get_doc_root($this->POST_BANNERS) .  "/" . 
                $this->widget_settings["post_id"];
        $image = $this->controller->ImgLib->get_image("$banner_dir/" . $attachment["Attachment"]["filename"], 
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
        $this->controller->loadModel("UrgPost.AttachmentType");
        $banner_type = $this->controller->AttachmentType->findByName("Banner");
        $attachments = $this->controller->Attachment->find("all", 
                array("conditions"=>array("Attachment.post_id" => $post_id,
                                          "Attachment.attachment_type_id" => $banner_type["AttachmentType"]["id"])));
        $this->set_post_banners($attachments);

        CakeLog::write("debug", "banners found: " . Debugger::exportVar($attachments, 3));

        return $attachments;
    }
}
