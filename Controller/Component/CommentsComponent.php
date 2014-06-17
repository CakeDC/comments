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

/**
 * CommentsComponent
 *
 * Helps handle 'view' action of controller so it can list/add related comments.
 * In related controller action there is no need to fetch associated data for comments - this
 * component is fetching them separately (needed different result from model in dependency of
 * used displayType).
 *
 * Needs Router::connectNamed(array('comment', 'comment_view', 'comment_action)) in config/routes.php.
 *
 * It is also usable to define (in controller, to not fetch unnecessary data
 * in used Controller::paginate() method):
 * var $paginate = array('Comment' => array(
 *	'order' => array('Comment.created' => 'desc'),
 *	'recursive' => 0,
 *	'limit' => 10
 * ));
 *
 * Includes helpers TextWidget and CommentWidget for controller, uses method
 * AppController::blackHole().
 *
 * Most of component methods possible to override in controller
 * for it need to create method with prefix _comments
 * Ex. : _add -> _commentsAdd, _fetchData -> _commentsFetchData
 * Callbacks also need to prefix with '_comments' in controller.
 *
 * callbacks
 * afterAdd
 *
 * params
 *  comment
 *  comment_view_type
 *  comment_action
 *
 * @see CommentWidgetHelper
 * @package comments
 * @subpackage comments.controllers.components
 */
App::uses('Component', 'Controller');

