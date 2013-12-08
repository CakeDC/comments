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
<h2><?php echo __d('comments', 'Comments');?></h2>

<?php if (isset($searchEnabled)) : ?>
	<div class="finder">
		<?php echo $this->element('comments/finder'); ?>
	</div>
<?php endif;?>

<ul>
	<li><?php echo $this->Html->link(__d('comments', 'Filter spam comments'), array('action' => 'index', 'spam'));?></li>
	<li><?php echo $this->Html->link(__d('comments', 'Filter good comments'), array('action' => 'index', 'clean'));?></li>
</ul>

<?php echo $this->Form->create('Comment',array('id' => 'CommentForm', 'name' => 'CommentForm', 'url' => Set::merge(array('action' => 'process'), $this->request->params['named']) ));?>
<?php echo $this->Form->input('Comment.action', array(
				'type' => 'select', 
				'options' => array(
					'ham' => __d('comments', 'Mark as ham'),
					'spam' => __d('comments', 'Mark as spam'),
					'delete' => __d('comments', 'Delete'),
					'approve' => __d('comments', 'Approve'),
					'disapprove' => __d('comments', 'Dispprove'))));?>
<?php echo $this->Form->submit('Process', array('name' => 'process'));?>

<table cellpadding="0" cellspacing="0">
	<tr>
		<th><?php echo $this->Paginator->sort('name');?></th>
        <th> Body </th>
        <th><?php echo $this->Paginator->sort('author_name');?></th>
        <th><?php echo $this->Paginator->sort('author_email');?></th>
        <th><?php echo $this->Paginator->sort('author_url');?></th>
        <th><?php echo $this->Paginator->sort('created');?></th>
        <th><?php echo $this->Paginator->sort('is_spam');?></th>
        <th><?php echo $this->Paginator->sort('approved');?></th>
		<th><?php echo __d('comments', 'Select...');?> <input id="mainCheck" style="width: 100%;" type="checkbox" onclick="$('.cbox').each (function (id,f) {$('#'+this.id).attr('checked', !!$('#mainCheck').attr('checked'))})"> </th>
		<th class="actions"><?php echo __d('comments', 'Actions');?></th>
	</tr>
	<?php
	$i = 0;	
	foreach ($comments as $comment) :
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
		<tr<?php echo $class;?>>
			<td>
				<?php echo h($comment['Comment']['title']); ?>
			</td>
			<td>
				<div class="hidden"><?php echo h($comment['Comment']['body']); ?> </div>
                <?php echo $this->Html->link(__('Hide'), '#', array('class' => 'toggle')); ?>
			</td>
			<td>
				<?php echo h($comment['Comment']['author_name']); ?>
			</td>
			<td>
				<?php echo h($comment['Comment']['author_email']); ?>
			</td>
			<td>
				<?php echo h($comment['Comment']['author_url']); ?>
			</td>
			<td>
				<?php echo $comment['Comment']['created']; ?>
			</td>
			<td>
				<?php echo $comment['Comment']['is_spam']; ?>
			</td>
			<td>
				<?php echo ($comment['Comment']['approved'] ? __d('comments', 'Yes') : __d('comments', 'No')); ?>
			</td>
			<td class="comment-check">
				<?php
					echo $this->Form->input('Comment.' . $comment['Comment']['id'], array(
						'label' => false,
						'div' => false,
						'class' => 'cbox',
						'type' => 'checkbox'));
					?>
			</td>
			<td class="actions">
				<?php echo $this->Html->link(__d('comments', 'Approve'), array('action' => 'approve', $comment['Comment']['id'])); ?>
				<?php echo $this->Html->link(__d('comments', 'Mark as spam'), array('action' => 'spam', $comment['Comment']['id'])); ?>
				<?php echo $this->Html->link(__d('comments', 'Mark as ham'), array('action' => 'ham', $comment['Comment']['id'])); ?>
				<?php echo $this->Html->link(__d('comments', 'Disapprove'), array('action' => 'disapprove', $comment['Comment']['id'])); ?>
				<?php echo $this->Html->link(__d('comments', 'View'), array('action' => 'view', $comment['Comment']['id'])); ?>
				<?php echo $this->Html->link(__d('comments', 'Edit'), array('action' => 'edit', $comment['Comment']['id'])); ?>
				<?php echo $this->Html->link(__d('comments', 'Delete'), array('action' => 'delete', $comment['Comment']['id']), null, sprintf(__d('comments', 'Are you sure you want to delete # %s?'), $comment['Comment']['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
</table>
<?php echo $this->Form->end(); ?>

<?php echo $this->element('paging'); ?>

<script type="text/javascript">
    $("td div.hidden").show();
    $("td a.toggle").click(function(event) {
        $this = $(this);
        if ($this[0].innerHTML == 'Show') {
            $this[0].innerHTML = 'Hide';
        } else {
            $this[0].innerHTML = 'Show';
        }
        $(this).parent().find("div.hidden").toggle();
        event.preventDefault();
    });


</script>