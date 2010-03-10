<h3><?php __d('comments', 'Filter comments'); ?></h3>
<?php echo $form->create('Comment', array(
	'url' => array('plugin' => 'comments', 'admin' => true, 'controller' => 'comments', 'action' => 'index'),
	'class' => 'finder-form',
	'id' => 'SearchForm')); ?>
<div class="content-block clearfix">
	<?php echo $form->input('approved', array(
		'label' => __d('cakedc', 'Approved', true),
		'class' => 'small',
		'empty' => __d('cakedc', '...select...', true),
		'options' => array(0 => 'not aproved', 1 => 'approved'),
		'div' => array('class' => 'left'),
	)); ?>

	<?php echo $form->input('is_spam', array(
		'label' => __d('cakedc', 'spam state', true),
		'class' => 'small',
		'empty' => __d('cakedc', '...select...', true),
		'options' => array('clean' => 'clean', 'ham' => 'ham', 'manualspam' => 'manualspam', 'spam' => 'spam'),
		'div' => array('class' => 'left spaced'),
	)); ?>
</div>
<?php echo $form->submit(__d('cakedc', 'Search', true), array(
	'class' => 'button-search', 'div' => array('class' => 'buttons'))); ?>
<?php echo $form->end(null); ?>