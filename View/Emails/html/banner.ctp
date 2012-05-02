<p style="font-size: small">
    <?php echo __("If you can't see this email correctly, ") .
            $this->Html->link(__("click here for the HTML version."),
            Router::url(array("plugin" => "urg_post", 
                              "controller" => "posts",
                              "action" => "view",
                              $post["Post"]["id"],
                              $post["Post"]["slug"]),
                        true)); ?>
</p>
<?php if (isset($banner)) { ?>
<p><?php echo $this->Html->image("http://" . $_SERVER['HTTP_HOST'] . $banner, array("style" => "width: 500px")) ?></p>
<br/><br/>
<?php } ?>
<?php echo $this->Markdown->html($post["Post"]["content"]); ?>
<br/>
<p>
<?php $url = Router::url(array("plugin" => "urg_subscription", 
                               "controller" => "subscriptions",
                               "action" => "unsubscribe",
                               $subscription["Subscription"]["ref"]),
                         true); ?>

<?php echo __("If you prefer not to receive anymore emails from us, go to the following link to unsubscribe: ") . "<br/>" .  $this->Html->link($url, $url, array("escape" => false)) ?>
</p>
<br/>
--<br/>
Sent by the Churchie Network
