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
<div class="comments form">
<?php echo $this->Form->create('Comment');?>
	<fieldset>
 		<legend><?php echo __d('comments', 'Add Comment');?></legend>
	<?php
		echo $this->Form->input('comment_id');
		echo $this->Form->input('foreign_key');
		echo $this->Form->input('user_id');
		echo $this->Form->input('model');
		echo $this->Form->input('approved');
		echo $this->Form->input('body');
	?>
	</fieldset>
<?php echo $this->Form->end('Submit');?>
</div>
<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__d('comments', 'List Comments'), array('action'=>'index'));?></li>
		<li><?php echo $this->Html->link(__d('comments', 'List Comments'), array('controller'=> 'comments', 'action'=>'index')); ?> </li>
		<li><?php echo $this->Html->link(__d('comments', 'New Parent Comment'), array('controller'=> 'comments', 'action'=>'add')); ?> </li>
		<li><?php echo $this->Html->link(__d('comments', 'List Users'), array('controller'=> 'users', 'action'=>'index')); ?> </li>
		<li><?php echo $this->Html->link(__d('comments', 'New User'), array('controller'=> 'users', 'action'=>'add')); ?> </li>
	</ul>
</div>
