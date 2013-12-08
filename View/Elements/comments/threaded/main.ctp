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
<div class="comments">
<?php
if ($allowAddByAuth):
	if ($isAddMode && $allowAddByAuth): ?>
		<h3><?php echo __d('comments', 'Add New Comment'); ?></h3>
		<?php
		echo $this->CommentWidget->element('form', array('comment' => (!empty($comment) ? $comment : 0)));
	else:
		if (empty($this->request->params[$adminRoute]) && $allowAddByAuth):
			echo $this->CommentWidget->link(__d('comments', 'ADD COMMENT'), am($url, array('comment' => 0)));
		endif;
	endif;
else: ?>
	<h3><?php echo __d('comments', 'Comments'); ?></h3>
	<?php
		echo sprintf(__d('comments', 'If you want to post comments, you need to login first.'), $this->Html->link(__d('comments', 'login'), array('controller' => 'users', 'action' => 'login', 'prefix' => $adminRoute, $adminRoute => false)));
endif;

//echo $this->CommentWidget->element('paginator');
echo $this->Tree->generate(${$viewComments}, array(
	'callback' => array($this->CommentWidget, 'treeCallback'),
	'model' => 'Comment',
	'class' => 'tree-block'));

?>
</div>
<?php echo $this->Html->image('/comments/img/indicator.gif', array('id' => 'busy-indicator',
 'style' => 'display:none;')); ?>
