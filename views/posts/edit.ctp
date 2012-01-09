<?php
/**
 * Post upload form.
 * This form is responsible for uplaoding posts onto an Urg system.
 *
 * @author Amos Chan <amos.chan@chapps.org>
 * @since v 1.0
 */
?>
<?php echo $this->Html->scriptStart(); ?>
    var image_in_progress = false;
    var attachment_in_progress = false;
    var submit_form = false;
    function on_complete_images(event, ID, fileObj, response, data) {
        if ($("#PostBannerAttachmentIndex").val() == "") {
            $("#PostBannerAttachmentIndex").val($("input.attachment").size());
        }

        bannerIndex = $("#PostBannerAttachmentIndex").val();

        if ($("#Attachment" + bannerIndex + "Filename").length == 0) {
            $('<input>').attr({ 
                    type: 'hidden', 
                    id: 'Attachment' + bannerIndex + 'Filename', 
                    name: 'data[Attachment][' + bannerIndex + '][filename]' ,
                    value: fileObj.name,
                    class: "attachment"
            }).appendTo('form');
            $('<input>').attr({ 
                    type: 'hidden', 
                    id: 'Attachment' + bannerIndex + 'AttachmentTypeId', 
                    name: 'data[Attachment][' + bannerIndex + '][attachment_type_id]' ,
                    value: <?php echo $banner_type["AttachmentType"]["id"]; ?>,
            }).appendTo('form');
        }

        banner_width = $("#post-banner").width();

        $("#post-banner").html(
                "<img id='#post-banner-img' src='" +
                "<?php echo $this->Html->url("/urg_post/img/" . $this->data["Post"]["id"]); ?>" 
                + "/" + fileObj.name + "#" + Math.random() + "' style='width: " + banner_width +  "px;' />");
    }

    function on_complete_attachments(event, ID, fileObj, response, data) {
        attachmentCounter = $("input.attachment").size();
        response = jQuery.parseJSON(response);
        $('<input>').attr({ type: 'hidden', 
                id: 'Attachment' + attachmentCounter + 'Filename', 
                name: 'data[Attachment][' + attachmentCounter + '][filename]' ,
                value: fileObj.name,
                class: "attachment"
        }).appendTo('form');
        $('<input>').attr({ 
                type: 'hidden', 
                id: 'Attachment' + attachmentCounter + 'AttachmentTypeId', 
                name: 'data[Attachment][' + attachmentCounter + '][attachment_type_id]' ,
                value: response.attachment_type_id,
        }).appendTo('form');

        $("<li>").attr({ id: "AttachmentQueueListItem" + attachmentCounter })
                .appendTo("#attachment-queue");

        $("<a>").attr({
                href: "<?php echo $this->Html->url("/urg_post/") ?>" + response.webroot_folder + 
                        "/<?php echo $this->data["Post"]["id"] ?>/" + fileObj.name,
                id: "AttachmentQueueAudioLink" + attachmentCounter ,
                target: "_blank"
        }).appendTo("#AttachmentQueueListItem" + attachmentCounter);

        $("#AttachmentQueueAudioLink" + attachmentCounter).text(fileObj.name.substring(0, 40));
    }

    function image_upload_in_progress(event, ID, fileObj, data) {
        image_in_progress = true;
    }

    function attachment_upload_in_progress(event, ID, fileObj, data) {
        attachment_in_progress = true;
    }
