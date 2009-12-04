<?php /* SVN FILE: $Id: admin_add.ctp 1061 2009-09-03 17:19:42Z renan.saddam $ */ ?>
<div class="comments form">
<?php echo $form->create('Comment');?>
	<fieldset>
 		<legend><?php __d('comments', 'Add Comment');?></legend>
	<?php
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
		<li><?php echo $html->link(__d('comments', 'List Comments', true), array('action'=>'index'));?></li>
		<li><?php echo $html->link(__d('comments', 'List Comments', true), array('controller'=> 'comments', 'action'=>'index')); ?> </li>
		<li><?php echo $html->link(__d('comments', 'New Parent Comment', true), array('controller'=> 'comments', 'action'=>'add')); ?> </li>
		<li><?php echo $html->link(__d('comments', 'List Users', true), array('controller'=> 'users', 'action'=>'index')); ?> </li>
		<li><?php echo $html->link(__d('comments', 'New User', true), array('controller'=> 'users', 'action'=>'add')); ?> </li>
	</ul>
</div>
