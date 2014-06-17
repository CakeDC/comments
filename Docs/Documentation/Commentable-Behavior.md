Commentable Behavior
====================

Behavior overloading and configuration
--------------------------------------

Some times you need to additional associated data returned with the comments. Most easy way for that is to overload the behaviors ```commentBeforeFind``` method on model level:

```php
/**
 * Prepare models association to before fetch data
 *
 * @param array $options
 * @return boolean
 * @access public
 */
	public function commentBeforeFind($options) {
		$result = $this->Behaviors->dispatchMethod($this, 'commentBeforeFind', array($options));

		$userModel = $this->Behaviors->Commentable->settings[$this->alias]['userModelAlias'];
		$this->Comment->bindModel(array('belongsTo' => array(
			'Profile' => array(
				'className' => 'Profile',
				'foreignKey' => false,
				'conditions' => array(
					'Profile.user_id = ' . $userModel . '.id'
				)
			)
		)), false);
		return $result;
	}
```

Supported callbacks
-------------------

* Behavior.Commentable.beforeCreateComment
* Behavior.Commentable.afterCreateComment

Both events called on save comment operation. If you need to prevent the comment saving on some condition, the event listener for ```beforeCreateComment``` must return false. Event ```afterCreateComment``` could used on same additional action that should performed on save comments. Event beforeCreateComment gets the complete comment data that will be stored into database. It is possible to override it in the listener and return new result. Event ```afterCreateComment``` gets only the comment id in the data record and the complete record could be read in the listener action.
