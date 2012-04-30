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

$this->loadHelper('Paginator');
if ($this->CommentWidget->globalParams['target']) {
	$this->Paginator->options(array_merge(
		array('url' => $this->CommentWidget->prepareUrl($url)),
		$this->CommentWidget->globalParams['ajaxOptions']));
} else {
	$this->Paginator->options(array('url' => $url));
}
$paging = $this->Paginator->params('Comment');
?>

<?php if (!empty(${$viewComments})): ?>
	<div class="paging">
		<?php echo $this->Paginator->prev('<< '.__d('comments', 'Most Recent'), array(), null, array('class'=>'disabled'));?>
	 | 	<?php echo $this->Paginator->numbers();?>
		<?php echo $this->Paginator->next(__d('comments', 'Oldest').' >>', array(), null, array('class'=>'disabled'));?>
	</div>
<?php endif; ?>
