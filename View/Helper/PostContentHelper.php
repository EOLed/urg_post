<?php
App::uses("MarkdownHelper", "Markdown.View/Helper");
App::uses("AbstractWidgetHelper", "Urg.Lib");
App::uses("Sanitize", "Utility");
App::uses("FacebookHelper", "Socialize.View/Helper");
App::uses("TwitterBootstrapHelper", "TwitterBootstrap.View/Helper");
class PostContentHelper extends AbstractWidgetHelper {
    var $helpers = array("Socialize.Facebook" => array("app_id" => "169875155877"), 
                         "Html", 
                         "Time", 
                         "Markdown.Markdown",
                         "Urg.SmartSnippet",
                         "Form",
                         "TwitterBootstrap.TwitterBootstrap",
                         "Session");
    var $images_type;
    var $banner_type;
    var $audio_type;

    function build_widget() {
        CakeLog::write(LOG_DEBUG, "building Post Content widget with options: " .
                                  Debugger::exportVar($this->options, 3));
        $this->Html->css("/urg_post/css/urg_post.css", null, array("inline"=>false));
        $this->banner_type = $this->options["banner_type"];
        $this->audio_type = $this->options["audio_type"];
        $this->images_type = $this->options["images_type"];
        return $this->post_content($this->options["title"], $this->options["post"]);
    }

    function post_content($title, $post) {
        $content = "";
        $links = array();

        if ($title !== false) {
            $content = $this->Html->tag("h2", __($title));
        }

        if ($this->options["can_edit"]) {
            array_push($links, $this->Html->link(__("Edit"), 
                                                 array("plugin" => "urg_post",
                                                       "controller" => "posts",
                                                       "action" => "edit",
                                                       $this->options["post_id"])));
        }

        if ($this->options["can_delete"]) {
            array_push($links, $this->Html->link(__("Delete"),
                                                 array("plugin" => "urg_post",
                                                       "controller" => "posts",
                                                       "action" => "delete",
                                                       $this->options["post_id"]), 
                                                 null,
                                                 __("Are you sure you want to delete this?")));
        }

        if (!empty($links)) {
            $content .= $this->Html->div("", 
                                        $this->_View->element("bootstrap_dropdown", 
                                                              array("label" => __("Action", true),
                                                                    "items" => $links,
                                                                    "class" => "btn-mini btn-inverse")),
                                        array("class" => "action-dropdown", "escape" => false));

        }

        $content .= $this->Markdown->html(Sanitize::html($post["Post"]["content"]));
        $gallery = "";

        $gallery_index = 1;

        $attachment_items = "";
        if (isset($post["Attachment"])) {
            foreach ($post["Attachment"] as $attachment) {
                if ($attachment["attachment_type_id"] == $this->images_type["AttachmentType"]["id"]) {
                    $link = $this->Html->link($this->Html->image("/urg_post/img/" .  $attachment["post_id"] . "/" .  $attachment["filename"]["thumb"]), "/urg_post/img/" .  $attachment["post_id"] . "/" . $attachment["filename"]["view"], array("escape" => false, "class" => "thumbnail gallery-$attachment[post_id]"));
                    $gallery .= $this->Html->tag("li", $link, array("class" => "span2")); 
                } else if ($attachment["attachment_type_id"] != $this->banner_type["AttachmentType"]["id"]) {
                    $webroot = $attachment["attachment_type_id"] == $this->audio_type["AttachmentType"]["id"] ? "audio" : "files";
                    $attachment_items .= $this->Html->tag("li", 
                                                          $this->Html->link($attachment["filename"], 
                                                                            "/urg_post/$webroot/$attachment[post_id]/$attachment[filename]"));
                }
            }
        }

        $js = "";
        if ($gallery != "") {
            $this->Html->css("/urg_post/css/colorbox.css", null, array("inline"=>false));
            $this->Html->script("/urg_post/js/jquery.colorbox-min", array("inline" => false));
            $gallery = $this->Html->div("post-section post-section-gallery", $this->Html->tag("h2", __("Pictures")) . $this->Html->tag("ul", $gallery, array("class" => "thumbnails", "id" => "gallery-" . $post["Post"]["id"])));
            $js = $this->js($post["Post"]["id"]);
        }

        $attachment_list = "";
        if ($this->options["list_attachments"] && $attachment_items != "") {
            $attachment_list = $this->Html->div("post-section post-section-attachments", $this->Html->tag("h2", __("Attachments")) . $this->Html->tag("ul", $attachment_items));
        }

        return $this->Html->div("post-content", 
                                $content . $gallery . $attachment_list . $this->build_social_widget($post) . $this->build_comments($post), 
                                array("id" => $this->options["id"])) . $js;
    }

