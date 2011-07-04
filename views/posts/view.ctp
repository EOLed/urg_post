<div class="posts view">
    <?php foreach ($banners as $banner) { ?>
    <div id="banner" class="grid_9 right-border">
        <?php echo $this->Html->image($banner, array("class"=>"shadow")); ?>
    </div>
    <?php } ?>
    <div id="about-panel" class="grid_3">
        <h3><?php echo strtoupper(__("About us", true)); ?></h3>
        <?php echo $about["Post"]["content"] ?>
    </div>

    <?php if (isset($widgets["title"])) {
        $title_widget = $widgets["title"];
        $options = array();
        foreach ($this->{$title_widget["Widget"]["helper_name"]}->widget_options as $option) {
            $options[$option] = ${$option . "_" . $title_widget["Widget"]["id"]};
        }
        echo $this->Html->div("title_widget", $this->{$title_widget["Widget"]["helper_name"]}->build($options));
    } else { ?>
    <div id='group-name' class='grid_12 page-title'>
        <div><?php echo $post["Post"]["title"]?></div>
    </div>
    <?php } ?>

    <?php if (isset($widgets["header"])) {
        $header_widget = $widgets["header"];
        $options = array();
        foreach ($this->{$header_widget["Widget"]["helper_name"]}->widget_options as $option) {
            $options[$option] = ${$option . "_" . $header_widget["Widget"]["id"]};
        }
        echo $this->Html->div("header_widget", $this->{$header_widget["Widget"]["helper_name"]}->build($options));
    } ?>

    <?php
    $columns = array();
    if (!isset($widgets["layout"])) {
        $columns["post-col-0"] = "grid_8 right-border";
        $columns["post-col-1"] = "grid_4";
    } else {
        $layout_widget = $widgets["layout"];
        $options = array();
        foreach ($this->{$layout_widget["Widget"]["helper_name"]}->widget_options as $option) {
            $options[$option] = ${$option . "_" . $layout_widget["Widget"]["id"]};
        }
        $this->{$layout_widget["Widget"]["helper_name"]}->build($options);

        $columns = $this->{$layout_widget["Widget"]["helper_name"]}->get_columns();
    }

    foreach ($columns as $column_id => $column_class) { ?>
    <div id="<?php echo $column_id ?>" class="<?php echo $column_class ?>">
        <?php 
        if (isset($widgets[$column_id])) {
            foreach ($widgets[$column_id] as $widget) {
                $options = array();
                foreach ($this->{$widget["Widget"]["helper_name"]}->widget_options as $option) {
                    $options[$option] = ${$option . "_" . $widget["Widget"]["id"]};
                }
                echo $this->Html->div("post-widget", $this->{$widget["Widget"]["helper_name"]}->build($options));
            }
        }
        ?>
        <?php //echo $post["Post"]["content"]; ?>

        <?php //echo $this->Post->attachments($post); ?>
    </div>
    <?php } ?>
</div>
<script type="text/javascript">
<?php echo $this->element("js_equal_height"); ?>
$("#post-col-1, #post-col-2").equalHeight();
</script>
<?php $this->Html->css("/urg_post/css/urg_post.css", null, array("inline" => false)); ?>

