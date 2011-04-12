<?php
class RecentActivityHelper extends AppHelper {
    var $helpers = array("Html", "Time");
    var $widget_options = array("recent_activity");

    function build($options = array()) {
        $title = $this->Html->tag("h2", __("Recent activity", true));
        $content = "";

        foreach ($options["recent_activity"] as $recent_activity) {
            $content .= $this->Html->tag("h3", $recent_activity["Post"]["title"]);
            $content .= $this->Html->para("post-content", $recent_activity["Post"]["content"]);
        }
        return $this->Html->div("about", $title . $this->post_feed($options["recent_activity"]));
    }

    function feed_icon($feed_item) {
        $icon = null;
        if (isset($feed_item["Post"])) {
           $icon = $this->Html->image("/urg/img/icons/feed/cloud-alt.png",
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
                                      "/urg_post/posts/view/" . $feed_item["Post"]["id"] . "/" . 
                                      $feed_item["Post"]["slug"]), array("class"=>"post-title"));
            $feed .= $this->Html->div("post", $title . $feed_item["Post"]["content"] . $time);
        }

        return $this->Html->div("", $feed, array("id" => "activity-feed"));
    }
}
