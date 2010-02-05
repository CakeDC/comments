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
	$_actionLinks = array();
	if (!empty($displayUrlToComment)) {
		$_actionLinks[] = sprintf('<a href="%s">%s</a>', $urlToComment . '/' . $comment['Comment']['slug'], __d('comments', 'View', true));
	}

	if (!empty($isAuthorized)) {
		$_actionLinks[] = $commentWidget->link(__d('comments', 'Reply', true), array_merge($url, array('comment' => $comment['Comment']['id'], '#' => 'comment' . $comment['Comment']['id'])));
		if (!empty($isAdmin)) {
			if (empty($comment['Comment']['approved'])) {
				$_actionLinks[] = $commentWidget->link(__d('comments', 'Publish', true), array_merge($url, array('comment' => $comment['Comment']['id'], 'comment_action' => 'toggleApprove', '#' => 'comment' . $comment['id'])));
			} else {
				$_actionLinks[] = $commentWidget->link(__d('comments', 'Unpublish', true), array_merge($url, array('comment' => $comment['Comment']['id'], 'comment_action' => 'toggleApprove', '#' => 'comment' . $comment['Comment']['id'])));
			}
		}
	}

	$_userLink = $comment[$userModel]['username'];

?>
<div class="comment">
	<div class="header">
		<a name="comment<?php echo $comment['Comment']['id'];?>"><?php echo $comment['Comment']['title'];?></a>
		<span style="float: right"><?php echo join('&nbsp;', $_actionLinks);?></span>
		<br/>
		<span><?php echo $_userLink; ?> &nbsp; <?php __d('comments', 'posted'); ?> &nbsp; <?php echo $time->timeAgoInWords($comment['Comment']['created']); ?></span>
	</div>
	<div class="body"><?php echo $cleaner->bbcode2js($comment['Comment']['body']);?></div>
</div>