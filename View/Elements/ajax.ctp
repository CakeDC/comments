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
<?php 
/**
 * @params $displayOptions array
 */
  if (empty($displayOptions)) {
	$displayOptions = array();
 }
?>
<?php if (isset($messageTxt)): ?>
<div class="message">
	<?php echo $messageTxt; ?>
</div>
<?php endif ;?>
<?php
	if (isset($ajaxMode)) :
		if (!empty($redirect)) :
			if (isset($redirect['#'])) {
				unset($redirect['#']);
			}

			$url = Router::parse($this->request->here);
			$url = array_merge($url, $url['named'], $url['pass']);
			unset($url['named']);
			unset($url['pass']);
			if (isset($url['comment'])) {
				unset($url['comment']);
			}

			echo $this->Html->scriptBlock('setTimeout(function () {' . $this->Js->request(Router::url($url), array('method' => 'get', 'update' => $this->CommentWidget->globalParams['target'])) . '}, 1500);');
		else:
			echo $this->CommentWidget->display($displayOptions);
		endif;
	else:
		echo $this->CommentWidget->display($displayOptions);
	endif;
