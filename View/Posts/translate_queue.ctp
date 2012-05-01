<div class="posts index">
	<h2><?php echo __('Translate Posts');?></h2>
	<table>
	<tr>
        <th><?php echo __('Group');?></th>
        <th><?php echo __('Slug');?></th>
        <th><?php echo __('Creator');?></th>
        <th><?php echo __('Created');?></th>
        <th class="actions"></th>
	</tr>
	<?php
	$i = 0;
	foreach ($posts as $post):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $this->Html->link($post['Group']['slug'], array('controller' => 'posts', 'action' => 'view', $post['Post']['id'])); ?>
			<?php echo $this->Html->link($post['Post']['slug'], array('controller' => 'posts', 'action' => 'view', $post['Post']['id'])); ?>
			<?php echo $this->Html->link($post['User']['username'], array('controller' => 'posts', 'action' => 'view', $post['Post']['id'])); ?>
			<?php echo $this->Html->link($post['Post']['created'], array('controller' => 'posts', 'action' => 'view', $post['Post']['id'])); ?>
		</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Translate'), 
                                         array('action' => 'translate', 
                                               $post['Post']['id'], 
                                               $post["Post"]["locale"]), 
                                         array("class" => "button")); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Post'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Posts'), array('controller' => 'posts', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Post'), array('controller' => 'posts', 'action' => 'add')); ?> </li>
	</ul>
</div>

<script type="text/javascript">
    $(function() {
        $(".button").button();
    });
</script>
