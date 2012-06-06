<?php foreach ($comments as $comment) { ?>
<div id="post-comment-<?php echo $comment["PostComment"]["id"] ?>" class="post-comment">
    <div class="post-commenter">
    <?php 
        $username = "";
        if ($comment["User"]["username"] == null) {
            $username = $comment["PostComment"]["username"]; 
            if ($comment["PostComment"]["link"] != null) {
                $username = $this->Html->link($comment["PostComment"]["username"], 
                                              $comment["PostComment"]["link"],
                                              array("rel" => "nofollow"));
            }
        } else {
            $username = $comment["User"]["username"];
        }
        echo "<strong>$username</strong> " . __("says:");
    ?>
    </div>
    <div class="post-comment-body">
        <?php echo $this->Markdown->html(Sanitize::html($comment["PostComment"]["comment"])); ?>
    </div>
    <div class="post-comment-meta">
        <?php echo $this->Time->timeAgoInWords($comment["PostComment"]["created"]); ?>
    </div>
</div>
<?php } ?>
