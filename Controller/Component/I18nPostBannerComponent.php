<?php
App::uses("PostBannerComponent", "UrgPost.Controller/Component");

class I18nPostBannerComponent extends PostBannerComponent {
    function build_widget() {
        $this->widget_settings = $this->widget_settings[$this->controller->Session->read("Config.language")];
        parent::build_widget();
    }
}

