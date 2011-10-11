<?php
class UpcomingEventsHelper extends AppHelper {
    var $helpers = array("Html", "Time");
    var $widget_options = array("upcoming_events");

    function build($options = array()) {
        $this->Html->css("/urg_post/css/urg_post.css", null, array("inline"=>false));
        $title = $this->Html->tag("h2", __("Upcoming events", true));
        return $this->Html->div("upcoming-events", $title . 
                $this->upcoming_activity($options["upcoming_events"]));
    }

    function upcoming_activity($posts) {
        $upcoming_events = "";

        if (sizeof($posts) > 0) {
            foreach ($posts as $post) {
                $time = $this->Html->div("upcoming-timestamp",
                        $this->Time->format("F d, Y", $post["Post"]["publish_timestamp"]));
                $upcoming_events .= $this->Html->tag("li", $time . $post["Post"]["title"]);
            }
        } else {
            $upcoming_events = $this->Html->tag("li", __("No upcoming events.", true));
        }

        return $this->Html->tag("ul", $upcoming_events, array("id" => "upcoming-events"));
    }
}
