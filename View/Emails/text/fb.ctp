<?php echo $this->HtmlText->convert_html_to_text($post["Post"]["content"]); ?>
<?php echo __("For more info, see ") . 
        Router::url(array("plugin" => "urg_post", 
                          "controller" => "posts",
                          "action" => "view",
                          $post["Post"]["id"],
                          $post["Post"]["slug"]),
                    true); ?>
