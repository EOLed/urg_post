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

    <div id="post-content" class="grid_8 right-border">
        <?php echo $post["Post"]["content"]; ?>

        <?php echo $this->Post->attachments($post); ?>
    </div>

    <div id="group-upcoming" class="grid_4">
        <h2><?php echo __("Upcoming events", true); ?></h2>
        <?php echo $this->Post->upcoming_activity($upcoming_events); ?>
    </div>
</div>
<script type="text/javascript">
<?php echo $this->element("js_equal_height"); ?>
$("#post-content, #group-upcoming").equalHeight();
</script>
<?php $this->Html->css("/urg_post/css/urg_post.css", null, array("inline" => false)); ?>

