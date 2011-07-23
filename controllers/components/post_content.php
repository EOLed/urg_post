<?php
App::import("Lib", "Urg.AbstractWidgetComponent");

/**
 * The Post Content widget will add the content of the specified post within a view.
 *
 * Parameters: post_id The id of the post whose content is to be outputted.
 *             title   The title of the widget. Defaults to the post's title.
 */
class PostContentComponent extends AbstractWidgetComponent {
    function build_widget() {
        $post = $this->controller->Post->findById($settings["post_id"]);
        $this->set("post", $post);
        $this->set("title", isset($settings["title"]) ? 
                            __($settings["title"], true) : $post["Post"]["title"]);
    }
}
