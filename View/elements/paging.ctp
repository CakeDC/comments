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

if (empty($Paginator) || !is_a($Paginator, 'PaginatorHelper')) {
	$Paginator = $this->Paginator;
}
?>
<?php if ($Paginator->hasNext() || $Paginator->hasPrev()): ?>
	<div class="paging">
		<?php echo $Paginator->prev('< prev', array(), null, array('class' => 'disabled'));?>
		<?php echo $Paginator->numbers(array('separator' => ' '));?>
		<?php echo $Paginator->next('next >', array(), null, array('class' => 'disabled'));?>
	</div>
<?php endif; ?>