    function build_comments($post) {
        if (!$post["Post"]["commentable"] || (isset($this->options["comments"]) && $this->options["comments"] === false))
            return "";

        $comments_header = $this->Html->tag("h2", 
                                            __("Comments") . "<a name='comments'></a>", 
                                            array("id" => "comments-header-" . $post["Post"]["id"]));
        $comments = $this->Html->div("", "", array("id" => "comments-" . $post["Post"]["id"]));
        $comments_form = $this->Html->tag("h2", 
                                          __("Add a Comment"), 
                                          array("class" => "comments-form-header"));
        $comments_form .= $this->Session->flash();
        $comments_form .= $this->Form->create("PostComment", 
                                              array("action" => "add", "class" => "post-comment-form"));
        $comments_form .= $this->Form->hidden("PostComment.post_id", array("value" => $post["Post"]["id"]));

        $logged_user = CakeSession::read("User");

        if ($logged_user == null) {
            $comments_form .= $this->TwitterBootstrap->input("PostComment.username");
            $comments_form .= $this->TwitterBootstrap->input("PostComment.link");
        }

        $comments_form .= $this->Form->textarea("PostComment.comment", 
                                                array("style" => "width: 98%; height: 200px"));
        $formatting_guide = $this->Html->div("format-guide", 
                              $this->Html->div("", $this->Html->link(__("Formatting guide"), "#", array("id" => "formatting-guide-link")))); 

        $formatting_guide .= $this->_View->element("UrgPost.formatting_guide", array("dom_id" => "formatting-guide"));
        $comments_form .= $formatting_guide;
        $comments_form .= $this->Form->button(__("Comment", true), array("class" => "btn btn-inverse")) . " ";
        $comments_form .= $this->Form->end();

        $js = "$.get(\"" . $this->Html->url(array("plugin" => "urg_post", "controller" => "post_comments", "action" => "post", $post["Post"]["id"])) . "\", function(data) { $('#comments-" . $post["Post"]["id"] . "').html(data);});";
        return $this->Html->div("post-comments", $comments_header . $comments . $comments_form . $this->Html->scriptBlock($js));
    }

    function build_social_widget($post) {
        $post_url = $this->Html->url(array("plugin" => "urg_post",
                                           "controller" => "posts",
                                           "action" => "view",
                                           $post["Post"]["id"],
                                           $post["Post"]["slug"]));

        $social = "";
        if ($this->options["social"] !== false) {
            $social = $this->Html->div("hidden-phone social-bookmarks", 
                                       $this->Facebook->loadJavascriptSdk() . 
                                       $this->Facebook->share(FULL_BASE_URL . $post_url));

            $this->_View->assign("meta", "");
            $this->Html->meta(array("name"=>"og:description", 
                                    "content"=>$this->SmartSnippet->snippet($post["Post"]["content"], 125, 25)),
                              array(),
                              array("inline" => false));
        }

        return $social;
    }

    function js($id) {
        $js = "$(function() { $('.gallery-$id').colorbox({rel:'gallery-$id'}); });";
        return $this->Html->scriptBlock($js);
    }
}
