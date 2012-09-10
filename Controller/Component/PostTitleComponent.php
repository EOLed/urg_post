<?php
App::uses("AbstractWidgetComponent", "Urg.Lib");
class PostTitleComponent extends AbstractWidgetComponent {
    function build_widget() {
        $this->controller->loadModel("UrgPost.Post");
        $this->post = $this->controller->Post->findById($this->widget_settings["post_id"]);
        CakeLog::write("debug", "article for article title widget: " . Debugger::exportVar($this->post, 3));
        $this->set("post", $this->post);
        $this->set("title", isset($this->widget_settings["title"]) ? 
                            $this->widget_settings["title"] : $this->post["Post"]["title"]);
    }
}


