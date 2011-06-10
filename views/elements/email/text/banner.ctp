<?php echo $this->HtmlText->convert_html_to_text($post["Post"]["content"]); ?>


<?php echo __("If you prefer not to receive anymore emails from us, go to the following link to unsubscribe: ", true) . "\n" .
    $this->Html->url("http://" . $_SERVER['HTTP_HOST'] . 
                     String::insert("/urg_subscription/subscriptions/unsubscribe/:email/:ref", 
                                    array("email" => $subscription["Subscription"]["email"], 
                                          "ref" => $subscription["Subscription"]["ref"]))); ?>


--
Sent by the Churchie Network
