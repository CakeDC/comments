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
		'Utils.Sluggable' => array(
			'label' => 'title',
			'separator' => '_',
			'length' => '255'),
		'Tree');

/**
 * Additional associations will be registered via callbacks
 *
 * @var array $belongsTo
 * @access public
 */
	/*
	public $belongsTo = array(
		'UserModel' => array('className' => 'Users.User',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'counterCache' => true,
			'order' => ''));
	*/
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
		if (!isset($this->data[$this->alias]['language'])) {#
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
 * @todo find why core creates wrong query for entries in the commented section
 * 
 * @param mixed $id The id to change count of.
 * @param string $direction 'up' or 'down'
 * @access public
 * @return null
 */
	public function changeCount($id, $direction) {
		$comment = $this->find('first', array('recursive' => -1, 'conditions' => array('id' => $id)));
		if (!isset($comment['Comment']['model'])) {
			return;
		}

		$Model = ClassRegistry::init($comment['Comment']['model']);
		if (empty($Model)) {
			return;
		}

		//$Model->id = $comment['Comment']['foreign_key'];
		$sign = ($direction == 'up') ? '+' : '-';
		$Model->recursive = -1;
		$Model->updateAll(array('comments' => "comments $sign 1"), array('id' => $comment['Comment']['foreign_key']));
	}
}
?>