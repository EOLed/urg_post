<?php
class AboutHelper extends AppHelper {
    var $helpers = array("Html", "Time");
    var $widget_options = array("about_title", "about_content");

    function build($options = array()) {
        $this->Html->css("/urg_post/css/urg_post.css", null, array("inline"=>false));
        $title = $this->Html->tag("h2", $options["about_title"], array("class"=>"about-title"));
        $content = $this->Html->para("about-content", $options["about_content"]);

        return $this->Html->div("about", $title . $content);
    }
}
