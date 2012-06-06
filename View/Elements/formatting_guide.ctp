<table class="table" style="display: none" id="<?php echo $dom_id ?>">
    <tr>
        <th><?php echo __("You type:"); ?></th>
        <th><?php echo __("You see:"); ?></th>
    </tr>
    <tr>
        <?php $type_this = "**" . __("Bold text") . "**"; ?>
        <td><?php echo $type_this ?></td>
        <td><?php echo $this->Markdown->html($type_this); ?></td>
    </tr>
    <tr>
        <?php $type_this = "*" . __("Italic text") . "*"; ?>
        <td><?php echo $type_this ?></td>
        <td><?php echo $this->Markdown->html($type_this); ?></td>
    </tr>
    <tr>
        <?php $type_this = "* " . __("Bullet list") . "\n* " . __("Bullet list"); ?>
        <td><?php echo nl2br($type_this) ?></td>
        <td><?php echo $this->Markdown->html($type_this); ?></td>
    </tr>
    </tr>
    <tr>
        <?php $type_this = "1. " . __("Ordered list") . "\n1. " . __("Ordered list"); ?>
        <td><?php echo nl2br($type_this) ?></td>
        <td><?php echo $this->Markdown->html($type_this); ?></td>
    </tr>
    <tr>
        <?php $type_this = "> " . __("blockquote"); ?>
        <td><?php echo $type_this ?></td>
        <td><?php echo $this->Markdown->html($type_this); ?></td>
    </tr>
    <tr>
        <?php $type_this = "[Google](http://google.com)"; ?>
        <td><?php echo $type_this ?></td>
        <td><?php echo $this->Markdown->html($type_this); ?></td>
    </tr>
    <tr>
        <?php $type_this = "![" . __("My alt text") . "](/favicon.png)"; ?>
        <td><?php echo $type_this ?></td>
        <td><?php echo $this->Markdown->html($type_this); ?></td>
    </tr>
</table>
<script type="text/javascript">
    $("#<?php echo $dom_id ?>-link").click(function() {
        $("#<?php echo $dom_id ?>").toggle();
        return false;
    });
</script>
