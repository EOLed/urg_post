<?php
class RecentActivityHelper extends AppHelper {
    var $helpers = array("Html", "Time", "Session");
    var $widget_options = array("recent_activity", "recent_activity_title");

    function build($options = array()) {
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
            $title = $this->Html->tag("h3", $this->Html->link($feed_item["Post"]["title"], 
                                      array("lang"=>$this->Session->read("Config.lang"),
                                            "plugin"=>"urg_post", 
                                            "action"=>"view", 
                                            "controller"=>"posts", 
                                            $feed_item["Post"]["id"],
                                            $feed_item["Post"]["slug"]), 
                                      array("class"=>"post-title")));
            $feed .= $this->Html->div("post", $title . $feed_item["Post"]["content"] . $time);
        }

        return $this->Html->div("", $feed, array("id" => "activity-feed"));
    }
}
