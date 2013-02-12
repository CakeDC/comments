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
<h3><?php echo __d('comments', 'Filter comments'); ?></h3>
<?php echo $this->Form->create('Comment', array(
	'url' => array('plugin' => 'comments', 'admin' => true, 'controller' => 'comments', 'action' => 'index'),
	'class' => 'finder-form',
	'id' => 'SearchForm')); ?>
<div class="content-block clearfix">
	<?php echo $this->Form->input('approved', array(
		'label' => __d('comments', 'Approved'),
		'class' => 'small',
		'empty' => __d('comments', '...select...'),
		'options' => array(
			0 => __d('comments', 'Not Approved'),
			1 => __d('comments', 'Approved')),
		'div' => array('class' => 'left'),
	)); ?>

	<?php echo $this->Form->input('is_spam', array(
		'label' => __d('comments', 'spam state'),
		'class' => 'small',
		'empty' => __d('comments', '...select...'),
		'options' => array(
			'clean' => __d('comments', 'Clean'),
			'ham' => __d('comments', 'Ham'),
			'spammanual' => __d('comments', 'Manual Spam'),
			'spam' => __d('comments', 'Spam')),
		'div' => array('class' => 'left spaced'),
	)); ?>
</div>
<?php echo $this->Form->submit(__d('comments', 'Search'), array(
	'class' => 'button-search', 'div' => array('class' => 'buttons'))); ?>
<?php echo $this->Form->end(null); ?>