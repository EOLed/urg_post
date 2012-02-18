<?php
App::import("Lib", "Urg.AbstractWidgetComponent");
/**
 * The Doc Viewer widget can be used to add an inline Google Docs viewer.
 *
 * Parameters: post_id         The id of the post whose attachments need a doc viewer.
 *             title           The title of the doc viewer. Defaults to the post title.
 *             toggle_panel_id The dom id of the panel to toggle visibility between.
 */
class DocViewerComponent extends AbstractWidgetComponent {
    function build_widget() {
        $this->bindModels();
        $settings = $this->widget_settings;
        CakeLog::write("debug", "settings for doc viewer: " . Debugger::exportVar($settings, 3));
        $post = $this->controller->Post->findById($settings["post_id"]);
        $this->set("post", $post);
        $this->set("title", 
                   isset($settings["title"]) ? __($settings["title"]) : $post["Post"]["title"]);
        $this->set("toggle_panel_id", $settings["toggle_panel_id"]);
        $this->set_documents($post);
    }

    function bindModels() {
        $this->controller->loadModel("Attachment");
    }

    function set_documents($post) {
        $attachments = $this->controller->Attachment->find("list", 
                array(  "conditions" => array("AND" => array(
                                "AttachmentType.name" => "Documents",
                                "Attachment.post_id" => $post["Post"]["id"])
                        ),
                        "fields" => array("Attachment.filename", 
                                          "Attachment.id",
                                          "AttachmentType.name"),
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

        $this->set("documents", $attachments);

        CakeLog::write("debug", "attachments for Doc Viewer widget: " . 
                                Debugger::exportVar($attachments, 3));
    }
}

