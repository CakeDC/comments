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
<div class="comments view">
<h2><?php  __d('comments', 'Comment');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __d('comments', 'Id'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $comment['Comment']['id']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __d('comments', 'Parent Comment'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $html->link($comment['ParentComment']['id'], array('controller'=> 'comments', 'action'=>'view', $comment['ParentComment']['id'])); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __d('comments', 'Commented On'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $html->link($comment['CommentedOn']['id'], array('controller'=> 'users', 'action'=>'view', $comment['CommentedOn']['id'])); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __d('comments', 'User'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $html->link($comment['User']['id'], array('controller'=> 'users', 'action'=>'view', $comment['User']['id'])); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __d('comments', 'Model'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $comment['Comment']['model']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __d('comments', 'Approved'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $comment['Comment']['approved']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __d('comments', 'Body'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $comment['Comment']['body']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __d('comments', 'Created'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $comment['Comment']['created']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php __d('comments', 'Modified'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $comment['Comment']['modified']; ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__d('comments', 'Edit Comment', true), array('action'=>'edit', $comment['Comment']['id'])); ?> </li>
		<li><?php echo $html->link(__d('comments', 'Delete Comment', true), array('action'=>'delete', $comment['Comment']['id']), null, sprintf(__d('comments', 'Are you sure you want to delete # %s?', true), $comment['Comment']['id'])); ?> </li>
		<li><?php echo $html->link(__d('comments', 'List Comments', true), array('action'=>'index')); ?> </li>
		<li><?php echo $html->link(__d('comments', 'New Comment', true), array('action'=>'add')); ?> </li>
		<li><?php echo $html->link(__d('comments', 'List Comments', true), array('controller'=> 'comments', 'action'=>'index')); ?> </li>
		<li><?php echo $html->link(__d('comments', 'New Parent Comment', true), array('controller'=> 'comments', 'action'=>'add')); ?> </li>
		<li><?php echo $html->link(__d('comments', 'List Users', true), array('controller'=> 'users', 'action'=>'index')); ?> </li>
		<li><?php echo $html->link(__d('comments', 'New User', true), array('controller'=> 'users', 'action'=>'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php __d('comments', 'Related Comments');?></h3>
	<?php if (!empty($comment['ChildComment'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php __d('comments', 'Id'); ?></th>
		<th><?php __d('comments', 'Comment Id'); ?></th>
		<th><?php __d('comments', 'Foreign Key'); ?></th>
		<th><?php __d('comments', 'User Id'); ?></th>
		<th><?php __d('comments', 'Model'); ?></th>
		<th><?php __d('comments', 'Approved'); ?></th>
		<th><?php __d('comments', 'Body'); ?></th>
		<th><?php __d('comments', 'Created'); ?></th>
		<th><?php __d('comments', 'Modified'); ?></th>
		<th class="actions"><?php __d('comments', 'Actions');?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($comment['ChildComment'] as $childComment):
			$class = null;
			if ($i++ % 2 == 0) {
				$class = ' class="altrow"';
			}
		?>
		<tr<?php echo $class;?>>
			<td><?php echo $childComment['id'];?></td>
			<td><?php echo $childComment['comment_id'];?></td>
			<td><?php echo $childComment['foreign_key'];?></td>
			<td><?php echo $childComment['user_id'];?></td>
			<td><?php echo $childComment['model'];?></td>
			<td><?php echo $childComment['approved'];?></td>
			<td><?php echo $childComment['body'];?></td>
			<td><?php echo $childComment['created'];?></td>
			<td><?php echo $childComment['modified'];?></td>
			<td class="actions">
				<?php echo $html->link(__d('comments', 'View', true), array('controller'=> 'comments', 'action'=>'view', $childComment['id'])); ?>
				<?php echo $html->link(__d('comments', 'Edit', true), array('controller'=> 'comments', 'action'=>'edit', $childComment['id'])); ?>
				<?php echo $html->link(__d('comments', 'Delete', true), array('controller'=> 'comments', 'action'=>'delete', $childComment['id']), null, sprintf(__d('comments', 'Are you sure you want to delete # %s?', true), $childComment['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $html->link(__d('comments', 'New Child Comment', true), array('controller'=> 'comments', 'action'=>'add'));?> </li>
		</ul>
	</div>
</div>
