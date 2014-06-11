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
App::uses('CommentsAppModel', 'Comments.Model');

/**
 * Comment model
 *
 * @package comments
 * @subpackage comments.models
 */
class Comment extends CommentsAppModel {

/**
 * Name
 *
 * @var string $name
 */
	public $name = 'Comment';

/**
 * Behaviors
 *
 * @var string $name
 */
	public $actsAs = array(
		'Utils.Sluggable' => array('label' => 'title'),
		'Tree'
	);

/**
 * Is spam field possible values
 *
 * @var array $isSpamValues
 */
	public $isSpamValues = array(
		'clean',
		'spam',
		'ham',
		'spammanual'
	);

/**
 * hasMany associations
 *
 * @var array $hasMany
 */
	public $hasMany = array();

/**
 * Permalink parameter required to pass into anti-spam system
 *
 * @var array $hasMany
 */
	public $permalink;

/**
 * Filter args
 *
 * @var array
 */
    public $filterArgs = array();

/**
 * beforeSave
 *
 * @param array $options
 * @return boolean
 */
	public function beforeSave($options = array()) {
		if (!isset($this->data[$this->alias]['language'])) {
			$this->data[$this->alias]['language'] = Configure::read('Config.language');
		}
		return true;
	}

/**
 * AfterSave
 *
 * @param boolean $created
 * @param array   $options
 * @return boolean
 */
	public function afterSave($created, $options = array()) {
		if ($created) {
			if ($this->Behaviors->enabled('Antispamable')) {
				$isSpam = $this->isSpam(null, array('permalink' => $this->permalink));
				$this->saveField('is_spam', $isSpam ? 'spam' : 'clean');
				if ($isSpam) {
					$this->changeCount($this->id, 'down');
				}
			}
		}
	}

/**
 * Group operation procession
 *
 * @param string $action
 * @param array $data
 * @return boolean Success / Fail
 */
	public function process($action, $data) {
		$message = $addInfo = 	'';
		if (!empty($action) && $action == 'delete') {
			$keys = array_keys($data['Comment']);
			foreach ($keys as $id) {
				$value = $data['Comment'][$id];
				if ((is_string($id) && strlen($id) == 36 || is_numeric($id)) && $value) {
					$result = $this->delete($id);
					if(!$result) {
						$addInfo = __d('comments', 'Some errors appear during execution.');
					}
				}
			}
			$message = __d('comments', 'Comments removed.') . ' ' . $addInfo;
		} elseif (!empty($action) && in_array($action, array('spam', 'ham', 'approve', 'disapprove'))) {
			$keys = array_keys($data['Comment']);
			foreach ($keys as $id) {
				$value = $data['Comment'][$id];
				if ((is_string($id) && strlen($id) == 36 || is_numeric($id)) && $value) {
					$this->recursive = -1;
					$comment = $this->read(null, $id);
					if ($action == 'spam' || $action == 'ham') {
						$modelName = str_replace('.', '', $comment['Comment']['model']);
						if (!isset(${$modelName})) {
							${$modelName} = ClassRegistry::init($comment['Comment']['model']);
						}
						if (method_exists(${$modelName}, 'permalink')) {
							$this->permalink = ${$modelName}->permalink($comment['Comment']['foreign_key']);
						} else {
							$this->permalink = '';
						}
					}
					switch ($action) {
						case 'ham':
							$result = $this->markAsHam($id);
							break;
						case 'spam':
							$result = $this->markAsSpam($id);
							break;
						case 'approve':
							$result = $this->saveField('approved', 1);
							break;
						case 'disapprove':
							$result = $this->saveField('approved', 0);
							break;
					}
					switch($result) {
						case false:
						case 'invalid':
						case 'error':
							$addInfo = __d('comments', 'Some errors appear during execution.');
							break;
					}
				}
			}
			$message = __d('comments', 'Operation was performed. ') . ' ' . $addInfo;
		}
		return $message;
	}

/**
 * Increment or decrement the comment count cache on the associated model
 *
 * @param mixed $id The id to change count of.
 * @param string $direction 'up' or 'down'
 * @return boolean Success of the update
 */
	public function changeCount($id, $direction) {
		$success = false;
		$associated = $this->__getCommentedRow($id);
		if ($associated !== false) {
			if (!$associated['Model']->hasField('comments')) {
				return true;
			}
			$sign = ($direction == 'up') ? '+' : '-';
			$associated['Model']->recursive = -1;
			$success = $associated['Model']->updateAll(
				array('comments' => $associated['Model']->alias.".comments $sign 1"),
				array($associated['Model']->alias . '.id' => $associated['id']));
		}
		return $success;
	}

/**
 * Mark a comment as a spam
 *
 * @param string $id Id of the comment to mark as spam, optional [defauдt: $this->id]
 * @return boolean Success / Fail
 */
	public function markAsSpam($id = null) {
		$success = false;
		if (is_null($id)) {
			$id = $this->id;
		}

		if ($this->changeCount($id, 'down')) {
			if ($this->__updateSpamType($id, 'spammanual')) {
				if ($this->Behaviors->enabled('Antispamable')) {
					$this->setSpam(null, array('permalink' => $this->permalink));
				}
				$success = true;
			} else {
				$this->changeCount($id, 'up');
			}
		}
		return $success;
	}

/**
 * Mark a comment as a ham
 *
 * @param string $id Id of the comment to mark as ham
 * @return boolean Success / Fail
 */
	public function markAsHam($id = null) {
		$success = false;
		if (is_null($id)) {
			$id = $this->id;
		}

		if ($this->changeCount($id, 'up')) {
			if ($this->__updateSpamType($id, 'ham')) {
				if ($this->Behaviors->enabled('Antispamable')) {
					$this->setHam(null, array('permalink' => $this->permalink));
				}
				$success = true;
			} else {
				$this->changeCount($id, 'down');
			}
		}
		return $success;
	}

/**
 * Overrides AppModel::delete() method
 *
 * Automatically decrement comment count of related model
 */
	public function delete($id = null, $cascade = true) {
		$success = false;
		if (is_null($id)) {
			$id = $this->id;
		}

		if ($this->changeCount($id, 'down')) {
			if (parent::delete($id, $cascade)) {
				$success = true;
			} else {
				$this->changeCount($id, 'up');
			}
		}
		return $success;
	}

/**
 * Update the comment spam type
 *
 * @param string $id Comment id
 * @param string $newType New spam type for the comment (valid values: cf $isSpamValues)
 * @return boolean Success of the update
 */
	private function __updateSpamType($id, $newType) {
		$success = false;
		if (in_array($newType, $this->isSpamValues)) {
			$success = $this->updateAll(
				array($this->escapeField('is_spam') => "'$newType'"),
				array($this->escapeField() => $id));
		}
		return $success;
	}

/**
 * Get the row related to a comment
 *
 * @param string $id Comment id
 * @return mixed False if an error occurred, an array with the following keys otherwise:
 * 	- Model: Associated model object
 *  - id: Id of the related row
 */
	private function __getCommentedRow($id) {
		$result = false;
		$comment = $this->find('first', array(
			'recursive' => -1,
			'conditions' => array('id' => $id)));

		if (isset($comment['Comment']['model'])) {
			$Model = ClassRegistry::init($comment['Comment']['model']);
			if (!empty($Model)) {
				$result = array(
					'Model' => $Model,
					'id' => $comment['Comment']['foreign_key']);
			}
		}
		return $result;
	}
}
