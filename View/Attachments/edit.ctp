<div class="attachments form">
<?php echo $this->Form->create('Attachment');?>
	<fieldset>
 		<legend><?php echo __('Edit Attachment'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('attachment_type_id');
		echo $this->Form->input('post_id');
		echo $this->Form->input('user_id');
		echo $this->Form->input('filename');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('Delete'), array('action' => 'delete', $this->Form->value('Attachment.id')), null, sprintf(__('Are you sure you want to delete # %s?'), $this->Form->value('Attachment.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Attachments'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Attachment Types'), array('controller' => 'attachment_types', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Attachment Type'), array('controller' => 'attachment_types', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Posts'), array('controller' => 'posts', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Post'), array('controller' => 'posts', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Attachment Metadata'), array('controller' => 'attachment_metadata', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Attachment Metadatum'), array('controller' => 'attachment_metadata', 'action' => 'add')); ?> </li>
	</ul>
</div>