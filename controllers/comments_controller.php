<?php
/* SVN FILE: $Id: comments_controller.php 1627 2009-11-05 14:54:05Z burzum $ */
/**
 * Short description for file.
 *
 * Long description for file
 *
 * PHP versions 4 and 5
 *
 * Converge Application Platform
 *
 * Copyright 2007-2008, Cake Development Corporation
 * 							1785 E. Sahara Avenue, Suite 490-423
 * 							Las Vegas, Nevada 89104
 *
 * You may obtain a copy of the License at:
 * License page: http://projects.cakedc.com/licenses/TBD  TBD
 * Copyright page: http://converge.cakedc.com/copyright/
 *
 * @filesource
 * @copyright		Copyright 2007-2008, Cake Development Corporation
 * @link				http://converge.cakedc.com/ Converge Application Platform
 * @package			converge.plugins
 * @subpackage		converge.plugins.Comments.controllers
 * @since			Converge v 1.0.0.0
 * @version			$Revision: 1627 $
 * @modifiedby		$LastChangedBy: burzum $
 * @lastmodified	$Date: 2009-11-05 08:54:05 -0600 (Thu, 05 Nov 2009) $
 * @license			http://projects.cakedc.com/licenses/TBD  TBD
 */
/**
 * Short description for class.
 *
 * @package		converge
 * @subpackage	converge.controllers
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
	public $uses = array('Comment');

/**
 * beforeFilter callback
 *
 * @access public
 * @todo figure out what to do with the account_type, it's different than in the main site.
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('view', 'requestForUser');
		if ($this->Auth->user('account_type') !== '9999') {
			$this->Auth->deny('admin_index', 'admin_spam', 'admin_ham', 'admin_delete', 'admin_edit');
		}
	}

/**
 * Admin index action
 *
 * @param string
 * @access public
 */
	public function admin_index($type = 'spam') {
		$this->Comment->recursive = 0;
		$this->Comment->bindModel(array('belongsTo' => array('UserModel' => array('className' => 'Users.User', 'foreignKey' => 'user_id'))));
		if ($type == 'spam') {
			$conditions = array('Comment.is_spam' => array('spam', 'manualspam'));
		} elseif ($type == 'clean') {
			$conditions = array('Comment.is_spam' => array('ham', 'clean'));
		} elseif (is_null($type)) {
			$conditions = array();
		}
		$this->paginate['Comment'] = array(
			'contain' => array('UserModel'),
			'conditions' => $conditions);
		$this->set('comments', $this->paginate('Comment'));
	}

/**
 * Admin mark mail as spam action
 *
 * @param string UUID
 * @access public
 */
	public function admin_spam($id) {
		$comment = $this->Comment->read(null, $id);
		if (!isset($comment['Comment']['id'])) {
			$this->Session->setFlash(__d('comments', 'Wrong comment id', true));
			$this->redirect(array('action' => 'index'));
		}
		$comment['Comment']['is_spam'] = 'spammanual';
		if ($this->Comment->save($comment)) {
			$Entry = ClassRegistry::init('Blogs.Entry');
			$this->Comment->setSpam(null, array('permalink' => $Entry->permalink($comment['Comment']['foreign_key'])));
			$this->Session->setFlash(__d('comments', 'Antispam system informed about spam message.', true));
		} else {
			$this->Session->setFlash(__d('comments', 'Error appear during save.', true));
		}
		$this->redirect(array('action' => 'index'));
	}

/**
 * Admin mark mail as ham action
 *
 * @param string UUID
 * @access public
 */
	public function admin_ham($id) {
		$comment = $this->Comment->read(null, $id);
		if (!isset($comment['Comment']['id'])) {
			$this->Session->setFlash(__d('comments', 'Wrong comment id',true));
			$this->redirect(array('action' => 'index'));
		}
		$comment['Comment']['is_spam'] = 'ham';
		if ($this->Comment->save($comment)) {
			$this->Comment->setHam(null, array('permalink' => Entry::permalink($modelId)));
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
		if (!$id) {
			$this->Session->setFlash(__d('comments', 'Invalid Comment.', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->set('comment', $this->Comment->read(null, $id));
	}

/**
 * Admin delete action
 *
 * @param string UUID
 * @access public
 */
	public function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__d('comments', 'Invalid id for Comment', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->Comment->delete($id)) {
			$this->Session->setFlash(__d('comments', 'Comment deleted', true));
			$this->redirect(array('action'=>'index'));
		}
	}

/**
 * Request comments 
 *
 * @param string user UUID
 * @return void
 * @access public
 */
	public function requestForUser($userId = null, $amount = 5) {
		if (!$this->RequestHandler->isAjax() && !$this->isRequestedAction()) {
			$this->cakeError('404');
		}

		$conditions = array('Comment.user_id' => $userId);
		if (!empty($this->params['named']['model'])) {
			$conditions['conditions']['Comment.model'] = $this->params['named']['model'];
		}

		$this->Comment->bindModel(array(
			'belongsTo' => array(
				'Answer' => array(
					'className' => 'Qanda.Answer',
					'foreignKey' => 'foreign_key'))));

		$this->paginate = array(
			'contain' => array(
				'Question',
				'Answer.Question'),
			'conditions' => $conditions,
			'order' => 'Comment.created DESC',
			'limit' => $amount);

		$this->set('comments', $this->paginate());
		$this->set('userId', $userId);

		$this->viewPath = 'elements/comments';
		$this->render('comment');
	}
}
?>