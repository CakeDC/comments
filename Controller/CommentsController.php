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
App::uses('CommentsAppController', 'Comments.Controller');

/**
 * Comments Controller
 *
 * @package comments
 * @subpackage comments.controllers
 *
 * @property Comment $Comment
 * @property PrgComponent $Prg
 * @property SessionComponent  $Session
 * @property RequestHandlerComponent $RequestHandler
 * @property CommentsComponent  $Comments
 */
class CommentsController extends CommentsAppController {

/**
 * Name
 *
 * @var string
 */
	public $name = 'Comments';

/**
 * Components
 *
 * @var array
 */
	public $components = array(
		'RequestHandler',
		'Paginator',
		'Session',
		'Comments.Comments' => array(
			'active' => false,
		),
	);

/**
 * Helpers
 *
 * @var array
 */
	public $helpers = array(
		'Text',
		'Time'
	);

/**
 * Uses
 *
 * @var array
 */
	public $uses = array(
		'Comments.Comment'
	);

/**
 * Preset for search views
 *
 * @var array
 */
	public $presetVars = array();

/**
 * Admin index action
 *
 * @param string
 * @return void
 */
	public function admin_index($type = '') {
		$this->Comment->recursive = 0;
		$this->Comment->bindModel(array(
			'belongsTo' => array(
				'UserModel' => array(
					'className' => 'Users.User',
					'foreignKey' => 'user_id'
				)
			)
		), false);

		$conditions = $this->_adminIndexSearch();

		$this->Paginator->settings = array(
			'Comment' => array(
				'conditions' => $conditions,
				'contain' => array(
					'UserModel'
				),
				'order' => 'Comment.created DESC'
			)
		);

		if ($type == 'spam') {
			$this->Paginator->settings['Comment']['conditions'] = array('Comment.is_spam' => array('spam', 'spammanual'));
		} elseif ($type == 'clean') {
			$this->Paginator->settings['Comment']['conditions'] = array('Comment.is_spam' => array('ham', 'clean'));
		}

		$this->set('comments', $this->Paginator->paginate('Comment'));
	}

/**
 * Checks if the CakeDC Search plugin is present and if yes loads the PRG component
 *
 * @return array Conditions for the pagination
 */
	protected function _adminIndexSearch() {
		$conditions = array();
		if (class_exists('PrgComponent')) {
			$this->Comment->Behaviors->load('Search.Searchable');
			$this->Comment->filterArgs = array(
				array('field' => 'is_spam', 'name' => 'is_spam', 'type' => 'value'),
				array('field' => 'approved', 'name' => 'approved', 'type' => 'value')
			);

			$this->presetVars = true;
			$this->Prg = new PrgComponent($this->Components, array());
			$this->Prg->initialize($this);
			$this->Prg->commonProcess();
			$conditions = $this->Comment->parseCriteria($this->passedArgs);
			$this->set('searchEnabled', true);
		}
		return $conditions;
	}

/**
 * Processes mailbox folders
 *
 * @param string $folder Name of the folder to process
 * @return void
 */
	public function admin_process($type = null) {
		if (!empty($this->request->data)) {
			try {
				$message = $this->Comment->process($this->request->data['Comment']['action'], $this->request->data);
			} catch (Exception $e) {
				$message = $e->getMessage();
			}
			$this->Comments->flash($message);
		}
		$url = array('plugin' => 'comments', 'action' => 'index', 'admin' => true);
		$url = Hash::merge($url, $this->request->params['pass']);
		$this->redirect(Hash::merge($url, $this->request->params['named']));
	}

/**
 * Admin mark comment as spam
 *
 * @param string UUID
 */
	public function admin_spam($id) {
		$this->Comment->id = $id;
		if (!$this->Comment->exists()) {
			$this->Comments->flash(__d('comments', 'Wrong comment id'));
		} elseif ($this->Comment->markAsSpam()) {
			$this->Comments->flash(__d('comments', 'Antispam system informed about spam message.'));
		} else {
			$this->Comments->flash(__d('comments', 'Error appear during save.'));
		}
		$this->redirect(array('action' => 'index'));
	}

/**
 * Admin mark comment as ham
 *
 * @param string UUID
 */
	public function admin_ham($id) {
		$this->Comment->id = $id;
		if (!$this->Comment->exists()) {
			$this->Comments->flash(__d('comments', 'Wrong comment id'));
		} elseif ($this->Comment->markAsHam()) {
			$this->Comments->flash(__d('comments', 'Antispam system informed about ham message.'));
		} else {
			$this->Comments->flash(__d('comments', 'Error appear during save.'));
		}
		$this->redirect(array('action' => 'index'));
	}

/**
 * Admin View action
 *
 * @param string UUID
 */
	public function admin_view($id = null) {
		$this->Comment->id = $id;
		$comment = $this->Comment->read(null, $id);
		if (empty($comment)) {
			$this->Comments->flash(__d('comments', 'Invalid Comment.'));
			return $this->redirect(array('action' => 'index'));
		}
		$this->set('comment', $comment);
	}

/**
 * Admin delete action
 *
 * @param string UUID
 */
	public function admin_delete($id = null) {
		$this->Comment->id = $id;
		$this->Comment->recursive = -1;
		if (!$this->Comment->exists()) {
			$this->Comments->flash(__d('comments', 'Invalid id for Comment'));
		} elseif ($this->Comment->delete($id, false)) {
			$this->Comments->flash(__d('comments', 'Comment deleted'));
		} else {
			$this->Comments->flash(__d('comments', 'Impossible to delete the Comment. Please try again.'));
		}
		$this->redirect(array('action' => 'index'));
	}

/**
 * View action
 *
 * @param string UUID
 */
	public function view($id = null) {
		$this->Comment->id = $id;
		$comment = $this->Comment->read(null, $id);
		if (empty($comment)) {
			$this->Comments->flash(__d('comments', 'Invalid Comment.'));
			return $this->redirect(array('action' => 'index'));
		}
		$this->set('comment', $comment);
	}

/**
 * Request comments
 *
 * @param string $userId UUID
 * @param int $amount
 * @return void
 */
	public function requestForUser($userId = null, $amount = 5) {
		if (!$this->request->is('ajax') && !$this->request->is('requested')) {
			return $this->cakeError('404');
		}

		$conditions = array('Comment.user_id' => $userId);
		if (!empty($this->request->params['named']['model'])) {
			$conditions['Comment.model'] = $this->request->params['named']['model'];
		}
		$conditions['Comment.is_spam'] = array('ham','clean');
		$this->Paginator->settings = array(
			'conditions' => $conditions,
			'order' => 'Comment.created DESC',
			'limit' => $amount
		);

		$this->set('comments', $this->Paginator->paginate());
		$this->set('userId', $userId);

		$this->viewPath = 'elements/comments';
		$this->render('comment');
	}

/**
 * Returns true if the action was called with requestAction()
 *
 * @return boolean
 */
	protected function _isRequestedAction() {
		return array_key_exists('requested', $this->request->params);
	}
}
