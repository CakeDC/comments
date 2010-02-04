<?php
	$pager = $this->Paginator;
	if ($commentWidget->globalParams['target']) {
		$pager->options(array('url' => $commentWidget->prepareUrl($url),'update' => $commentWidget->globalParams['target']));
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
