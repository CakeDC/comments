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
 * @link      http://codaset.com/cakedc/migrations/
 * @package   plugins.comments
 * @license   MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Short description for class.
 *
 * @package		plugins.comments
 * @subpackage	models.behaviors
 */

CakePlugin::load('Utils');

class BlackHoleException extends Exception {}
class NoActionException extends Exception {}

class CommentableBehavior extends ModelBehavior {
/**
 * Settings array
 *
 * @var array
 */
	public $settings = array();

/**
 * Default settings
 *
 * @var array
 */
	public $defaults = array(
		'commentModel' => 'Comments.Comment',
		'spamField' => 'is_spam',
		'userModelAlias' => 'UserModel',
		'userModelClass' => 'User');

/**
 * Setup
 *
 * @param AppModel $model
 * @param array $settings
 */
	public function setup(Model $model, $settings = array()) {
		if (!isset($this->settings[$model->alias])) {
			$this->settings[$model->alias] = $this->defaults;
		}
		if (!is_array($settings)) {
			$settings = (array) $settings;
		}
			
		$this->settings[$model->alias] = array_merge($this->settings[$model->alias], $settings);

		$cfg = $this->settings[$model->alias];
		$model->bindModel(array('hasMany' => array(
			'Comment' => array(
				'className' => $cfg['commentModel'],
				'foreignKey' => 'foreign_key',
				'unique' => true,
				'conditions' => '',
				'fields' => '',
				'dependent' => true,
				'order' => '',
				'limit' => '',
				'offset' => '',
				'exclusive' => '',
				'finderQuery' => '',
				'counterQuery' => ''))), false);
		$model->Comment->bindModel(array('belongsTo' => array(
			$model->alias => array(
				'className' => $model->name,
				'foreignKey' => 'foreign_key',
				'unique' => true,
				'conditions' => '',
				'fields' => '',
				'counterCache' => true,
				'dependent' => false))), false);
		$model->Comment->bindModel(array('belongsTo' => array(
			$cfg['userModelAlias'] => array(
				'className' => $cfg['userModelClass'],
				'foreignKey' => 'user_id',
				'conditions' => '',
				'fields' => '',
				'counterCache' => true,
				'order' => ''))), false);
	}

/**
 * Toggle approved field in model record and increment or decrement the associated
 * models comment count appopriately.
 *
 * @param AppModel $model
 * @param mixed commentId
 * @param array $options
 * @return boolean
 */
	public function commentToggleApprove(Model $model, $commentId, $options = array()) {
		$model->Comment->recursive = -1;
		$data = $model->Comment->read(null, $commentId);
		if ($data) {
			if ($data['Comment']['approved'] == 0) {
				$data['Comment']['approved'] = 1;
				$direction = 'up';
			} else {
				$data['Comment']['approved'] = 0;
				$direction = 'down';
			}
			if ($model->Comment->save($data, false, array('approved'))) {
				$this->changeCommentCount($model, $data['Comment']['foreign_key'], $direction);
				return true;
			}
		}
		return false;
	}

/**
 * Delete comment
 *
 * @param AppModel $model
 * @param mixed commentId
 * @return boolean
 */
	public function commentDelete(Model $model, $commentId = null) {
		return $model->Comment->delete($commentId);
	}

/**
 * Handle adding comments
 *
 * @param AppModel $model Object of the related model class
 * @param mixed $commentId parent comment id, 0 for none
 * @param array $options extra information and comment statistics
 * @return boolean
 */
	public function commentAdd(Model $model, $commentId = null, $options = array()) {
		$options = array_merge(array('defaultTitle' => '', 'modelId' => null, 'userId' => null, 'data' => array(), 'permalink' => ''), (array)$options);
		extract($options);
		if (isset($options['permalink'])) {
			$model->Comment->permalink = $options['permalink'];
		}

		$model->Comment->recursive = -1;
		if (!empty($commentId)) {
			$model->Comment->id = $commentId;
			if (!$model->Comment->find('count', array('conditions' => array(
				'Comment.id' => $commentId,
				'Comment.approved' => true,
				'Comment.foreign_key' => $modelId)))) {
				throw new BlackHoleException(__d('comments', 'Unallowed comment id', true));
			}
		}

		if (!empty($data)) {
			$data['Comment']['user_id'] = $userId;
			$data['Comment']['model'] = $modelName;
			if (!isset($data['Comment']['foreign_key'])) {
				$data['Comment']['foreign_key'] = $modelId;
			}
			if (!isset($data['Comment']['parent_id'])) {
				$data['Comment']['parent_id'] = $commentId;
			}
			if (empty($data['Comment']['title'])) {
				$data['Comment']['title'] = $defaultTitle;
			}

			if (!empty($data['Other'])) {
				foreach($data['Other'] as $spam) {
					if(!empty($spam)) {
						return false;
					}
				}
			}

			if (method_exists($model, 'beforeComment')) {
				if (!$model->beforeComment(&$data)) {
					return false;
				}
			}

			$model->Comment->create($data);

			if ($model->Comment->Behaviors->enabled('Tree')) {
				if (isset($data['Comment']['foreign_key'])) {
					$fk = $data['Comment']['foreign_key'];
				} elseif (isset($data['foreign_key'])) {
					$fk = $data['foreign_key'];
				} else {
					$fk = null;
				}
				$model->Comment->Behaviors->attach('Tree', array('scope' => array('Comment.foreign_key' => $fk)));
			}

			if ($model->Comment->save()) {
				$id = $model->Comment->id;
				$data['Comment']['id'] = $id;
				$model->Comment->data[$model->Comment->alias]['id'] = $id;
				if (!isset($data['Comment']['approved']) || $data['Comment']['approved'] == true) {
					$this->changeCommentCount($model, $modelId);
				}
				if (method_exists($model, 'afterComment')) {
					if (!$model->afterComment($data)) {
						return false;
					}
				}
				return $id;
			} else {
				return false;
			}
		}
		return null;
	}

/**
 * Increment or decrement the comment count cache on the associated model
 *
 * @param Object $model Model to change count of
 * @param mixed $id The id to change count of
 * @param string $direction 'up' or 'down'
 * @return null
 */
	public function changeCommentCount(Model $model, $id = null, $direction = 'up') {
		if ($model->hasField('comments')) {
			if ($direction == 'up') {
				$direction = '+ 1';
			} elseif ($direction == 'down') {
				$direction = '- 1';
			} else {
				$direction = null;
			}

			$model->id = $id;
			if (!is_null($direction) && $model->exists(true)) {
				return $model->updateAll(
					array($model->alias . '.comments' => $model->alias . '.comments ' . $direction),
					array($model->alias . '.id' => $id));
			}
		}
		return false;
	}

/**
 * Prepare models association to before fetch data
 *
 * @param array $options
 * @return boolean
 */
	public function commentBeforeFind(Model $model, $options) {
		$options = array_merge(array('userModel' => $this->settings[$model->alias]['userModelAlias'], 'userData' => null, 'isAdmin' => false), (array)$options);
		extract($options);

		$model->Behaviors->disable('Containable');
		$model->Comment->Behaviors->disable('Containable');
		$unbind = array();

		foreach (array('belongsTo', 'hasOne', 'hasMany', 'hasAndBelongsToMany') as $assocType) {
			if (!empty($model->Comment->{$assocType})) {
				$unbind[$assocType] = array();
				foreach ($model->Comment->{$assocType} as $key => $assocConfig) {
					$keep = false;
					if (!empty($options['keep']) && in_array($key, $options['keep'])) {
						$keep = true;
					}
					if (!in_array($key, array($userModel, $model->alias)) && !$keep) {
						$unbind[$assocType][] = $key;
					}
				}
			}
		}

		if (!empty($unbind)) {
			$model->Comment->unbindModel($unbind, false);
		}

		$model->Comment->belongsTo[$model->alias]['fields'] = array('id');
		$model->Comment->belongsTo[$userModel]['fields'] = array('id', $model->Comment->{$userModel}->displayField, 'slug');
		$conditions = array('Comment.approved' => 1);
		if (isset($id)) {
			$conditions[$model->alias . '.' . $model->primaryKey] = $id;
			$conditions[$model->Comment->alias . '.model'] = $model->alias;
		}

		if ($isAdmin) {
			unset($conditions['Comment.approved']);
		}

		$model->Comment->recursive = 0;
		$spamField = $this->settings[$model->alias]['spamField'];

		if ($model->Comment->hasField($spamField)) {
			$conditions['Comment.' . $spamField] = array('clean', 'ham');
		}
		$model->Behaviors->enable('Containable');
		$model->Comment->Behaviors->enable('Containable');

		return array('conditions' => $conditions);
	}

}

