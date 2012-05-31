<?php $this->Html->css("/urg_post/css/urg_post.css", null, array("inline" => false)); ?>
<div class="posts view">
    <div class="row">
        <div id="banner" class="span9 right-border">
        <?php
            if (isset($widgets["banner"])) {
                $banner = $widgets["banner"];
                echo $this->{$banner["Widget"]["helper_name"]}->build(${"options_" .  
                                                                      $banner["Widget"]["id"]});
            }
        ?>
        </div>
        <div id="side-panel" class="hidden-phone span3">
        <?php
            if (isset($widgets["side"])) {
                $side = $widgets["side"];
                echo $this->{$side["Widget"]["helper_name"]}->build(${"options_" .  
                                                                      $side["Widget"]["id"]});
            }
        ?>
        </div>
    </div>
    <div class="row">
        <?php if (isset($widgets["title"])) {
            $title = $widgets["title"];
            echo $this->Html->div("title_widget", 
                                  $this->{$title["Widget"]["helper_name"]}->build(${"options_" . $title["Widget"]["id"]}));
        } else { ?>
        <div class='span12'>
            <div id="post-title" class="page-title">
                <div><?php echo $post["Post"]["title"]?></div>
                <div id="post-info">
                    <?php echo __(sprintf("by %s on %s", $post["User"]["username"], date("F j, Y h:i A", strtotime($post["Post"]["publish_timestamp"])))) ?>
                    <span id="post-title-home-group"> &raquo;
                        <?php echo $this->Html->link($home_group["Group"]["name"],
                                                     array("plugin" => "urg",
                                                           "controller" => "groups",
                                                           "action" => "view",
                                                           $home_group["Group"]["slug"]))?>
                    </span>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>

    <?php if (isset($widgets["header"])) {
        $header = $widgets["header"];
        echo $this->Html->div("row", $this->Html->div("span12 header_widget", 
                              $this->{$header["Widget"]["helper_name"]}->build(${"options_" . 
                                                                               $header["Widget"]["id"]})));
    } ?>

   <div class="row"> 
        <?php
        $columns = array();
        if (!isset($widgets["layout"])) {
            $columns["col-0"] = "span8 right-border";
            $columns["col-1"] = "span4";
        } else {
            $layout_widget = $widgets["layout"];
            $this->{$layout_widget["Widget"]["helper_name"]}->build(${"options_" . 
                                                                    $layout_widget["Widget"]["id"]});

            $columns = $this->{$layout_widget["Widget"]["helper_name"]}->get_columns();
        }

        foreach ($columns as $column_id => $column_class) { ?>
        <div id="<?php echo $column_id ?>" class="<?php echo $column_class ?>">
            <div class="post-col">
            <?php 
            if (isset($widgets[$column_id])) {
                foreach ($widgets[$column_id] as $widget) {
                    echo $this->Html->div("post-widget", 
                            $this->{$widget["Widget"]["helper_name"]}->build(${"options_" . $widget["Widget"]["id"]}));
                }
            } else if ($column_id == "col-0") {
                echo $post["Post"]["content"];
            }
            ?>

            <?php //echo $this->Post->attachments($post); ?>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
<script type="text/javascript">
<?php echo $this->element("js_equal_height"); ?>
$(".post-col").equalHeight();
</script>
