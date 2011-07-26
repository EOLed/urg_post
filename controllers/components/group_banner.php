<?php
App::import("Lib", "Urg.AbstractWidgetComponent");
/**
 * The GroupBanner widget can be used to add a banner of the specified group to views.
 *
 * Parameters: group_id        The id of the group whose banner you wish to display.
 */
class GroupBannerComponent extends AbstractWidgetComponent {
    function build_widget() {
        $this->bindModels();
        $config = $this->controller->Group->find("first", 
                array("conditions" => 
                        array("ParentGroup.id" => $this->widget_settings["group_id"],
                              "I18n__name.content" => __("Config", true)
                        )
                )
        );

        $banners = $this->controller->Post->find("first", array("conditions" => array("Post.group_id" => $config["Group"]["id"],
                                                                          "I18n__title.content" => __("Banners", true))));

        $this->set_banners($banners);
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

        $this->set("banners", $attachments);

        CakeLog::write("debug", "attachments for Group Banner widget: " . Debugger::exportVar($attachments, 3));
    }
}
