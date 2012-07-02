<?php echo $this->HtmlText->convert_html_to_text($this->Markdown->html($post["Post"]["content"])); ?>


<?php echo __("The HTML version of this email can be found at ") .
        Router::url(array("plugin" => "urg_post", 
                          "controller" => "posts",
                          "action" => "view",
                          $post["Post"]["id"],
                          $post["Post"]["slug"]),
                    true); ?>


<?php echo __("If you prefer not to receive anymore emails from us, go to the following link to unsubscribe: ") . "\n" .
        Router::url(array("plugin" => "urg_subscription", 
                          "controller" => "subscriptions",
                          "action" => "unsubscribe",
                          $subscription["Subscription"]["ref"]),
                    true); ?>


--
Sent by the Churchie Network
