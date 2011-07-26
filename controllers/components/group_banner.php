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
        $group_id = $this->widget_settings["group_id"];
        $banners = $this->get_banners($group_id);

        while ($banners === false) {
            $parent = $this->controller->Group->getparentnode($group_id);
            CakeLog::write("debug", "parent of group ($group_id): " . Debugger::exportVar($parent, 3));

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
                                            "I18n__name.content" => __("Config", true))));

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

        $this->set("banners", $attachments);

        CakeLog::write("debug", "attachments for Group Banner widget: " . Debugger::exportVar($attachments, 3));
    }
}
