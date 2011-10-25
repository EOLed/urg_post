<?php
App::import("Helper", "Markdown.Markdown");
class RecentActivityHelper extends AppHelper {
    var $helpers = array("Html", "Time", "Session", "Markdown");
    var $options;

    function build($options = array()) {
        $this->options = $options;
        $this->Html->css("/urg_post/css/urg_post.css", null, array("inline"=>false));
        $title = $this->Html->tag("h2", __($options["recent_activity_title"], true));
        return $this->Html->div("recent-activity", $title . $this->post_feed($options["recent_activity"]));
    }

    function feed_icon($feed_item) {
        $icon = null;
        if (isset($feed_item["Post"])) {
           $icon = $this->Html->image("/urg_post/img/icons/feed/cloud.png",
                                      array("class" => "feed-icon")); 
        }
        return $icon; 
    }

    function post_feed($posts) {
        $feed = "";

        foreach ($posts as $feed_item) {
            $feed_icon = $this->feed_icon($feed_item);
            $time = $this->Html->div("feed-timestamp",
                    $feed_icon . 
                    $this->Time->timeAgoInWords($feed_item["Post"]["publish_timestamp"], 'j/n/y', false, true));
            $banner = $this->options["show_thumbs"] ? $this->Html->image("/urg_post/img/" . $feed_item["Post"]["id"] . "/" . $this->options["feed_banners"][$feed_item["Post"]["id"]][0], array("class" => "activity-feed-thumbnail")) : "";
            $title = $this->Html->tag("h3", $this->Html->link($feed_item["Post"]["title"], 
                                      array("lang"=>$this->Session->read("Config.lang"),
                                            "plugin"=>"urg_post", 
                                            "action"=>"view", 
                                            "controller"=>"posts", 
                                            $feed_item["Post"]["id"],
                                            $feed_item["Post"]["slug"]), 
                                      array("class"=>"post-title")));
            $feed .= $banner . $this->Html->div("activity-feed-post post", $title . $this->Markdown->html($feed_item["Post"]["content"]) . $time);
        }

        return $this->Html->div("", $feed, array("id" => "activity-feed"));
    }
}
