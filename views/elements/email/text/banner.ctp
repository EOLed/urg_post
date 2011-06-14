<?php echo $this->HtmlText->convert_html_to_text($post["Post"]["content"]); ?>


<?php echo __("The HTML version of this email can be found at ", true) .
        "http://" . $_SERVER['HTTP_HOST'] .
        $this->Html->url(String::insert("/urg_post/posts/view/:id/:slug", 
                                        array("id" => $post["Post"]["id"],
                                              "slug" => $post["Post"]["slug"]))); ?>


<?php echo __("If you prefer not to receive anymore emails from us, go to the following link to unsubscribe: ", true) . "\n" .
    "http://" . $_SERVER['HTTP_HOST'] . 
             $this->Html->url(String::insert("/urg_subscription/subscriptions/unsubscribe/:ref", 
                              array("ref" => $subscription["Subscription"]["ref"]))); ?>


--
Sent by the Churchie Network
