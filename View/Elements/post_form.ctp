<div class="posts form">
    <div class="row">
        <div class="span12">
            <?php echo $this->Form->create('Post', array("class" => "form-horizontal")); ?>
                <div class="row">
                    <div class="span6">
                        <fieldset>
                            <legend><h2><?php echo __('Add Post'); ?></h2></legend>
                            <?php
                            echo $this->Form->hidden("Post.id");
                            echo $this->Form->hidden("bannerAttachmentIndex");
                            echo $this->TwitterBootstrap->input('Post.group_id', array("escape" => false));
                            echo $this->TwitterBootstrap->input('Post.title', array("class" => "span4"));
                            echo $this->Html->div("error-message", "", 
                                    array("id"=>"PostTitleError", "style"=>"display: none"));
                            echo $this->Html->div("validated", "✓", 
                                    array("id"=>"PostTitleValid", "style"=>"display: none"));
                            echo $this->Form->hidden("Post.formatted_date");
                            $time = $this->Form->text("Post.displayTime", array("div"=> false, 
                                                                                "label"=> false,
                                                                                "class" => "span2"));
                            $options = array("type" => "text", 
                                             "class" => "span2", 
                                             "label"=> __("Date"), 
                                             "after" => $time);
                            echo $this->TwitterBootstrap->input("Post.displayDate", $options);
                            echo $this->TwitterBootstrap->input("Post.content", array("class" => "span4",
                                                                                      "rows" => "15"));
                            $sticky_msg = __("Display this post at the top of activity feeds.");
                            echo $this->TwitterBootstrap->input("Post.sticky", 
                                                                array("help_inline" => $sticky_msg,
                                                                      "label" => false));
                            ?>
                        </fieldset>
                    </div>
                    <div class="span6">
                        <fieldset>
                            <legend><h2><?php echo __('Add Resources'); ?></h2></legend>
                            <?php 
                            echo $this->Html->div("input", 
                                    $this->Html->div("placeholder", "", array("id" => "post-banner")) . 
                                    $this->element("Cuploadify.uploadify", 
                                    array("plugin" => "Cuploadify", 
                                            "dom_id" => "image_upload", 
                                            "session_id" => CakeSession::id(),
                                            "include_scripts" => array("uploadify_css", "uploadify", "swfobject"),
                                            "options" => array("auto" => true, 
                                                    "folder" => "/" . $this->data["Post"]["id"],
                                                    "script" => $this->Html->url("/urg_post/posts/upload_image"),
                                                    "buttonText" => strtoupper(__("Add Banner")), 
                                                    //"multi" => true,
                                                    //"queueID" => "upload_queue",
                                                    "removeCompleted" => true,
                                                    "fileExt" => "*.jpg;*.jpeg;*.png;*.gif;*.bmp",
                                                    "fileDataName" => "imageFile",
                                                    "fileDesc" => "Image Files",
                                                    "onComplete" => "on_complete_images",
                                                    "onProgress" => "image_upload_in_progress",
                                                    "onAllComplete" => "image_uploads_completed"
                                                    )))); 
                            echo $this->Html->div("input", $this->element("Cuploadify.uploadify",
                                    array("plugin" => "Cuploadify", 
                                            "dom_id" => "attachment_upload", 
                                            "session_id" => CakeSession::id(),
                                            "options" => array("auto" => true, 
                                                    "folder" => "/" . $this->data["Post"]["id"],
                                                    "script" => $this->Html->url("/urg_post/posts/upload_attachments"),
                                                    "buttonText" => strtoupper(__("Attachments")), 
                                                    "removeCompleted" => true,
                                                    "fileExt" => "*.mp3;*.jpg;*.jpeg;*.png;*.gif;*.bmp;" .
                                                                 "*.ppt;*.pptx;*.doc;*.docx;*.pdf",
                                                    "fileDataName" => "attachmentFile",
                                                    "fileDesc" => "Post Attachments",
                                                    "multi" => true,
                                                    "onComplete" => "on_complete_attachments",
                                                    "onProgress" => "attachment_upload_in_progress",
                                                    "onAllComplete" => "attachment_uploads_completed"
                                                    ))));
                            if ($this->fetch("attachment_queue") == "") {
                                echo $this->start("attachment_queue");
                                echo $this->Html->div("", 
                                                      $this->Html->tag("ul", 
                                                                       "", 
                                                                       array("id"=>"attachment-queue")));
                                echo $this->end();
                            }
                            echo $this->fetch("attachment_queue");
                            ?>
                        </fieldset>
                    </div>
                </div>
                <div class="row form-actions">
                    <div class="span12">
                        <?php
                            echo $this->Form->button(__("Publish", true), array("class" => "btn btn-primary")) . " ";
                            echo $this->Form->button(__("Reset", true), array("type" => "reset", "class" => "btn"));
                        ?>
                    </div>
                </div>
                <?php 
                    echo $this->Html->div("", $this->Html->image("/urg_post/img/loading.gif"), 
                            array("id" => "loading-validate", "style" => "display: none")); 
                ?>
                <div style="display: none;" id="in-progress" title="<?php echo __("Uploads pending..."); ?>">
                    <p>
                        <?php echo __("The post form will be submitted after all attachments have been uploaded."); ?>
                    </p>
                </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>    

