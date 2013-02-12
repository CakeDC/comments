<?php
/**
 * Copyright 2009 - 2013, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2009 - 2013, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<div class="comments view">
<h2><?php  echo __d('comments', 'Comment');?></h2>
	<dl><?php $i = 0; $class = ' class="altrow"';?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __d('comments', 'Id'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $comment['Comment']['id']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __d('comments', 'Parent Comment'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($comment['ParentComment']['id'], array('controller'=> 'comments', 'action'=>'view', $comment['ParentComment']['id'])); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __d('comments', 'Commented On'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($comment['CommentedOn']['id'], array('controller'=> 'users', 'action'=>'view', $comment['CommentedOn']['id'])); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __d('comments', 'User'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $this->Html->link($comment['User']['id'], array('controller'=> 'users', 'action'=>'view', $comment['User']['id'])); ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __d('comments', 'Model'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $comment['Comment']['model']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __d('comments', 'Approved'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $comment['Comment']['approved']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __d('comments', 'Body'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $comment['Comment']['body']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __d('comments', 'Created'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $comment['Comment']['created']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __d('comments', 'Modified'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $comment['Comment']['modified']; ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__d('comments', 'Edit Comment'), array('action'=>'edit', $comment['Comment']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__d('comments', 'Delete Comment'), array('action'=>'delete', $comment['Comment']['id']), null, sprintf(__d('comments', 'Are you sure you want to delete # %s?'), $comment['Comment']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__d('comments', 'List Comments'), array('action'=>'index')); ?> </li>
		<li><?php echo $this->Html->link(__d('comments', 'New Comment'), array('action'=>'add')); ?> </li>
		<li><?php echo $this->Html->link(__d('comments', 'List Comments'), array('controller'=> 'comments', 'action'=>'index')); ?> </li>
		<li><?php echo $this->Html->link(__d('comments', 'New Parent Comment'), array('controller'=> 'comments', 'action'=>'add')); ?> </li>
		<li><?php echo $this->Html->link(__d('comments', 'List Users'), array('controller'=> 'users', 'action'=>'index')); ?> </li>
		<li><?php echo $this->Html->link(__d('comments', 'New User'), array('controller'=> 'users', 'action'=>'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __d('comments', 'Related Comments');?></h3>
	<?php if (!empty($comment['ChildComment'])):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __d('comments', 'Id'); ?></th>
		<th><?php echo __d('comments', 'Comment Id'); ?></th>
		<th><?php echo __d('comments', 'Foreign Key'); ?></th>
		<th><?php echo __d('comments', 'User Id'); ?></th>
		<th><?php echo __d('comments', 'Model'); ?></th>
		<th><?php echo __d('comments', 'Approved'); ?></th>
		<th><?php echo __d('comments', 'Body'); ?></th>
		<th><?php echo __d('comments', 'Created'); ?></th>
		<th><?php echo __d('comments', 'Modified'); ?></th>
		<th class="actions"><?php echo __d('comments', 'Actions');?></th>
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
				<?php echo $this->Html->link(__d('comments', 'View'), array('controller'=> 'comments', 'action'=>'view', $childComment['id'])); ?>
				<?php echo $this->Html->link(__d('comments', 'Edit'), array('controller'=> 'comments', 'action'=>'edit', $childComment['id'])); ?>
				<?php echo $this->Html->link(__d('comments', 'Delete'), array('controller'=> 'comments', 'action'=>'delete', $childComment['id']), null, sprintf(__d('comments', 'Are you sure you want to delete # %s?'), $childComment['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__d('comments', 'New Child Comment'), array('controller'=> 'comments', 'action'=>'add'));?> </li>
		</ul>
	</div>
</div>
