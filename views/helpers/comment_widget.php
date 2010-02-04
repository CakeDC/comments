	<?php
/* SVN FILE: $Id: comment_widget.php 1159 2009-09-17 21:01:45Z mark_story $ */
class CommentWidgetHelper extends AppHelper {
/**
 * Helpers
 *
 * @var array
 * @access public
 */
	public $helpers = array('Html', 'Js' => array('Jquery'));
/**
 * Flag if this widget is properly configured
 *
 * @var boolean
 * @access public
 */
	public $enabled = true;
/**
 * Helper options
 *
 * @var array
 * @access public
 */
	public $options = array(
		'target' => false,
		'ajaxAction' => false,
		'displayUrlToComment' => false,
		'urlToComment' => '',
		'allowAnonymousComment'  => false,
		'url' => null,
		'viewInstance' => null
	);
/**
 * List of settings needed to be not empty in $this->params['Comments']
 *
 * @var array
 * @access protected
 */
	protected $__passedParams = array('displayType', 'viewComments');
/**
 * Global widget parameters
 *
 * @var string
 * @access public
 */
	public $globalParams = array();
/**
 * 
 */
	public function initialize() {
		$this->options(array());
	}
/**
 * Callback
 */
	public function beforeRender() {
		parent::beforeRender();
		$View = $this->__view();

		$this->enabled = !empty($View->viewVars['commentParams']);
		if ($this->enabled) {
			foreach ($this->__passedParams as $param) {
				if (empty($View->viewVars['commentParams'][$param])) {
					$this->enabled = false;
					break;
				}
			}
		}
	}
/**
 * Setup options
 */
	public function options($data) {
		$this->globalParams = array_merge(array_merge($this->globalParams, $this->options), (array)$data);
	}
/**
 * Display comments
 *
 * ### Params
 *
 * - `displayType` The primary type of comments you want to display.  Default is flat, and built in types are
 *    flat, threaded and tree.
 * - `subtheme` an optional subtheme to use for rendering the comments, used with `displayType`.  
 *    If your comments type is 'flat' and you use `'theme' => 'mytheme'` in your params. 
 *   `elements/comments/flat_mytheme` is the directory the helper will look for your elements in.
 *
 * @TODO Check if the $adminRoute value is used somewher. Either remove it or find a 1.3 equivalent.
 * @param array $params Parameters for the comment rendering
 * @return string Rendered elements.
 */
	public function display($params = array()) {
		//$this->options(array());
		$result = '';
		if ($this->enabled) {
			$View = $this->__view();

			$params = Set::merge($View->viewVars['commentParams'], $params);
			if (isset($params['displayType'])) {
				$theme = $params['displayType'];
				if (isset($params['subtheme'])) {
					$theme .= '_' . $params['subtheme'];
				}
			} else {
				$theme = 'flat';
			}

			$url = $this->globalParams['url'] = array();
			if (isset($View->params['userslug'])) {
				$url[] = $View->params['userslug'];
			}
			if (!empty($View->passedArgs)) {
				foreach ($View->passedArgs as $key => $value) {
					if (is_numeric($key)) {
						$url[] = $value;
					}
				}
			}

			$model = $params['modelName'];
			$viewRecord = $this->globalParams['viewRecord'] = array();
			if (isset($View->viewVars[Inflector::variable($model)][$model])) {
				$viewRecord = $View->viewVars[Inflector::variable($model)][$model];
			}

			if (isset($viewRecord['allow_comments'])) {
				$allowAddByModel = ($viewRecord['allow_comments'] == 1);
			} else {
				$allowAddByModel = 1;
			}
			$isAddMode = (isset($params['comment']) && !isset($params['comment_action']));
			$adminRoute = Configure::read('Routing.admin');

			$allowAddByAuth = ($this->allowAnonymousComment() || !empty($View->viewVars['isAuthorized']));

			$params = array_merge($params, compact('url', 'allowAddByAuth', 'allowAddByModel', 'adminRoute', 'isAddMode', 'viewRecord', 'theme'));

			$this->globalParams = Set::merge($this->globalParams, $params);
			$result = $this->element('main');
		}
		return $result;
	}
/**
 * Description
 *
 * @access public
 */
	public function link($title, $url='', $options=array()) {
		if ($this->globalParams['target']) {
			return $this->Jquery->link($title, $this->prepareUrl($url), am($options, array('update' => $this->globalParams['target'])));
		} else {
			return $this->Html->link($title, $url, $options);
		}
	}
/**
 * Description
 *
 * @access public
 */
	public function prepareUrl(&$url) {
		if ($this->globalParams['target']) {
			if (is_string($this->globalParams['ajaxAction'])) {
				$url['action'] = $this->globalParams['ajaxAction'];
			} elseif(is_array($this->globalParams['ajaxAction'])) {
				$url = array_merge($url, $this->globalParams['ajaxAction']);
			}
		}
		return $url;
	}
/**
 * Description
 *
 * @access public
 */
	public function allowAnonymousComment() {
		return $this->globalParams['allowAnonymousComment'];
	}

/**
 * Render element from global theme
 *
 * @access public
 */
	public function element($name, $params = array()) {
		$View = $this->__view();
		if (strpos($name, '/') === false) {
			$name = 'comments/' . $this->globalParams['theme'] . '/' . $name;
		}
		$params = Set::merge($this->globalParams, $params);
		$response = $View->element($name, $params);
		if (is_null($response) || substr($response, 0, 10) === 'Not Found:') {
			$response = $View->element($name, array_merge($params, array('plugin' => 'comments')));
		}
		return $response;
	}
/**
 * Basic tree callback
 */
	public function treeCallback($data) {
		return $this->element('item', array('comment' => $data['data'], 'data' => $data));
	}
/**
 * Get current view class
 *
 * @access public
 * @return object, View class
 */
	private function __view() {
		if (!empty($this->globalParams['viewInstance'])) {
			return $this->globalParams['viewInstance'];
		} else {
			return ClassRegistry::getObject('view');
		}
	}
}
?>