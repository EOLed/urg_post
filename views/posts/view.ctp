<?php $this->Html->css("/urg_post/css/urg_post.css", null, array("inline" => false)); ?>
<div class="posts view">
    <div id="banner" class="grid_9 right-border">
    <?php
        if (isset($widgets["banner"])) {
            $banner = $widgets["banner"];
            echo $this->{$banner["Widget"]["helper_name"]}->build(${"options_" .  
                                                                  $banner["Widget"]["id"]});
        }
    ?>
    </div>
    <div id="side-panel" class="grid_3">
    <?php
        if (isset($widgets["side"])) {
            $side = $widgets["side"];
            echo $this->{$side["Widget"]["helper_name"]}->build(${"options_" .  
                                                                  $side["Widget"]["id"]});
        }
    ?>
    </div>

    <?php if (isset($widgets["title"])) {
        $title = $widgets["title"];
        echo $this->Html->div("title_widget", 
                              $this->{$title["Widget"]["helper_name"]}->build(${"options_" . $title["Widget"]["id"]}));
    } else { ?>
    <div id='group-name' class='grid_12 page-title'>
        <div><?php echo $post["Post"]["title"]?></div>
    </div>
    <?php } ?>

    <?php if (isset($widgets["header"])) {
        $header = $widgets["header"];
        echo $this->Html->div("header_widget", 
                              $this->{$header["Widget"]["helper_name"]}->build(${"options_" . 
                                                                               $header["Widget"]["id"]}));
    } ?>

    <?php
    $columns = array();
    if (!isset($widgets["layout"])) {
        $columns["col-0"] = "grid_8 right-border";
        $columns["col-1"] = "grid_4";
    } else {
        $layout_widget = $widgets["layout"];
        $this->{$layout_widget["Widget"]["helper_name"]}->build(${"options_" . 
                                                                $layout_widget["Widget"]["id"]});

        $columns = $this->{$layout_widget["Widget"]["helper_name"]}->get_columns();
    }

    foreach ($columns as $column_id => $column_class) { ?>
    <div id="<?php echo $column_id ?>" class="post-col <?php echo $column_class ?>">
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
    <?php } ?>
</div>
<script type="text/javascript">
<?php echo $this->element("js_equal_height"); ?>
$(".post-col").equalHeight();
</script>