class CommentsComponent extends Component {

/**
 * Components
 *
 * @var array $components
 */
	public $components = array(
		'Cookie',
		'Session',
		'Auth',
		'Paginator'
	);

/**
 * Enabled
 *
 * @var boolean $enabled
 */
	public $active = true;

/**
 * Controller
 *
 * @var mixed $controller
 */
	public $Controller = null;

/**
 * Name of actions this component should use
 *
 * Customizable in beforeFilter()
 *
 * @var array $actionNames
 */
	public $actionNames = array(
		'view', 'comments'
	);

/**
 * Actions used for deleting of some model record, which doesn't use SoftDelete
 * (so we want comments delete directly)
 *
 * Causes than Comment association will NOT be automatically unbind()ed,
 * independently on $this->unbindAssoc
 *
 * Customizable in beforeFilter()
 *
 * @var array $deleteActions
 */
	public $deleteActions = array();

/**
 * Name of 'commentable' model
 *
 * Customizable in beforeFilter(), or default controller's model name is used
 *
 * @var string Model name
 */
	public $modelName = null;

/**
 * Name of association for comments
 *
 * Customizable in beforeFilter()
 *
 * @var string Association name
 */
	public $assocName = 'Comment';

/**
 * Name of user model associated to comment
 *
 * Customizable in beforeFilter()
 *
 * @var string Name of the user model
 */
	public $userModel = 'UserModel';

/**
 * Class Name for user model in ClassRegistry format.
 * Ex: For User model stored in User plugin need to use Users.User
 *
 * Customizable in beforeFilter()
 *
 * @var string user model class name
 */
	public $userModelClass = 'User';

/**
 * Flag if this component should permanently unbind association to Comment model in order to not
 * query model for not necessary data in Controller::view() action
 *
 * Customizable in beforeFilter()
 *
 * @var boolean
 */
	public $unbindAssoc = false;

/**
 * Parameters passed to view
 *
 * @var array
 */
	public $commentParams = array();

/**
 * Name of view variable which contains model data for view() action
 *
 * Needed just for PK value available in it
 *
 * Customizable in beforeFilter(), or default Inflector::variable($this->modelName)
 *
 * @var string
 */
	public $viewVariable = null;

/**
 * Name of view variable for comments data
 *
 * Customizable in beforeFilter()
 *
 * @var string
 */
	public $viewComments = 'commentsData';

/**
 * Flag to allow anonymous user make comments
 *
 * Customizable in beforeFilter()
 *
 * @var boolean
 */
	public $allowAnonymousComment = false;

/**
 * Settings to use when CommentsComponent needs to do a flash message with SessionComponent::setFlash().
 * Available keys are:
 *
 * - `element` - The element to use, defaults to 'default'.
 * - `key` - The key to use, defaults to 'flash'
 * - `params` - The array of additional params to use, defaults to array()
 *
 * @var array
 */
	public $flash = array(
		'element' => 'default',
		'key' => 'flash',
		'params' => array()
	);

/**
 * Named params used internally by the component
 *
 * @var array
 */
	protected $_supportNamedParams = array(
		'comment',
		'comment_action',
		'comment_view_type',
		'quote'
	);

/**
 * Constructor.
 *
 * @param ComponentCollection $collection
 * @param array               $settings
 * @return CommentsComponent
 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		parent::__construct($collection, $settings);
		foreach ($settings as $setting => $value) {
			if (isset($this->{$setting})) {
				$this->{$setting} = $value;
			}
		}
	}

/**
 * Initialize Callback
 *
 * @param Controller $controller
 * @return void
 */
	public function initialize(Controller $controller) {
		$this->Controller = $controller;
		if (empty($this->Cookie) && !empty($this->Controller->Cookie)) {
			$this->Cookie = $this->Controller->Cookie;
		}
		if (empty($this->Session) && !empty($this->Controller->Session)) {
			$this->Session = $this->Controller->Session;
		}
		if (empty($this->Auth) && !empty($this->Controller->Auth)) {
			$this->Auth = $this->Controller->Auth;
		}
		if (!$this->active) {
			return;
		}

		$this->modelName = $controller->modelClass;
		$this->modelAlias = $controller->{$this->modelName}->alias;
		$this->viewVariable = Inflector::variable($this->modelName);
		$controller->helpers = array_merge($controller->helpers, array('Comments.CommentWidget', 'Time', 'Comments.Cleaner', 'Comments.Tree'));
		if (!$controller->{$this->modelName}->Behaviors->attached('Commentable')) {
			$controller->{$this->modelName}->Behaviors->attach('Comments.Commentable', array('userModelAlias' => $this->userModel, 'userModelClass' => $this->userModelClass));
		}
	}

/**
 * Callback
 *
 * @param Controller $controller
 * @return void
 */
	public function startup(Controller $controller) {
		$this->Controller = $controller;
		if (!$this->active) {
			return;
		}
		$this->Auth = $this->Controller->Auth;
		if (!empty($this->Auth) && $this->Auth->user()) {
			$controller->set('isAuthorized', ($this->Auth->user('id') != ''));
		}

		if (in_array($controller->action, $this->deleteActions)) {
			$controller->{$this->modelName}->{$this->assocName}->softDelete(false);
		} elseif ($this->unbindAssoc) {
			foreach (array('hasMany', 'hasOne') as $assocType) {
				if (array_key_exists($this->assocName, $controller->{$this->modelName}->{$assocType})) {
					$controller->{$this->modelName}->unbindModel(array($assocType => array($this->assocName)), false);
					break;
				}
			}
		}
	}

/**
 * Callback
 *
 * @param Controller $controller
 * @return void
 */
	public function beforeRender(Controller $controller) {
		try {
			if ($this->active && in_array($this->Controller->request->action, $this->actionNames)) {
				$type = $this->_call('initType');
				$this->commentParams = array_merge($this->commentParams, array('displayType' => $type));
				$this->_call('view', array($type));
				$this->_call('prepareParams');
				$this->Controller->set('commentParams', $this->commentParams);
			}
		} catch (BlackHoleException $exception) {
			return $this->Controller->blackHole($exception->getMessage());
		} catch (NoActionException $exception) {
		}
	}

/**
 * Determine used type of display (flat/threaded/tree)
 *
 * @return string Type of comment display
 */
	public function callback_initType() {
		$types = array('flat', 'threaded', 'tree');
		$param = 'Comments.' . $this->modelName;

		if (!empty($this->Controller->passedArgs['comment_view_type'])) {
			$type = $this->Controller->passedArgs['comment_view_type'];
			if (in_array($type, $types)) {
				$this->Cookie->write($param, $type, true, '+2 weeks');
				return $type;
			}
		}

		$type = $this->Cookie->read($param);
		if ($type) {
			if (in_array($type, $types)) {
				return $type;
			} else {
				$this->Cookie->delete('Comments');
			}
		}
		return 'flat';
	}

/**
 * Handles controllers actions like list/add related comments
 *
 * @param string $displayType
 * @param bool   $processActions
 * @throws RuntimeException
 * @return void
 */
	public function callback_view($displayType, $processActions = true) {
		if (!isset($this->Controller->{$this->modelName}) ||
			(!array_key_exists($this->assocName, array_merge($this->Controller->{$this->modelName}->hasOne, $this->Controller->{$this->modelName}->hasMany)))) {
			throw new RuntimeException('CommentsComponent: model ' . $this->modelName . ' or association ' . $this->assocName . ' doesn\'t exist');
		}

		$primaryKey = $this->Controller->{$this->modelName}->primaryKey;
		if (empty($this->Controller->viewVars[$this->viewVariable][$this->Controller->{$this->modelName}->alias][$primaryKey])) {
			throw new RuntimeException('CommentsComponent: missing view variable ' . $this->viewVariable . ' or value for primary key ' . $primaryKey . ' of model ' . $this->modelName);
		}

		$id = $this->Controller->viewVars[$this->viewVariable][$this->Controller->{$this->modelName}->alias][$primaryKey];
		$options = compact('displayType', 'id');
		if ($processActions) {
			$this->_processActions($options);
		}

		try {
			$data = $this->_call('fetchData' . Inflector::camelize($displayType), array($options));
		} catch (BadMethodCallException $exception) {
			$data = $this->_call('fetchData', array($options));
		}

		$this->Controller->set($this->viewComments, $data);
	}

/**
 * Paginateable tree representation of the comment data.
 *
 * @param array $options
 * @return array
 */
	public function callback_fetchDataTree($options) {
		$settings = $this->_prepareModel($options);
		$settings += array('order' => array('Comment.lft' => 'asc'));
		$paginate = $settings;
		$paginate['limit'] = 10;

		$overloadPaginate = !empty($this->Controller->paginate['Comment']) ? $this->Controller->paginate['Comment'] : array();
		$this->Controller->Paginator->settings['Comment'] = array_merge($paginate, $overloadPaginate);
		$data = $this->Controller->Paginator->paginate($this->Controller->{$this->modelName}->Comment);
		$parents = array();
		if (isset($data[0]['Comment'])) {
			$rec = $data[0]['Comment'];
			$settings['conditions'][] = array('Comment.lft <' => $rec['lft']);
			$settings['conditions'][] = array('Comment.rght >' => $rec['rght']);
			$parents = $this->Controller->{$this->modelName}->Comment->find('all', $settings);
		}
		return array_merge($parents, $data);
	}

/**
 * Flat representation of the comment data.
 *
 * @param array $options
 * @return array
 */
	public function callback_fetchDataFlat($options) {
		$paginate = $this->_prepareModel($options);

		$overloadPaginate = !empty($this->Controller->paginate['Comment']) ? $this->Controller->paginate['Comment'] : array();
		$this->Controller->Paginator->settings['Comment'] = array_merge($paginate, $overloadPaginate);
		return $this->Controller->Paginator->paginate($this->Controller->{$this->modelName}->Comment);
	}

/**
 * Threaded comment data, one-paginateable, the whole data is fetched.
 *
 * @param array $options
 * @return array
 */
	public function callback_fetchDataThreaded($options) {
		$Comment =& $this->Controller->{$this->modelName}->Comment;
		$settings = $this->_prepareModel($options);
		$settings['fields'] = array(
			'Comment.author_email', 'Comment.author_name', 'Comment.author_url',
			'Comment.id', 'Comment.user_id', 'Comment.foreign_key', 'Comment.parent_id', 'Comment.approved',
			'Comment.title', 'Comment.body', 'Comment.slug', 'Comment.created',
			$this->Controller->{$this->modelName}->alias . '.' . $this->Controller->{$this->modelName}->primaryKey,
			$this->userModel . '.' . $Comment->{$this->userModel}->primaryKey,
			$this->userModel . '.' . $Comment->{$this->userModel}->displayField);

		if ($Comment->{$this->userModel}->hasField('slug')) {
			$settings['fields'][] = $this->userModel . '.slug';
		}

		$settings += array('order' => array(
			'Comment.parent_id' => 'asc',
			'Comment.created' => 'asc'));
		return $Comment->find('threaded', $settings);
	}

/**
 * Default method, calls callback_fetchData
 *
 * @param array $options
 * @return array
 */
	public function callback_fetchData($options) {
		return $this->callback_fetchDataFlat($options);
	}

/**
 * Prepare model association to fetch data
 *
 * @param array $options
 * @return boolean
 */
	protected function _prepareModel($options) {
		$params = array(
			'isAdmin' => $this->Auth->user('is_admin') == true,
			'userModel' => $this->userModel,
			'userData' => $this->Auth->user());
		return $this->Controller->{$this->modelName}->commentBeforeFind(array_merge($params, $options));
	}

/**
 * Prepare passed parameters.
 *
 * @return void
 */
	public function callback_prepareParams() {
		$this->commentParams = array_merge($this->commentParams, array(
			'viewComments' => $this->viewComments,
			'modelName' => $this->modelAlias,
			'userModel' => $this->userModel));
		$allowedParams = array('comment', 'comment_action', 'quote');
		foreach ($allowedParams as $param) {
			if (isset($this->Controller->passedArgs[$param])) {
				$this->commentParams[$param] = $this->Controller->passedArgs[$param];
			}
		}
	}

/**
 * Handle adding comments
 *
 * @param integer $modelId
 * @param integer $commentId Parent comment id
 * @param string  $displayType
 * @param array   $data
 */
	public function callback_add($modelId, $commentId, $displayType, $data = array()) {
		if (!empty($this->Controller->data)) {
			if (!empty($this->Controller->data['Comment']['title'])) {
				$data['Comment']['title'] = $this->cleanHtml($this->Controller->data['Comment']['title']);
			}
			$data['Comment']['body'] = $this->cleanHtml($this->Controller->data['Comment']['body']);
			$modelName = $this->Controller->{$this->modelName}->alias;
			if (!empty($this->Controller->{$this->modelName}->fullName)) {
				$modelName = $this->Controller->{$this->modelName}->fullName;
			}
			$permalink = '';
			if (method_exists($this->Controller->{$this->modelName}, 'permalink')) {
				$premalink = $this->Controller->{$this->modelName}->permalink($modelId);
			}
			$options = array(
				'userId' => $this->Auth->user('id'),
				'modelId' => $modelId,
				'modelName' => $modelName,
				'defaultTitle' => isset($this->Controller->defaultTitle) ? $this->Controller->defaultTitle : '',
				'data' => $data,
				'permalink' => $permalink);
			$result = $this->Controller->{$this->modelName}->commentAdd($commentId, $options);

			if (!is_null($result)) {
				if ($result) {
					try {
						$options['commentId'] = $result;
						$this->_call('afterAdd', array($options));
					} catch (BadMethodCallException $exception) {
					}
					$this->flash(__d('comments', 'The Comment has been saved.'));
					$this->redirect(array('#' => 'comment' . $result));
					if (!empty($this->ajaxMode)) {
						$this->ajaxMode = null;
						$this->Controller->set('redirect', null);
						if (isset($this->Controller->passedArgs['comment'])) {
							unset($this->Controller->passedArgs['comment']);
						}
						$this->_call('view', array($this->commentParams['displayType'], false));
					}
				} else {
					$this->flash(__d('comments', 'The Comment could not be saved. Please, try again.'));
				}
			}
		} else {
			if (!empty($this->Controller->passedArgs['quote'])) {
				if (!empty($this->Controller->passedArgs['comment'])) {
					$message = $this->_call('getFormatedComment', array($this->Controller->passedArgs['comment']));
					if (!empty($message)) {
						$this->Controller->request->data['Comment']['body'] = $message;
					}
				}
			}
		}
	}

/**
 * Fetch and format a comment message.
 *
 * @param string $commentId
 * @return string
 */
	public function callback_getFormatedComment($commentId) {
		$comment = $this->Controller->{$this->modelName}->Comment->find('first', array(
			'recursive' => -1,
			'fields' => array('Comment.body', 'Comment.title'),
			'conditions' => array('Comment.id' => $commentId)));
		if (!empty($comment)) {

		} else {
			return null;
		}
		return "[quote]\n" . $comment['Comment']['body'] . "\n[end quote]";
	}

/**
 * Handles approval of comments.
 *
 * @param string $modelId
 * @param string $commentId
 * @throws BlackHoleException
 * @return void
 */
	public function callback_toggleApprove($modelId, $commentId) {
        if (!isset($this->Controller->passedArgs['comment_action'])
			|| !($this->Controller->passedArgs['comment_action'] == 'toggle_approve' && $this->Controller->Auth->user('is_admin') == true)) {
			throw new BlackHoleException(__d('comments', 'Nonrestricted operation'));
		}
		if ($this->Controller->{$this->modelName}->commentToggleApprove($commentId)) {
			$this->flash(__d('comments', 'The Comment status has been updated.'));
		} else {
			$this->flash(__d('comments', 'Error appear during comment status update. Try later.'));
		}
	}

/**
 * Deletes comments
 *
 * @param string $modelId
 * @param string $commentId
 * @return void
 */
	public function callback_delete($modelId, $commentId) {
		if ($this->Controller->{$this->modelName}->commentDelete($commentId)) {
			$this->flash(__d('comments', 'The Comment has been deleted.'));
		} else {
			$this->flash(__d('comments', 'Error appear during comment deleting. Try later.'));
		}
		$this->redirect();
	}

/**
 * Flash message - for ajax queries, sets 'messageTxt' view vairable,
 * otherwise uses the Session component and values from CommentsComponent::$flash.
 *
 * @param string $message The message to set.
 * @return void
 */
	public function flash($message) {
		$isAjax = isset($this->Controller->params['isAjax']) ? $this->Controller->params['isAjax'] : false;
		if ($isAjax) {
			$this->Controller->set('messageTxt', $message);
		} else {
			$this->Controller->Session->setFlash($message, $this->flash['element'], $this->flash['params'], $this->flash['key']);
		}
	}

/**
 * Redirect
 * Redirects the user to the wanted action by persisting passed args excepted
 * the ones used internally by the component
 *
 * @param array $urlBase
 * @return void
 */
	public function redirect($urlBase = array()) {
		$isAjax = isset($this->Controller->params['isAjax']) ? $this->Controller->params['isAjax'] : false;

		$url = array_merge(
			array_diff_key($this->Controller->passedArgs, array_flip($this->_supportNamedParams)),
			$urlBase);
		if ($isAjax) {
			$this->Controller->set('redirect', $url);
		} else {
			$this->Controller->redirect($url);
		}
		if ($isAjax) {
			$this->ajaxMode = true;
			$this->Controller->set('ajaxMode', true);
		}
	}

/**
 * Generate permalink to page
 *
 * @return string URL to the comment
 */
	public function permalink() {
		$params = array();
        foreach (array('admin', 'controller', 'action', 'plugin') as $name) {
			if (isset($this->Controller->request->params['name'])) {
				$params[$name] = $this->Controller->request->params['name'];
			}
		}

		if (isset($this->Controller->request->params['pass'])) {
			$params = array_merge($params, $this->Controller->params['pass']);
		}

		if (isset($this->Controller->request->params['named'])) {
			foreach ($this->Controller->request->params['named'] as $k => $v) {
				if (!in_array($k, $this->_supportNamedParams)) {
					$params[$k] = $v;
				}
			}
		}
		return Router::url($params, true);
	}

/**
 * Call action from component or overridden action from controller.
 *
 * @param string $method
 * @param array  $args
 * @throws BadMethodCallException
 * @return mixed
 */
	protected function _call($method, $args = array()) {
		$methodName = 'callback_comments' . Inflector::camelize(Inflector::underscore($method));
		$localMethodName = 'callback_' . $method;
		if (method_exists($this->Controller, $methodName)) {
			return call_user_func_array(array($this->Controller, $methodName), $args);
		} elseif (method_exists($this, $localMethodName)) {
			return call_user_func_array(array($this, $localMethodName), $args);
		} else {
			throw new BadMethodCallException();
		}
	}

/**
 * Non view action process method
 *
 * @param array
 * @return boolean
 */
	protected function _processActions($options) {
		extract($options);
		if (isset($this->Controller->passedArgs['comment'])) {
			if ($this->allowAnonymousComment || $this->Auth->user()) {
				if (isset($this->Controller->passedArgs['comment_action'])) {
					$commentAction = $this->Controller->passedArgs['comment_action'];
					$isAdmin = (bool)$this->Auth->user('is_admin');
					if (!$isAdmin) {
						if (in_array($commentAction, array('delete'))) {
							call_user_func(array($this, '_' . Inflector::variable($commentAction)), $id, $this->Controller->passedArgs['comment']);
							return;
						} else {
							return $this->Controller->blackHole("CommentsComponent: comment_Action '$commentAction' is for admins only");
						}
					}
					if (!in_array($commentAction, array('toggle_approve', 'delete'))) {
						return $this->Controller->blackHole("CommentsComponent: unsupported comment_Action '$commentAction'");
					}
					$this->_call(Inflector::variable($commentAction), array($id, $this->Controller->passedArgs['comment']));
				} else {
					Configure::write('Comment.action', 'add');
					$parent = empty($this->Controller->passedArgs['comment']) ? null : $this->Controller->passedArgs['comment'];
					$this->_call('add', array($id, $parent, $displayType));
				}
			} else {
				$this->Controller->Session->write('Auth.redirect', $this->Controller->request['url']);
				$this->Controller->redirect($this->Controller->Auth->loginAction);
			}
		}
	}

/**
 * Wrapping method to clean incoming html contents
 *
 * @deprecated This is going to be removed in the near future
 * @param string $text
 * @param string $settings
 * @return string
 */
	public function cleanHtml($text, $settings = 'full') {
		App::import('Helper', 'Comments.Cleaner');
		$cleaner = & new CleanerHelper(new View($this->Controller));
		return $cleaner->clean($text, $settings);
	}

}

