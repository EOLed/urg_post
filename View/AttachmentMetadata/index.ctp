<div class="attachmentMetadata index">
	<h2><?php echo __('Attachment Metadata');?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id');?></th>
			<th><?php echo $this->Paginator->sort('attachment_id');?></th>
			<th><?php echo $this->Paginator->sort('key');?></th>
			<th><?php echo $this->Paginator->sort('value');?></th>
			<th><?php echo $this->Paginator->sort('created');?></th>
			<th><?php echo $this->Paginator->sort('modified');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($attachmentMetadata as $attachmentMetadatum):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $attachmentMetadatum['AttachmentMetadatum']['id']; ?>&nbsp;</td>
		<td>
			<?php echo $this->Html->link($attachmentMetadatum['Attachment']['filename'], array('controller' => 'attachments', 'action' => 'view', $attachmentMetadatum['Attachment']['id'])); ?>
		</td>
		<td><?php echo $attachmentMetadatum['AttachmentMetadatum']['key']; ?>&nbsp;</td>
		<td><?php echo $attachmentMetadatum['AttachmentMetadatum']['value']; ?>&nbsp;</td>
		<td><?php echo $attachmentMetadatum['AttachmentMetadatum']['created']; ?>&nbsp;</td>
		<td><?php echo $attachmentMetadatum['AttachmentMetadatum']['modified']; ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $attachmentMetadatum['AttachmentMetadatum']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $attachmentMetadatum['AttachmentMetadatum']['id'])); ?>
			<?php echo $this->Html->link(__('Delete'), array('action' => 'delete', $attachmentMetadatum['AttachmentMetadatum']['id']), null, sprintf(__('Are you sure you want to delete # %s?'), $attachmentMetadatum['AttachmentMetadatum']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%')
	));
	?>	</p>

	<div class="paging">
		<?php echo $this->Paginator->prev('<< ' . __('previous'), array(), null, array('class'=>'disabled'));?>
	 | 	<?php echo $this->Paginator->numbers();?>
 |
		<?php echo $this->Paginator->next(__('next') . ' >>', array(), null, array('class' => 'disabled'));?>
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Attachment Metadatum'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Attachments'), array('controller' => 'attachments', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Attachment'), array('controller' => 'attachments', 'action' => 'add')); ?> </li>
	</ul>
</div>