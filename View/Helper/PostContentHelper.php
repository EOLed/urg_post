<?php
App::uses("MarkdownHelper", "Markdown.View/Helper");
App::uses("AbstractWidgetHelper", "Urg.Lib");
App::uses("Sanitize", "Utility");
class PostContentHelper extends AbstractWidgetHelper {
    var $helpers = array("Html", "Time", "Markdown");
    var $images_type;

    function build_widget() {
        CakeLog::write(LOG_DEBUG, "building Post Content widget with options: " .
                                  Debugger::exportVar($this->options, 3));
        $this->Html->css("/urg_post/css/urg_post.css", null, array("inline"=>false));
        $this->Html->css("/urg_post/css/colorbox.css", null, array("inline"=>false));
        $this->Html->script("/urg_post/js/jquery.masonry.min", array("inline" => false));
        $this->Html->script("/urg_post/js/jquery.colorbox-min", array("inline" => false));
        $this->images_type = $this->options["images_type"];
        return $this->post_content($this->options["title"], $this->options["post"]) . $this->js($this->options["post"]["Post"]["id"]);
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
                                                       $this->options["post"]["Post"]["id"])));
        }

        if ($this->options["can_delete"]) {
            array_push($links, $this->Html->link(__("Delete"),
                                                 array("plugin" => "urg_post",
                                                       "controller" => "posts",
                                                       "action" => "delete",
                                                       $this->options["post"]["Post"]["id"]), 
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

        if (isset($post["Attachment"])) {
            foreach ($post["Attachment"] as $attachment) {
                if ($attachment["attachment_type_id"] == $this->images_type["AttachmentType"]["id"]) {
                    $class = $gallery_index++ % 4 == 0 ? "last" : "";
                    $link = $this->Html->link($this->Html->image("/urg_post/img/" .  $attachment["post_id"] . "/" .  $attachment["filename"]["thumb"]), "/urg_post/img/" .  $attachment["post_id"] . "/" . $attachment["filename"]["view"], array("escape" => false, "class" => "gallery-" . $attachment["post_id"]));
                    $gallery .= $this->Html->div("gallery-image $class", $link); 
                }
            }
        }

        if ($gallery != "") {
            $gallery = $this->Html->div("post-section post-section-gallery", $this->Html->tag("h2", __("Pictures")) . $this->Html->div("gallery", $gallery, array("id" => "gallery-" . $post["Post"]["id"])));
        }

        return $this->Html->div("", $content . $gallery, array("id" => $this->options["id"]));
    }

    function js($id) {
        $js = "$(function(){
                   $(window).load(function(){   
                       $('#gallery-$id').masonry({
                           // options
                          itemSelector : '.gallery-image',
                          columnWidth : 155
                       }); 
                   });
                   $('.gallery-$id').colorbox({rel:'gallery-$id'});
               });";
        return $this->Html->scriptBlock($js);
    }
}
