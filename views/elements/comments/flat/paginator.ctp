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
<?php
	$pager = $this->Paginator;
	if ($commentWidget->globalParams['target']) {
		$pager->options(array_merge(
			array('url' => $commentWidget->prepareUrl($url)),
			$commentWidget->globalParams['ajaxOptions']));
	} else {
		$pager->options(array('url' => $url));
	}
	$paging = $pager->params('Comment');
?>

<?php if (!empty(${$viewComments})): ?>
	<div class="paging">
		<?php echo $pager->prev('<< '.__d('comments', 'Most Recent', true), array(), null, array('class'=>'disabled'));?>
	 | 	<?php echo $pager->numbers();?>
		<?php echo $pager->next(__d('comments', 'Oldest', true).' >>', array(), null, array('class'=>'disabled'));?>
	</div>
<?php endif; ?>
