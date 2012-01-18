<?php
App::import("Helper", "Markdown.Markdown");
class RecentActivityHelper extends AppHelper {
    var $helpers = array("Html", "Time", "Session", "Markdown");
    var $options;

    function build($options = array()) {
        $this->options = $options;
        $this->Html->css("/urg_post/css/urg_post.css", null, array("inline"=>false));
        $this->Html->script("/urg_post/js/jquery.expander.min", array("inline" => false));
        $title = $this->Html->tag("h2", __($options["recent_activity_title"], true));
        return $this->Html->div("recent-activity", 
                                $title . $this->add_post() . $this->post_feed($options["recent_activity"]));
    }

    function add_post() {
        $link = "";
        if ($this->options["can_add"]) {
            $link = $this->Html->link(__("Add a new post...", true), array("plugin" => "urg_post",
                                                                           "controller" => "posts",
                                                                           "action" => "add",
                                                                           $this->options["group_slug"]));
        }
        return $link;
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
            $banner_attachment = $this->options["feed_banners"][$feed_item["Post"]["id"]][0];
            $banner = $this->options["show_thumbs"] ? $this->Html->image("/urg_post/img/" . $banner_attachment["post_id"]. "/" . $banner_attachment["filename"], array("class" => "activity-feed-thumbnail")) : "";
            $title = $this->Html->tag("h3", $this->Html->link($feed_item["Post"]["title"], 
                                      array( "plugin"=>"urg_post", 
                                            "action"=>"view", 
                                            "controller"=>"posts", 
                                            $feed_item["Post"]["id"],
                                            $feed_item["Post"]["slug"]), 
                                      array("class"=>"post-title")));
            $home_group = $feed_item["Group"]["home"] ? $feed_item : array("Group" => $feed_item["Group"]["ParentGroup"]);
            CakeLog::write(LOG_DEBUG, "the home group: " . Debugger::exportVar($home_group, 3));

            $home_link = "";

            if ($this->options["show_home_link"]) {
                $home_link = $this->Html->link(__($home_group["Group"]["name"], true), array("plugin" => "urg",
                                                                                             "controller" => "groups",
                                                                                             "action" => "view",
                                                                                             $home_group["Group"]["slug"]));
                $home_link = $this->Html->div("home-link", $home_link);
            }
            $post_content = $this->Html->div("activity-feed-post-content", 
                                             $this->Markdown->html($feed_item["Post"]["content"]),
                                             array("id" => "activity-feed-post-content-" . $feed_item["Post"]["id"]));
            $feed .= $banner . $this->Html->div("activity-feed-post post", $title . $home_link . $post_content . $this->js($feed_item["Post"]["id"]) . $time);
        }

        return $this->Html->div("", $feed, array("id" => "activity-feed"));
    }

    function js($post_id) {
        return $this->Html->scriptBlock("
            $('#activity-feed-post-content-$post_id').expander({
                slicePoint: 250,
                preserveWords: true,
                widow: 50,
                userCollapseText: '',
                expandText: '" . __("See More", true) . "',
                expandPrefix: '<br/>...<br/>'
            });", array("inline" => true));
    }
}
