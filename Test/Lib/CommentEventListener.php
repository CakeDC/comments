<?php
/*
 * CommentEventListener.php
 */

App::uses('CakeEventListener', 'Event');

class CommentEventListener implements CakeEventListener {


/**
 * Register events
 */
	public function implementedEvents() {
		return array(
			'Behavior.Commentable.beforeCreateComment' => 'handleBeforeCreateComment',
			'Behavior.Commentable.afterCreateComment' => 'handleAfterCreateComment',
		);
	}

/**
 * Test event listener for before create comment
 *
 * @param CakeEvent $event
 * @return mixed
 */
	public function handleBeforeCreateComment(CakeEvent $event) {
		$data = $event->data;
		$data['Comment']['title'] = 'Changed in beforeComment!';
		return $data;
	}

/**
 * Test event listener for before create comment
 *
 * @param CakeEvent $event
 * @return mixed
 */
	public function handleAfterCreateComment(CakeEvent $event) {
		$data = $event->data;
		$model = $event->subject();
		$comment = $model->Comment->read(null, $data['Comment']['id']);
		$comment['Comment']['body'] = 'Changed in afterComment!';
		$model->Comment->save($comment);
		return $data;
	}
}