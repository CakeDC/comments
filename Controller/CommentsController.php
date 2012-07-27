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
App::uses('CommentsAppController', 'Comments.Controller');
/**
 * Comments Controller
 *
 * @package comments
 * @subpackage comments.controllers
 */

/**
 * @property Comment Comment
 * @property PrgComponent Prg
 * @property SessionComponent  Session
 * @property RequestHandlerComponent RequestHandler
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
		'Session');

/**
 * Helpers
 *
 * @var array
 */
	public $helpers = array('Text', 'Time');

/**
 * Uses
 *
 * @var array
 */
	public $uses = array('Comments.Comment');

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
		$this->presetVars = array(
			array('field' => 'approved', 'type' => 'value'),
			array('field' => 'is_spam', 'type' => 'value'));

		$this->Comment->recursive = 0;
		$this->Comment->bindModel(array(
			'belongsTo' => array(
				'UserModel'  => array(
					'className' => 'Users.User',
					'foreignKey' => 'user_id'))), false);
		$conditions = array();

		if (App::import('Component', 'Search.Prg')) {
			$this->Comment->Behaviors->load('Search.Searchable');
			$this->Comment->filterArgs = array(
				array('name' => 'is_spam', 'type' => 'value'),
				array('name' => 'approved', 'type' => 'value'));
			$this->Prg = new PrgComponent($this->Components, array());
			$this->Prg->initialize($this);
			$this->Prg->commonProcess();
			$conditions = $this->Comment->parseCriteria($this->passedArgs);
			$this->set('searchEnabled', true);
		}
		

		$this->Paginator->settings = array(
			'Comment' => array(
				'conditions' => $conditions,
				'contain' => array('UserModel'),
				'order' => 'Comment.created DESC'));
		if ($type == 'spam') {
			$this->Paginator->settings['Comment']['conditions'] = array('Comment.is_spam' => array('spam', 'spammanual'));
		} elseif ($type == 'clean') {
			$this->Paginator->settings['Comment']['conditions'] = array('Comment.is_spam' => array('ham', 'clean'));
		}

		$this->set('comments', $this->Paginator->paginate('Comment'));
	}


/**
 * Processes mailbox folders
 *
 * @param string $folder Name of the folder to process
 * @return void
 */
	public function admin_process($type = null) {
		$addInfo = '';
		if (!empty($this->request->data)) {
			try {
				$message = $this->Comment->process($this->request->data['Comment']['action'], $this->request->data);
			} catch (Exception $ex) {
				$message = $ex->getMessage();
			}
			$this->Session->setFlash($message);
		}
		$url = array('plugin'=>'comments', 'action' => 'index', 'admin' => true);
		$url = Set::merge($url, $this->request->params['pass']);
		$this->redirect(Set::merge($url, $this->request->params['named']));
	}

/**
 * Admin mark comment as spam
 *
 * @param string UUID
 */
	public function admin_spam($id) {
		$this->Comment->id = $id;
		if (!$this->Comment->exists()) {
			$this->Session->setFlash(__d('comments', 'Wrong comment id'));
		} elseif ($this->Comment->markAsSpam()) {
			$this->Session->setFlash(__d('comments', 'Antispam system informed about spam message.'));
		} else {
			$this->Session->setFlash(__d('comments', 'Error appear during save.'));
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
			$this->Session->setFlash(__d('comments', 'Wrong comment id'));
		} elseif ($this->Comment->markAsHam()) {
			$this->Session->setFlash(__d('comments', 'Antispam system informed about ham message.'));
		} else {
			$this->Session->setFlash(__d('comments', 'Error appear during save.'));
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
			$this->Session->setFlash(__d('comments', 'Invalid Comment.'));
			return $this->redirect(array('action'=>'index'));
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
        if (!$this->Comment->exists()) {
			$this->Session->setFlash(__d('comments', 'Invalid id for Comment'));
		} elseif ($this->Comment->delete()) {
			$this->Session->setFlash(__d('comments', 'Comment deleted'));
		} else {
			$this->Session->setFlash(__d('comments', 'Impossible to delete the Comment. Please try again.'));
		}
		$this->redirect(array('action'=>'index'));
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
			$this->Session->setFlash(__d('comments', 'Invalid Comment.'));
			return $this->redirect(array('action'=>'index'));
		}
		$this->set('comment', $comment);
	}

/**
 * Request comments
 *
 * @param string user UUID
 * @return void
 */
	public function requestForUser($userId = null, $amount = 5) {
		if (!$this->RequestHandler->isAjax() && !$this->_isRequestedAction()) {
			return $this->cakeError('404');
		}

		$conditions = array('Comment.user_id' => $userId);
		if (!empty($this->request->params['named']['model'])) {
			$conditions['Comment.model'] = $this->request->params['named']['model'];
		}
		$conditions['Comment.is_spam'] = array('ham','clean');
		$this->paginate = array(
			'conditions' => $conditions,
			'order' => 'Comment.created DESC',
			'limit' => $amount);

		$this->set('comments', $this->paginate());
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
