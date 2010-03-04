<h2><?php __d('comments', 'Comments');?></h2>

<div class="finder">
	<?php echo $this->element('comments/finder'); ?>
</div>

<ul>
	<li><?php echo $this->Html->link(__d('comments', 'Filter spam comments', true), array('action' => 'index', 'spam'));?></li>
	<li><?php echo $this->Html->link(__d('comments', 'Filter good comments', true), array('action' => 'index', 'clean'));?></li>
</ul>

<?php echo $this->Form->create('Comment',array('id' => 'CommentForm', 'name' => 'CommentForm', 'url' => Set::merge(array('action' => 'process'), $this->params['named']) ));?>
<?php echo $this->Form->input('Comment.action', array(
				'type' => 'select', 
				'options' => array(
					'ham' => __d('comments', 'Mark as ham', true),
					'spam' => __d('comments', 'Mark as spam', true),
					'delete' => __d('comments', 'Delete', true),
					'approve' => __d('comments', 'Approve', true),
					'disapprove' => __d('comments', 'Dispprove', true))));?>
<?php echo $this->Form->submit('Process', array('name' => 'process'));?>

<table cellpadding="0" cellspacing="0">
	<tr>
		<th><?php echo $this->Paginator->sort('name');?></th>
		<th><?php echo $this->Paginator->sort('author_name');?></th>
		<th><?php echo $this->Paginator->sort('author_email');?></th>
		<th><?php echo $this->Paginator->sort('author_url');?></th>
		<th><?php echo $this->Paginator->sort('created');?></th>
		<th><?php echo $this->Paginator->sort('is_spam');?></th>
		<th><?php echo $this->Paginator->sort('approved');?></th>
		<th><?php __d('comments', 'Select...');?> <input id="mainCheck" style="width: 100%;" type="checkbox" onclick="$('.cbox').each (function (id,f) {$('#'+this.id).attr('checked', !!$('#mainCheck').attr('checked'))})"> </th>
		<th class="actions"><?php __d('comments', 'Actions');?></th>
	</tr>
	<?php
	$i = 0;	
	foreach ($comments as $comment) :
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
		<tr<?php echo $class;?>>
			<td>
				<?php echo h($comment['Comment']['title']); ?>
			</td>
			<td>
				<?php echo h($comment['Comment']['author_name']); ?>
			</td>
			<td>
				<?php echo h($comment['Comment']['author_email']); ?>
			</td>
			<td>
				<?php echo h($comment['Comment']['author_url']); ?>
			</td>
			<td>
				<?php echo $comment['Comment']['created']; ?>
			</td>
			<td>
				<?php echo $comment['Comment']['is_spam']; ?>
			</td>
			<td>
				<?php echo ($comment['Comment']['approved'] ? __d('comments', 'Yes', true) : __d('comments', 'No', true)); ?>
			</td>
			<td class="comment-check">
				<?php
					echo $this->Form->input('Comment.' . $comment['Comment']['id'], array(
						'label' => false,
						'div' => false,
						'class' => 'cbox',
						'type' => 'checkbox'));
					?>
			</td>
			<td class="actions">
				<?php echo $this->Html->link(__d('comments', 'Approve', true), array('action' => 'approve', $comment['Comment']['id'])); ?>
				<?php echo $this->Html->link(__d('comments', 'Mark as spam', true), array('action' => 'spam', $comment['Comment']['id'])); ?>
				<?php echo $this->Html->link(__d('comments', 'Mark as ham', true), array('action' => 'ham', $comment['Comment']['id'])); ?>
				<?php echo $this->Html->link(__d('comments', 'Disapprove', true), array('action' => 'disapprove', $comment['Comment']['id'])); ?>
				<?php echo $this->Html->link(__d('comments', 'View', true), array('action' => 'view', $comment['Comment']['id'])); ?>
				<?php echo $this->Html->link(__d('comments', 'Edit', true), array('action' => 'edit', $comment['Comment']['id'])); ?>
				<?php echo $this->Html->link(__d('comments', 'Delete', true), array('action' => 'delete', $comment['Comment']['id']), null, sprintf(__d('comments', 'Are you sure you want to delete # %s?', true), $comment['Comment']['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
<?php echo $this->Form->end(); ?>

<?php echo $this->element('paging'); ?>