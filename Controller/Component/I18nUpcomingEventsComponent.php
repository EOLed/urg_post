<?php
App::uses("UpcomingEventsComponent", "UrgPost.Controller/Component");

class I18nUpcomingEventsComponent extends UpcomingEventsComponent {
    function build_widget() {
        $this->widget_settings = $this->widget_settings[$this->controller->Session->read("Config.language")];
        parent::build_widget();
    }
}

