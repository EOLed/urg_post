<?php echo $this->HtmlText->convert_html_to_text($this->Markdown->html($post["Post"]["content"])); ?>


<?php echo __("For more info, see ") . 
        Router::url(array("plugin" => "urg_post", 
                          "controller" => "posts",
                          "action" => "view",
                          $post["Post"]["id"],
                          $post["Post"]["slug"]),
                    true); ?>
