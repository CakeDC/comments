<?php
class Comment extends CommentsAppModel {
/**
 * Name
 *
 * @var string $name
 * @access public
 */
	public $name = 'Comment';

/**
 * Behaviors
 *
 * @var string $name
 * @access public
 */
	public $actsAs = array(
		'Comments.Sluggable' => array(
			'label' => 'title'),
		'Tree');

/**
 * belongsTo Associations. 
 * The reason why for alias used UserModel instead of User next: if you will need to attach comments to the User model it allow you to do it.
 *
 * @var array $belongsTo
 * @access public
 */
	// public $belongsTo = array(
		// 'UserModel' => array('className' => 'Users.User',
			// 'foreignKey' => 'user_id',
			// 'conditions' => '',
			// 'fields' => '',
			// 'counterCache' => true,
			// 'order' => ''));
/**
 * Is spam field possible values
 *
 * @var array $isSpamValues
 * @access public
 */
	public $isSpamValues = array('clean', 'spam', 'ham', 'spammanual');

/**
 * hasMany associations
 *
 * @var array $hasMany
 * @access public
 */
	public $hasMany = array();

/**
 * Permalink parameter required to pass into antispam system
 *
 * @var array $hasMany
 * @access public
 */
	public $permalink;

/**
 * beforeSave
 *
 * @param boolean $created
 * @return boolean
 * @access public
 */
	public function beforeSave() {
		if (!isset($this->data[$this->alias]['language'])) {
			$this->data[$this->alias]['language'] = Configure::read('Config.language');
		}
		return true;
	}

/**
 * AfterSave
 *
 * @param boolean $created
 * @return boolean
 * @access public
 */
	public function afterSave($created) {
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
 * Constructor
 *
 * @param string $id UUID
 * @param string $table
 * @param string $ds
 * @return
 * @access public
 */
	public function __construct($id = false, $table = null, $ds = null) {
		//$this->actsAs['Antispam.Antispamable'] = Set::merge($this->actsAs['Antispam.Antispamable'], Configure::read('Antispam.config'));
		return parent::__construct($id, $table, $ds);
	}

/**
 * Increment or decrement the comment count cache on the associated model
 * 
 * @param mixed $id The id to change count of.
 * @param string $direction 'up' or 'down'
 * @access public
 * @return boolean Success of the update
 */
	public function changeCount($id, $direction) {
		$success = false;
		$associated = $this->__getCommentedRow($id);
		
		if ($associated !== false){
			$sign = ($direction == 'up') ? '+' : '-';
			$associated['Model']->recursive = -1;
			$success = $associated['Model']->updateAll(
				array('comments' => "comments $sign 1"),
				array('id' => $associated['id']));
		}
		return $success;
	}

/**
 * Mark a comment as a spam
 * 
 * @param string $id Id of the comment to mark as spam, optionnal [defaut: $this->id]
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
 * Automatically decrement comment count of related model
 * 
 * (non-PHPdoc)
 * @see cake/libs/model/Model#delete($id, $cascade)
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
 * @return mixed False if an error occured, an array with the following keys otherwise:
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
?>