<?php
App::import('Core', array('ClassRegistry', 'Controller', 'View', 'Model', 'Security'));
App::import('Helper', array('Comments.CommentWidget', 'Html', 'Form', 'Session'));
App::import('Component', array('Comments.Comments'));

class Article extends CakeTestModel {
/**
 * 
 */
	public $name = 'Article';
}

class ArticlesTestController extends Controller {

/**
 * @var string
 * @access public
 */
	public $name = 'ArticlesTest';

/**
 * @var array
 * @access public
 */
	public $uses = array('Article');

/**
 * @var array
 * @access public
 */
	public $components = array('Comments.Comments');

/**
 * 
 */
	public function beforeFilter() {
		parent::beforeFilter();
	}

/**
 * 
 */
	public function redirect($url, $status = NULL, $exit = true) {
		$this->redirectUrl = $url;
	}

}

class CommentWidgetHelperTest extends CakeTestCase {

/**
 * fixtures property
 *
 * @var array
 * @access public
 */
	public $fixtures = array(
		'plugin.comments.comment',
		'plugin.comments.user',
		'plugin.comments.article');

/**
 * setUp method
 *
 * @access public
 * @return void
 */
	function setUp() {
		parent::setUp();
		Router::reload();

		$this->CommentWidget =& new CommentWidgetHelper();
		$this->CommentWidget->Form =& new FormHelper();
		$this->Controller =& new ArticlesTestController();
		$this->View =& new View($this->Controller);
		$this->CommentWidget->params['action'] = 'view';

		ClassRegistry::addObject('view', $view);
		ClassRegistry::addObject('Article', new Article());
		ClassRegistry::addObject('CommentWidget', new CommentWidget());
	}

/**
 * tearDown method
 *
 * @access public
 * @return void
 */
	function tearDown() {
		ClassRegistry::removeObject('view');
		ClassRegistry::removeObject('Article');
		ClassRegistry::removeObject('CommentWidget');
		unset($this->CommentWidget->Html, $this->CommentWidget, $this->Controller, $this->View);
	}

}
?>