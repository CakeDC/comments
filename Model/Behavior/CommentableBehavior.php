<?php
/**
 * CakePHP Comments
 *
 * Copyright 2009 - 2013, Cake Development Corporation
 *                        1785 E. Sahara Avenue, Suite 490-423
 *                        Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright 2009 - 2013, Cake Development Corporation
 * @link      http://codaset.com/cakedc/migrations/
 * @package   plugins.comments
 * @license   MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('ModelBehavior', 'Model');
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
		'userModelClass' => 'User',
		'userModel' => null,
	);

/**
 * Setup
 *
 * @param Model $model
 * @param array $settings
 */
	public function setup(Model $model, $settings = array()) {
		if (!isset($this->settings[$model->alias])) {
			$this->settings[$model->alias] = $this->defaults;
		}

		if (!is_array($settings)) {
			$settings = (array)$settings;
		}

		$this->settings[$model->alias] = array_merge($this->settings[$model->alias], $settings);
		$this->bindCommentModels($model);
	}

/**
 * Binds the commend and user model and the current model to the comments model
 *
 * @param Model $model
 * @return void
 */
	public function bindCommentModels(Model $model) {
		$config = $this->settings[$model->alias];

		if (!empty($config['commentModel']) && is_array($config['commentModel'])) {
			$model->bindModel(
				array(
					'hasMany' => array(
						'Comment' => $config['commentModel']
					)
				),
				false
			);
		} else {
			$model->bindModel(
				array(
					'hasMany' => array(
						'Comment' => array(
							'className' => $config['commentModel'],
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
							'counterQuery' => ''
						)
					)
				),
				false
			);
		}

		$model->Comment->bindModel(array(
			'belongsTo' => array(
				$model->alias => array(
					'className' => $model->name,
					'foreignKey' => 'foreign_key',
					'unique' => true,
					'conditions' => '',
					'fields' => '',
					'counterCache' => true,
					'dependent' => false)
				)
			),
			false
		);

		if (!empty($config['userModel']) && is_array($config['userModel'])) {
			$model->bindModel(
				array(
					'belongsTo' => array(
						$config['userModelAlias'] => $config['userModel']
					)
				),
				false
			);
		} else {
			$model->Comment->bindModel(array(
				'belongsTo' => array(
					$config['userModelAlias'] => array(
						'className' => $config['userModelClass'],
						'foreignKey' => 'user_id',
						'conditions' => '',
						'fields' => '',
						'counterCache' => true,
						'order' => '')
					)
				),
				false
			);
		}
	}

/**
 * Toggle approved field in model record and increment or decrement the associated
 * models comment count appopriately.
 *
 * @param Model $model
 * @param string $commentId
 * @param array   $options
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
 * @param Model $Model
 * @param string $commentId
 * @return boolean
 */
	public function commentDelete(Model $Model, $commentId = null) {
		return $Model->Comment->delete($commentId);
	}

/**
 * Handle adding comments
 *
 * @param Model $Model     Object of the related model class
 * @param mixed $commentId parent comment id, 0 for none
 * @param array $options   extra information and comment statistics
 * @throws BlackHoleException
 * @return boolean
 */
	public function commentAdd(Model $Model, $commentId = null, $options = array()) {
		$options = array_merge(array('defaultTitle' => '', 'modelId' => null, 'userId' => null, 'data' => array(), 'permalink' => ''), (array)$options);
		extract($options);
		if (isset($options['permalink'])) {
			$Model->Comment->permalink = $options['permalink'];
		}

		$Model->Comment->recursive = -1;
		if (!empty($commentId)) {
			$Model->Comment->id = $commentId;
			if (!$Model->Comment->find('count', array('conditions' => array(
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
				foreach ($data['Other'] as $spam) {
					if (!empty($spam)) {
						return false;
					}
				}
			}

			$event = new CakeEvent('Behavior.Commentable.beforeCreateComment', $Model, $data);
			CakeEventManager::instance()->dispatch($event);
			if ($event->isStopped() && !$event->result) {
				return false;
			} elseif ($event->result) {
				$data = $event->result;
			}

			$Model->Comment->create($data);

			if ($Model->Comment->Behaviors->enabled('Tree')) {
				if (isset($data['Comment']['foreign_key'])) {
					$fk = $data['Comment']['foreign_key'];
				} elseif (isset($data['foreign_key'])) {
					$fk = $data['foreign_key'];
				} else {
					$fk = null;
				}
				$Model->Comment->Behaviors->load('Tree', array(
					'scope' => array('Comment.foreign_key' => $fk))
				);
			}

			if ($Model->Comment->save()) {
				$id = $Model->Comment->id;
				$data['Comment']['id'] = $id;
				$Model->Comment->data[$Model->Comment->alias]['id'] = $id;
				if (!isset($data['Comment']['approved']) || $data['Comment']['approved'] == true) {
					$this->changeCommentCount($Model, $modelId);
				}

				$event = new CakeEvent('Behavior.Commentable.afterCreateComment', $Model, $Model->Comment->data);
				CakeEventManager::instance()->dispatch($event);
				if ($event->isStopped() && !$event->result) {
					return false;
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
 * @param Model 		$Model     Model to change count of
 * @param mixed         $id        The id to change count of
 * @param string        $direction 'up' or 'down'
 * @return null
 */
	public function changeCommentCount(Model $Model, $id = null, $direction = 'up') {
		if ($Model->hasField('comments')) {
			if ($direction == 'up') {
				$direction = '+ 1';
			} elseif ($direction == 'down') {
				$direction = '- 1';
			} else {
				$direction = null;
			}

			$Model->id = $id;
			if (!is_null($direction) && $Model->exists(true)) {
				return $Model->updateAll(
					array($Model->alias . '.comments' => $Model->alias . '.comments ' . $direction),
					array($Model->alias . '.id' => $id));
			}
		}
		return false;
	}

/**
 * Prepare models association to before fetch data
 *
 * @param Model $model
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

		$model->Comment->belongsTo[$model->alias]['fields'] = array($model->primaryKey);
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

