<?php
App::import("Lib", "Urg.AbstractWidgetHelper");
class PostBannerHelper extends AbstractWidgetHelper {
    var $helpers = array("Html", "Time");

    function build_widget() {
        $this->Html->css("/urg_post/css/urg_post.css", null, array("inline"=>false));
        $banners = $this->options["banners"];
        $widget = "";

        foreach ($banners as $banner) {
            foreach ($banner["AttachmentMetadatum"] as $meta) {
                if (strcmp($meta["key"], "post_id") == 0) {
                    $widget .= $this->Html->div("banner", $this->Html->image("/urg_post/img/" . 
                                                                             $meta["value"] . "/" . 
                                                                             $banner["Attachment"]["filename"]));
                    break;
                } else if (strcmp($meta["key"], "group_id") == 0) {
                    $widget .= $this->Html->div("banner", $this->Html->image("/urg/img/banners/" . 
                                                                             $meta["value"] . "/" . 
                                                                             $banner["Attachment"]["filename"]));
                    break;
                }
            }
        }

        return $widget;
    }
}
