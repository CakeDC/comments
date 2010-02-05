<?php 
/**
 * Copyright 2009 - 2010, Cake Development Corporation
 *                        1785 E. Sahara Avenue, Suite 490-423
 *                        Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 */
?>
<div class="comments index">
<h2><?php __d('comments', 'Comments');?></h2>
<p>
<?php
echo $paginator->counter(array(
'format' => __d('comments', 'Page %page% of %pages%, showing %current% records out of %count% total, starting on record %start%, ending on %end%', true)
));
?></p>
<table cellpadding="0" cellspacing="0">
<tr>
	<th><?php echo $paginator->sort('id');?></th>
	<th><?php echo $paginator->sort('comment_id');?></th>
	<th><?php echo $paginator->sort('foreign_key');?></th>
	<th><?php echo $paginator->sort('user_id');?></th>
	<th><?php echo $paginator->sort('model');?></th>
	<th><?php echo $paginator->sort('approved');?></th>
	<th><?php echo $paginator->sort('body');?></th>
	<th><?php echo $paginator->sort('created');?></th>
	<th><?php echo $paginator->sort('modified');?></th>
	<th class="actions"><?php __d('comments', 'Actions');?></th>
</tr>
<?php
$i = 0;
foreach ($comments as $comment):
	$class = null;
	if ($i++ % 2 == 0) {
		$class = ' class="altrow"';
	}
?>
	<tr<?php echo $class;?>>
		<td>
			<?php echo $comment['Comment']['id']; ?>
		</td>
		<td>
			<?php echo $html->link($comment['ParentComment']['id'], array('controller'=> 'comments', 'action'=>'view', $comment['ParentComment']['id'])); ?>
		</td>
		<td>
			<?php echo $html->link($comment['CommentedOn']['id'], array('controller'=> 'users', 'action'=>'view', $comment['CommentedOn']['id'])); ?>
		</td>
		<td>
			<?php echo $html->link($comment['User']['id'], array('controller'=> 'users', 'action'=>'view', $comment['User']['id'])); ?>
		</td>
		<td>
			<?php echo $comment['Comment']['model']; ?>
		</td>
		<td>
			<?php echo $comment['Comment']['approved']; ?>
		</td>
		<td>
			<?php echo $comment['Comment']['body']; ?>
		</td>
		<td>
			<?php echo $comment['Comment']['created']; ?>
		</td>
		<td>
			<?php echo $comment['Comment']['modified']; ?>
		</td>
		<td class="actions">
			<?php echo $html->link(__d('comments', 'View', true), array('action'=>'view', $comment['Comment']['id'])); ?>
			<?php echo $html->link(__d('comments', 'Edit', true), array('action'=>'edit', $comment['Comment']['id'])); ?>
			<?php echo $html->link(__d('comments', 'Delete', true), array('action'=>'delete', $comment['Comment']['id']), null, sprintf(__d('comments', 'Are you sure you want to delete # %s?', true), $comment['Comment']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
</table>
</div>
<div class="paging">
	<?php echo $paginator->prev('<< '.__d('comments', 'previous', true), array(), null, array('class'=>'disabled'));?>
 | 	<?php echo $paginator->numbers();?>
	<?php echo $paginator->next(__d('comments', 'next', true).' >>', array(), null, array('class'=>'disabled'));?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__d('comments', 'New Comment', true), array('action'=>'add')); ?></li>
		<li><?php echo $html->link(__d('comments', 'List Comments', true), array('controller'=> 'comments', 'action'=>'index')); ?> </li>
		<li><?php echo $html->link(__d('comments', 'New Parent Comment', true), array('controller'=> 'comments', 'action'=>'add')); ?> </li>
		<li><?php echo $html->link(__d('comments', 'List Users', true), array('controller'=> 'users', 'action'=>'index')); ?> </li>
		<li><?php echo $html->link(__d('comments', 'New User', true), array('controller'=> 'users', 'action'=>'add')); ?> </li>
	</ul>
</div>
