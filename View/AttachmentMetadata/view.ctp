<div class="attachmentMetadata view">
<h2><?php echo __('Attachment Metadatum');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Id'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $attachmentMetadatum['AttachmentMetadatum']['id']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Attachment'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($attachmentMetadatum['Attachment']['filename'], array('controller' => 'attachments', 'action' => 'view', $attachmentMetadatum['Attachment']['id'])); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Key'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $attachmentMetadatum['AttachmentMetadatum']['key']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Value'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $attachmentMetadatum['AttachmentMetadatum']['value']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Created'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $attachmentMetadatum['AttachmentMetadatum']['created']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Modified'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $attachmentMetadatum['AttachmentMetadatum']['modified']; ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Attachment Metadatum'), array('action' => 'edit', $attachmentMetadatum['AttachmentMetadatum']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Delete Attachment Metadatum'), array('action' => 'delete', $attachmentMetadatum['AttachmentMetadatum']['id']), null, sprintf(__('Are you sure you want to delete # %s?'), $attachmentMetadatum['AttachmentMetadatum']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Attachment Metadata'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Attachment Metadatum'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Attachments'), array('controller' => 'attachments', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Attachment'), array('controller' => 'attachments', 'action' => 'add')); ?> </li>
	</ul>
</div>
