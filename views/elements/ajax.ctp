<?php if (isset($messageTxt)): ?>
<div class="message">
	<?php echo $messageTxt; ?>
</div>
<?php endif ;?>
<?php 
	if (isset($ajaxMode)): 
		if (isset($redirect)):
			if (isset($redirect['#'])) {
				unset($redirect['#']);
			}

			echo $this->Html->scriptBlock('setTimeout(function () {' . $js->request(null, array('method' => 'get', 'update' => $commentWidget->globalParams['target'])) . '}, 1500);');
		endif;
	else:
		echo $commentWidget->display();
	endif;
?>
