<?php
App::import("Component", "UrgPost.RecentActivity");

/**
 * The Post Content widget will add the content of the specified post within a view.
 *
 * Parameters: post_id The id of the post whose content is to be outputted.
 *             title   The title of the widget. Defaults to the post's title.
 */
class I18nRecentActivityComponent extends RecentActivityComponent {
    function build_widget() {
        $this->widget_settings = $this->widget_settings[$this->controller->Session->read("Config.language")];
        parent::build_widget();
    }
}

