<?php
App::uses("MarkdownHelper", "Markdown.View/Helper");
App::uses("AbstractWidgetHelper", "Urg.Lib");
App::uses("Sanitize", "Utility");
App::uses("FacebookHelper", "Socialize.View/Helper");
class PostContentHelper extends AbstractWidgetHelper {
    var $helpers = array("Socialize.Facebook" => array("app_id" => "169875155877"), 
                         "Html", 
                         "Time", 
                         "Markdown.Markdown",
                         "Urg.SmartSnippet");
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

        $post_url = $this->Html->url(array("plugin" => "urg_post",
                                           "controller" => "posts",
                                           "action" => "view",
                                           $post["Post"]["id"],
                                           $post["Post"]["slug"]));
        $social = "";
        if ($this->options["social"] !== false) {
            $social = $this->Html->div("hidden-phone social-bookmarks", 
                                       $this->Facebook->loadJavascriptSdk() . $this->Facebook->share(FULL_BASE_URL . $post_url));
            $this->_View->assign("meta", "");
            $this->Html->meta(array("name"=>"og:description", 
                                    "content"=>$this->SmartSnippet->snippet($post["Post"]["content"], 125, 25)),
                              array(),
                              array("inline" => false));
        }

        return $this->Html->div("post-content", 
                                $content . $gallery . $attachment_list . $social, 
                                array("id" => $this->options["id"])) . $js;
    }

    function js($id) {
        $js = "$(function() { $('.gallery-$id').colorbox({rel:'gallery-$id'}); });";
        return $this->Html->scriptBlock($js);
    }
}
