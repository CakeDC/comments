<?php
/**
 * Copyright 2009-2010, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2009-2010, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<h3><?php __d('comments', 'Filter comments'); ?></h3>
<?php echo $this->Form->create('Comment', array(
	'url' => array('plugin' => 'comments', 'admin' => true, 'controller' => 'comments', 'action' => 'index'),
	'class' => 'finder-form',
	'id' => 'SearchForm')); ?>
<div class="content-block clearfix">
	<?php echo $this->Form->input('approved', array(
		'label' => __d('comments', 'Approved', true),
		'class' => 'small',
		'empty' => __d('comments', '...select...', true),
		'options' => array(
			0 => __d('comments', 'Not Approved', true),
			1 => __d('comments', 'Approved', true)),
		'div' => array('class' => 'left'),
	)); ?>

	<?php echo $this->Form->input('is_spam', array(
		'label' => __d('comments', 'spam state', true),
		'class' => 'small',
		'empty' => __d('comments', '...select...', true),
		'options' => array(
			'clean' => __d('comments', 'Clean', true),
			'ham' => __d('comments', 'Ham', true),
			'spammanual' => __d('comments', 'Manual Spam', true),
			'spam' => __d('comments', 'Spam', true)),
		'div' => array('class' => 'left spaced'),
	)); ?>
</div>
<?php echo $this->Form->submit(__d('comments', 'Search', true), array(
	'class' => 'button-search', 'div' => array('class' => 'buttons'))); ?>
<?php echo $this->Form->end(null); ?>