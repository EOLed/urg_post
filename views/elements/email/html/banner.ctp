<p style="font-size: small">
    <?php echo __("If you can't see this email correctly, ", true) .
            $this->Html->link(__("click here for the HTML version.", true),
            "http://" . $_SERVER['HTTP_HOST'] .
            $this->Html->url(String::insert("/urg_post/posts/view/:id/:slug", 
                                            array("id" => $post["Post"]["id"],
                                                  "slug" => $post["Post"]["slug"])))); ?>
</p>
<?php if (isset($banner)) { ?>
<p><?php echo $this->Html->image("http://" . $_SERVER['HTTP_HOST'] . $banner) ?></p>
<br/><br/>
<?php } ?>
<?php echo $post["Post"]["content"] ?>
<br/>
<p>
<?php $url = "http://" . $_SERVER['HTTP_HOST'] . 
             $this->Html->url(String::insert("/urg_subscription/subscriptions/unsubscribe/:ref", 
                                             array("ref" => $subscription["Subscription"]["ref"]))); ?>

<?php echo __("If you prefer not to receive anymore emails from us, go to the following link to unsubscribe: ", true) . "<br/>" .  $this->Html->link($url, $url, array("escape" => false)) ?>
</p>
<br/>
--<br/>
Sent by the Churchie Network
