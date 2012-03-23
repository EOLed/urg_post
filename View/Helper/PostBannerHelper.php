<?php
App::uses("AbstractWidgetHelper", "Urg.Lib");
class PostBannerHelper extends AbstractWidgetHelper {
    public $helpers = array("Html", "Time");

    function build_widget() {
        $this->Html->css("/urg_post/css/urg_post.css", null, array("inline"=>false));
        $banners = $this->options["banners"];
        $widget = "";

        foreach ($banners as $banner) {
            $widget .= $this->Html->div("banner", $this->Html->image("/urg_post/img/" . 
                                                                     $banner["Attachment"]["post_id"] . "/" . 
                                                                     $banner["Attachment"]["filename"]));
        }

        return $widget;
    }
}
