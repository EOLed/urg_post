<?php
class DocViewerComponent extends Object {
    var $controller = null;
    var $settings = null;
    var $widget_id = null;

    function initialize(&$controller, $settings = array()) {
        $this->controller =& $controller;
        $this->settings = $settings;
    }

    function build($widget_id) {
        $this->bindModels();
        $settings = $this->settings[$widget_id];
        $this->widget_id = $widget_id;

        $post = $this->controller->Post->findById($settings["post_id"]);
        $this->controller->set("post_$widget_id", $post);
        $this->controller->set("title_$widget_id", 
                               isset($settings["title"]) ? __($settings["title"], true) : 
                                                           $post["Post"]["title"]);
        $this->controller->set("toggle_panel_id_$widget_id", $settings["toggle_panel_id"]);
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
                        "fields" => array(  "Attachment.filename", 
                                            "Attachment.id",
                                            "AttachmentType.name"
                                    ),
                        "joins" => array(   
                                array(  "table" => "attachment_types",
                                        "alias" => "AttachmentType",
                                        "type" => "LEFT",
                                        "conditions" => array(
                                            "AttachmentType.id = Attachment.attachment_type_id"
                                        )
                                )
                        )
               )
        );

        $this->controller->set("documents_" . $this->widget_id, $attachments);

        CakeLog::write("debug", "attachments for Doc Viewer widget: " . 
                                Debugger::exportVar($attachments, 3));
    }
}

