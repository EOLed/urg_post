<div class="attachmentTypes form">
<?php echo $this->Form->create('AttachmentType');?>
	<fieldset>
 		<legend><?php echo __('Edit Attachment Type'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name');
		echo $this->Form->input('description');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('Delete'), array('action' => 'delete', $this->Form->value('AttachmentType.id')), null, sprintf(__('Are you sure you want to delete # %s?'), $this->Form->value('AttachmentType.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Attachment Types'), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Attachments'), array('controller' => 'attachments', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Attachment'), array('controller' => 'attachments', 'action' => 'add')); ?> </li>
	</ul>
</div>