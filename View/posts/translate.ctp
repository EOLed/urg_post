<div class="grid_6 posts form">
<?php echo $this->Form->create('Post');?>
	<fieldset>
 		<legend><?php echo __('Translate Post'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('group_id', array("type" => "hidden"));
        echo $this->Form->input('Post.locale', array("type" => "select", 
                                                     "label" => __("Language"),
                                                     "options" => $locales));
		echo $this->Form->input('user_id', array("type" => "hidden"));
		echo $this->Form->input('title', array("between" => $this->data["Translation"]["Post"]["title"]));
		echo $this->Form->input('content', array("type" => "textarea", 
                                                 "between" => $this->data["Translation"]["Post"]["content"]));
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="grid_6 actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('Delete'), array('action' => 'delete', $this->Form->value('Post.id')), null, sprintf(__('Are you sure you want to delete # %s?'), $this->Form->value('Post.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Posts'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Groups'), array('controller' => 'groups', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Group'), array('controller' => 'groups', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
