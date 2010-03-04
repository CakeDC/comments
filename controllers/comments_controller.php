<?php
/**
 * CakePHP Comments
 *
 * Copyright 2009 - 2010, Cake Development Corporation
 *                        1785 E. Sahara Avenue, Suite 490-423
 *                        Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright 2009 - 2010, Cake Development Corporation
 * @link      http://github.com/cakedc/comments/
 * @package   plugins.comments
 * @license   MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Short description for class.
 *
 * @package		comments
 * @subpackage	controllers
 */

class CommentsController extends CommentsAppController {

/**
 * Name
 *
 * @var string
 * @access public
 */
	public $name = 'Comments';

/**
 * Components
 *
 * @var array
 * @access public
 */
	public $components = array('RequestHandler');

/**
 * Helpers
 *
 * @var array
 * @access public
 */
	public $helpers = array('Text', 'Time');

/**
 * Uses
 *
 * @var array
 * @access public
 */
	public $uses = array('Comments.Comment');

/**
 * Admin index action
 *
 * @TODO either hardcode spam/ham possible values (remove option from Commentable) or find a way to use these values here
 * @param string
 * @access public
 */
	public function admin_index($type = 'spam') {
		$this->Comment->recursive = 0;
		$this->Comment->bindModel(array(
			'belongsTo' => array(
				'UserModel'  => array(
					'className' => 'Users.User', 
					'foreignKey' => 'user_id'))));
		$conditions = array();
		if (App::import('Component', 'Search.Prg')) {
			$this->Prg = new PrgComponent();
			$this->Prg->initialize($this);
			$this->Comment->Behaviors->attach('Search.Searchable');
			$this->Comment->filterArgs = array(
				array('name' => 'is_spam', 'type' => 'value'),
				array('name' => 'approved', 'type' => 'value'),
			);
			$this->Prg->commonProcess();
			$this->Comment->parseCriteria($this->passedArgs);
		}

		$this->paginate['Comment'] = array(
			'conditions' => $conditions,
			'contain' => array('UserModel'),
			'order' => 'Comment.created DESC'); 
		if ($type == 'spam') {
			$this->paginate['Comment']['conditions'] = array('Comment.is_spam' => array('spam', 'manualspam'));
		} elseif ($type == 'clean') {
			$this->paginate['Comment']['conditions'] = array('Comment.is_spam' => array('ham', 'clean'));
		}			
		$this->set('comments', $this->paginate('Comment'));
	}


/**
 * Processes mailbox folders
 *
 * @param string $folder Name of the folder to process
 */
	public function admin_process($type = null) {
		$addInfo = '';
		if (!empty($this->data)) {
			if (!empty($this->params['form']['delete']) || (!empty($this->data['Comment']['action']) && $this->data['Comment']['action'] == 'delete')) { //delete mails
				$keys = array_keys($this->data['Comment']);
				foreach ($keys as $id) {
					$value = $this->data['Comment'][$id];
					if (is_string($id) & strlen($id)==36 && ($value)) {
						$result = $this->Comment->delete($id);
						if(!$result) {
							$addInfo = __d('comments', 'Some errors appear during excution.', true);
						}
					}
				}
				$this->Session->setFlash(__d('comments', 'Comments removed.',true) . ' ' . $addInfo);
			} elseif ((!empty($this->data['Comment']['action']) && in_array($this->data['Comment']['action'], array('spam', 'ham', 'approve', 'disapprove')))) {
				$keys = array_keys($this->data['Comment']);
				$action = $this->data['Comment']['action'];
				foreach ($keys as $id) {
					$value = $this->data['Comment'][$id];
					if (is_string($id) & strlen($id)==36 && ($value)) {
						$this->Comment->recursive = -1;
						$comment = $this->Comment->read(null, $id);
						if ($action == 'spam' || $action == 'ham') {
							$modelName = r('.', '', $comment['Comment']['model']);
							if (!isset(${$modelName})) {
								${$modelName} = ClassRegistry::init($comment['Comment']['model']);
							}
							$settings = array('permalink' => ${$modelName}->permalink($comment['Comment']['foreign_key']));
							$this->Comment->permalink = ${$modelName}->permalink($comment['Comment']['foreign_key']);
						}
						switch ($action) {
							case 'ham':
								$result = $this->Comment->markAsHam($id);
								break;
							case 'spam':
								$result = $this->Comment->markAsSpam($id);
								break;
							case 'approve':
								$result = $this->Comment->saveField('approved', 1);
								break;
							case 'disapprove':
								$result = $this->Comment->saveField('approved', 0);
								break;
						}
						switch($result) {
							case false:
							case 'invalid':
							case 'error':
								$addInfo = __d('comments', 'Some errors appear during excution.', true);
								break;
						}
					}
				}
				$this->Session->setFlash(__d('comments', 'Operation was performed. ',true) . ' ' . $addInfo);
			}
		}
		$url = array('plugin'=>'comments', 'action' => 'index', 'admin' => true);
		$url = Set::merge($url, $this->params['pass']);
		$this->redirect(Set::merge($url, $this->params['named']));
	}
	
/**
 * Admin mark comment as spam
 *
 * @param string UUID
 * @access public
 */
	public function admin_spam($id) {
		$this->Comment->id = $id;
		if (!$this->Comment->exists(true)) {
			$this->Session->setFlash(__d('comments', 'Wrong comment id', true));
		} elseif ($this->Comment->markAsSpam()) {
			$this->Session->setFlash(__d('comments', 'Antispam system informed about spam message.', true));
		} else {
			$this->Session->setFlash(__d('comments', 'Error appear during save.', true));
		}
		$this->redirect(array('action' => 'index'));
	}

/**
 * Admin mark comment as ham
 *
 * @param string UUID
 * @access public
 */
	public function admin_ham($id) {
		$this->Comment->id = $id;
		if (!$this->Comment->exists(true)) {
			$this->Session->setFlash(__d('comments', 'Wrong comment id',true));
		} elseif ($this->Comment->markAsHam()) {
			$this->Session->setFlash(__d('comments', 'Antispam system informed about ham message.', true));
		} else {
			$this->Session->setFlash(__d('comments', 'Error appear during save.', true));
		}
		$this->redirect(array('action' => 'index'));
	}

/**
 * Admin View action
 *
 * @param string UUID
 * @access public
 */
	public function admin_view($id = null) {
		$this->Comment->id = $id;
		$comment = $this->Comment->read(null, $id);
		if (empty($comment)) {
			$this->Session->setFlash(__d('comments', 'Invalid Comment.', true));
			return $this->redirect(array('action'=>'index'));
		}
		$this->set('comment', $comment);
	}

/**
 * Admin delete action
 *
 * @param string UUID
 * @access public
 */
	public function admin_delete($id = null) {
		$this->Comment->id = $id;
		if (!$this->Comment->exists(true)) {
			$this->Session->setFlash(__d('comments', 'Invalid id for Comment', true));
		} elseif ($this->Comment->delete()) {
			$this->Session->setFlash(__d('comments', 'Comment deleted', true));
		} else {
			$this->Session->setFlash(__d('comments', 'Impossible to delete the Comment. Please try again.', true));
		}
		$this->redirect(array('action'=>'index'));
	}

/**
 * Request comments 
 *
 * @todo Return only "clean" comments?
 * @todo Return also related models: find a way to automatically bind related models to comments
 * @param string user UUID
 * @return void
 * @access public
 */
	public function requestForUser($userId = null, $amount = 5) {
		if (!$this->RequestHandler->isAjax() && !$this->_isRequestedAction()) {
			return $this->cakeError('404');
		}

		$conditions = array('Comment.user_id' => $userId);
		if (!empty($this->params['named']['model'])) {
			$conditions['Comment.model'] = $this->params['named']['model'];
		}

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
		return array_key_exists('requested', $this->params);
	}
}
?>