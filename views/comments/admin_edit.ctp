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
<div class="comments form">
<?php echo $form->create('Comment');?>
	<fieldset>
 		<legend><?php __d('comments', 'Edit Comment');?></legend>
	<?php
		echo $form->input('id');
		echo $form->input('comment_id');
		echo $form->input('foreign_key');
		echo $form->input('user_id');
		echo $form->input('model');
		echo $form->input('approved');
		echo $form->input('body');
	?>
	</fieldset>
<?php echo $form->end('Submit');?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $html->link(__d('comments', 'Delete', true), array('action'=>'delete', $form->value('Comment.id')), null, sprintf(__d('comments', 'Are you sure you want to delete # %s?', true), $form->value('Comment.id'))); ?></li>
		<li><?php echo $html->link(__d('comments', 'List Comments', true), array('action'=>'index'));?></li>
		<li><?php echo $html->link(__d('comments', 'List Comments', true), array('controller'=> 'comments', 'action'=>'index')); ?> </li>
		<li><?php echo $html->link(__d('comments', 'New Parent Comment', true), array('controller'=> 'comments', 'action'=>'add')); ?> </li>
		<li><?php echo $html->link(__d('comments', 'List Users', true), array('controller'=> 'users', 'action'=>'index')); ?> </li>
		<li><?php echo $html->link(__d('comments', 'New User', true), array('controller'=> 'users', 'action'=>'add')); ?> </li>
	</ul>
</div>