<?php echo $this->Html->scriptEnd(); ?>
<div class="posts form">
<?php echo $this->Form->create('Post'); ?>
    <div class="grid_6 right-border">
        <fieldset>
            <legend> <div> <h2><?php __('Edit Post'); ?></h2> </div> </legend>
            <?php
            echo $this->Form->hidden("Post.id");
            echo $this->Form->hidden("bannerAttachmentIndex");
            echo $this->Form->input('Post.group_id', array("escape" => false));
            echo $this->Form->input('Post.title');
            echo $this->Html->div("error-message", "", 
                    array("id"=>"PostTitleError", "style"=>"display: none"));
            echo $this->Html->div("validated", "✓", 
                    array("id"=>"PostTitleValid", "style"=>"display: none"));
            echo $this->Form->hidden("Post.formatted_date");
            echo $this->Form->input("Post.displayDate", 
                    array("type"=>"text", 
                          "label"=>__("Date", true), 
                          "after"=>$this->Form->text("Post.displayTime", 
                                                     array("div"=>false, "label"=>false))));
            echo $this->Markdown->input('Post.content', array("label"=>__("Content", true), "rows"=>"20"));
            ?>
        </fieldset>
    </div>
    <div class="grid_3 suffix_3">
        <fieldset>
            <legend> <div> <h2><?php __('Add Resources'); ?></h2> </div> </legend>
            <?php 
            echo $this->Html->div("input", 
                    $this->Html->div("placeholder", 
                            $this->Html->div("", 
                                    $this->Html->image($banner, array("id"=>"post-banner-id")), 
                                    array("id" => "post-banner")
                    ) . 
                    $this->element("uploadify", 
                    array("plugin" => "cuploadify", 
                            "dom_id" => "image_upload", 
                            "session_id" => $this->Session->id(),
                            "include_scripts" => array("uploadify_css", "uploadify", "swfobject"),
                            "options" => array("auto" => true, 
                                    "folder" => "/" . $this->data["Post"]["id"],
                                    "script" => $this->Html->url("/urg_post/posts/upload_image"),
                                    "buttonText" => strtoupper(__("Add Banner", true)), 
                                    //"multi" => true,
                                    //"queueID" => "upload_queue",
                                    "removeCompleted" => true,
                                    "fileExt" => "*.jpg;*.jpeg;*.png;*.gif;*.bmp",
                                    "fileDataName" => "imageFile",
                                    "fileDesc" => "Image Files",
                                    "onComplete" => "on_complete_images",
                                    "onProgress" => "image_upload_in_progress",
                                    "onAllComplete" => "image_uploads_completed"
                                    ))))); 
            echo $this->Html->div("input", $this->element("uploadify",
                    array("plugin" => "cuploadify", 
                            "dom_id" => "attachment_upload", 
                            "session_id" => $this->Session->id(),
                            "options" => array("auto" => true, 
                                    "folder" => "/" . $this->data["Post"]["id"],
                                    "script" => $this->Html->url("/urg_post/posts/upload_attachments"),
                                    "buttonText" => strtoupper(__("Attachments", true)), 
                                    "removeCompleted" => true,
                                    "fileExt" => "*.mp3;*.jpg;*.jpeg;*.png;*.gif;*.bmp;" .
                                                 "*.ppt;*.pptx;*.doc;*.docx",
                                    "fileDataName" => "attachmentFile",
                                    "fileDesc" => "Post Attachments",
                                    "multi" => true,
                                    "onComplete" => "on_complete_attachments",
                                    "onProgress" => "attachment_upload_in_progress",
                                    "onAllComplete" => "attachment_uploads_completed"
                                    ))));
            echo $this->element("attachment_queue", array("attachments" => $attachments, 
                                                          "post_id" => $this->data["Post"]["id"], 
                                                          "plugin" => "urg_post"));
            ?>
        </fieldset>
    </div>
    <div class="grid_6 suffix_6">
        <?php echo $this->Form->end(__('Upload Post', true));?>
    </div>
    <?php 
        echo $this->Html->div("", $this->Html->image("/urg_post/img/loading.gif"), 
                array("id" => "loading-validate", "style" => "display: none")); 
    ?>
    <div style="display: none;" id="in-progress" title="<?php echo __("Uploads pending...", true); ?>">
        <p>
            <?php echo __("The post form will be submitted after all attachments have been uploaded.", true); ?>
        </p>
    </div>
