<?php
App::uses("Sanitize", "Utility");
App::uses("MarkdownHelper", "Markdown.View/Helper");
class RecentActivityHelper extends AppHelper {
    var $helpers = array("Html", "Time", "Session", "Markdown");
    var $options;

    function build($options = array()) {
        $this->options = $options;
        $this->Html->css("/urg_post/css/urg_post.css", null, array("inline"=>false));
        $this->Html->script("/urg_post/js/jquery.expander.min", array("inline" => false));
        $title = $this->Html->tag("h2", __($options["recent_activity_title"]));
        return $this->Html->div("recent-activity", 
                                $title . $this->add_post() . $this->post_feed($options["recent_activity"]));
    }

    function add_post() {
        $link = "";
        if ($this->options["can_add"]) {
            $link = $this->Html->link(__("Add a new post"), array("plugin" => "urg_post",
                                                                           "controller" => "posts",
                                                                           "action" => "add",
                                                                           $this->options["group_slug"]));
            $link = $this->Html->div("", 
                                     $this->_View->element("bootstrap_dropdown", 
                                                           array("label" => __("Action", true),
                                                                 "items" => array($link),
                                                                 "class" => "btn-mini btn-inverse")),
                                     array("class" => "action-dropdown", "escape" => false));
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
            $banner_attachment = $this->options["feed_banners"][$feed_item["Post"]["id"]][0];
            $link = array("plugin"=>"urg_post", 
                                  "action"=>"view", 
                                  "controller"=>"posts", 
                                  $feed_item["Post"]["id"],
                                  $feed_item["Post"]["slug"]);
            $banner = "";
            
            if ($this->options["show_thumbs"])
            {
                 $post_id = $banner_attachment["post_id"];
                 $filename = $banner_attachment["filename"];
                 $thumb = $this->Html->image("/urg_post/img/$post_id/$filename",
                                             array("class" => "activity-feed-thumbnail-image"));
                 $banner = $this->Html->div("activity-feed-thumbnail", 
                                            $this->Html->link($thumb,
                                                              $link, 
                                                              array("escape" => false)));
            }

            $title = $this->Html->tag("h3", $this->Html->link($feed_item["Post"]["title"], 
                                                              $link,
                                                              array("class"=>"post-title")));
            $home_group = $feed_item["Group"]["home"] ? $feed_item : array("Group" => $feed_item["Group"]["ParentGroup"]);
            CakeLog::write(LOG_DEBUG, "the home group: " . Debugger::exportVar($home_group, 3));

            $post_meta = "";

            if ($this->options["show_home_link"]) {
                $post_meta = $this->Html->link(__($home_group["Group"]["name"]), 
                                               array("plugin" => "urg",
                                                     "controller" => "groups",
                                                     "action" => "view",
                                                     $home_group["Group"]["slug"]),
                                               array("class" => "post-author"));
            } else {
                $post_meta = $this->Html->tag("span", 
                                              __($feed_item["User"]["username"]), 
                                              array("class" => "post-author"));
            }

            $post_meta = $this->Html->div("activity-feed-post-meta", $post_meta . " | " . $this->Time->format("F j, Y g:i a", $feed_item["Post"]["created"]));

            $pos = strpos($feed_item["Post"]["content"], ' ', min(strlen($feed_item["Post"]["content"]), 400));
            $content_snippet = Sanitize::html($pos === false ? $feed_item["Post"]["content"] : substr($feed_item["Post"]["content"], 0, $pos ) . "..."); 

            $post_content = $this->Html->div("activity-feed-post-content",
                                             $this->Markdown->html($content_snippet),
                                             array("id" => "activity-feed-post-content-" . $feed_item["Post"]["id"]));
            $feed .= $this->Html->div("activity-feed-post post ", $title . $post_meta . $banner . $post_content . $this->js($feed_item["Post"]["id"]));
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
                expandText: '" . __("See More") . "',
                expandPrefix: '<br/>...<br/>'
            });", array("inline" => true));
    }
}
