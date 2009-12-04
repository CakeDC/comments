<?php /* SVN FILE: $Id: paginator.ctp 1608 2009-11-04 09:11:08Z skie $ */ ?>
<?php
	if ($commentWidget->globalParams['target']) {
		$pager = $jpaginator;
		$pager->options(array('url' => $commentWidget->prepareUrl($url),'update' => $commentWidget->globalParams['target']));
		$pager->setDefaultModel('Comment');
	} else {
		$pager = $paginator;
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
