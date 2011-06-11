<p><?php echo $this->Html->image("http://" . $_SERVER['HTTP_HOST'] . $banner) ?></p>
<br/><br/>
<?php echo $post["Post"]["content"] ?>
<br/>
<p>
<?php $url = $this->Html->url("http://" . $_SERVER['HTTP_HOST'] . 
                     String::insert("/urg_subscription/subscriptions/unsubscribe/:ref", 
                                    array("ref" => $subscription["Subscription"]["ref"]))); ?>

<?php echo __("If you prefer not to receive anymore emails from us, go to the following link to unsubscribe: ", true) . "<br/>" .  $this->Html->link($url, $url, array("escape" => false)) ?>
</p>
<br/>
--<br/>
Sent by the Churchie Network