</div>
<?php echo $this->Html->scriptStart(); ?>
    function on_validate(dom_id, XMLHttpRequest, textStatus) {
        $("#loading-validate").hide();
        
        if ($(dom_id + "Error").text() == "") {
            $(dom_id + "Error").hide();
            $(dom_id).after($(dom_id + "Valid"));
            $(dom_id + "Valid").show();
            $(dom_id).removeClass("invalid");
        } else {
            $(dom_id + "Valid").hide();
            $(dom_id).after($(dom_id + "Error"));
            $(dom_id + "Error").show();
            $(dom_id).addClass("invalid");
        }
    }

    function loading_validate(dom_id) {
        $(dom_id).after($("#loading-validate"));
        $(dom_id + "Error").hide();
        $("#loading-validate").show();
    }

    $("#PostTitle").blur(function() {
        if ($(this).hasClass("dirty")) {
        <?php
        $this->Js->get("#PostTitle");
        echo $this->Js->request("/urg_post/posts/validate_field/Post/title", array(
                "update" => "#PostTitleError",
                "async" => true,
                "data" => '{ value: $("#PostTitle").val() }',
                "dataExpression" => true,
                "complete" => "on_validate('#PostTitle', XMLHttpRequest, textStatus)",
                "before" => "loading_validate('#PostTitle')"
        ));
        ?>
        }

        $(this).removeClass("dirty");
    });

    var search_series = true;
    var search_speaker = true;
<?php echo $this->Html->scriptEnd(); ?>

<?php echo $this->Html->script("tinymce/jquery.tinymce.js"); ?>
<?php echo $this->Html->script("/urg_post/js/jquery.timepicker.min.js"); ?>

<?php echo $this->Html->scriptStart(); ?>
    $(function() {
        $("#in-progress").dialog({
            modal: true,
            autoOpen: false
        })
    });

    $("#PostAddForm").submit(function() {
        error = false;
        scrolled = false;
        $(":input.invalid").each(function(index) {
            if (!scrolled) {
                $('html,body').animate(
                        { scrollTop: $(this).offset().top - 30 }, 
                        { duration: 'fast', easing: 'swing'}
                );
                scrolled = true;
            }
            $(this).effect("highlight", { color: "#FFD4D4" });
            error = true;
        });

        if (error) return false;

        if (image_in_progress || attachment_in_progress) {
            submit_form = true;
            $("#in-progress").dialog("open");
        }

        return !image_in_progress && !attachment_in_progress;
    });

    function image_uploads_completed(event, data) {
        image_in_progress = false;

        $("#in-progress").dialog("close");

        if (submit_form) {
            $("#PostAddForm").submit();
        }
    }

    function attachment_uploads_completed(event, data) {
        attachment_in_progress = false;

        $("#in-progress").dialog("close");

        if (submit_form) {
            $("#PostAddForm").submit();
        }
    }
    
    $($(":input").addClass("dirty"));

    $($(":input").change(function(event) {
        $(this).addClass("dirty");
    }));

    function invalidate(dom_id) {
        has_errors = $("#flashMessage").length;
        if (!has_errors || $(dom_id).hasClass("form-error")) {
            $(dom_id).addClass("invalid"); 
        }
    }

    $(function() {
        invalidate("#PostTitle");
    });

    $(function() {
        $("#PostDisplayDate").datepicker({
            altField: "#PostFormattedDate",
            altFormat: "yy-mm-dd",
            dateFormat: "MM d, yy"
        });

        $("input:submit").button();
    });

    $(function() {
        $('#PostDisplayTime').timepicker({ 
            scrollDefaultNow: true, 
            timeFormat: 'h:i A'
        }); 
    });

    $(function() { 
        $(".delete-attachment-link").click(function() {
            if (confirm("<?php __("Are you sure you want to delete this attachment?") ?>")) {
                $.get($(this).attr("href"), {domId: $(this).attr("id")}, function(data) {
                    data = jQuery.parseJSON(data);
                    if (data.success) {
                        $("#" + data.domId).parent().hide();
                    }
                });
            }
            return false;
        });
    });
<?php echo $this->Html->scriptEnd(); ?>
<?php $this->Html->css("/urg_post/css/jquery.timepicker.css", null, array("inline"=>false)); ?>
<?php $this->Html->css("/urg_post/css/urg_post.css", null, array("inline"=>false));
