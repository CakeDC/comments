<?php
App::import('Core', array('ClassRegistry', 'Controller', 'View', 'Model', 'Security'));
App::import('Helper', array('Comments.CommentWidget', 'Html', 'Form', 'Session'));
App::import('Component', array('Comments.Comments'));

Mock::generatePartial('AppHelper', 'JqueryHelper', array('link'));

class Article extends CakeTestModel {
/**
 * Name
 * @var string
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
 * Overrides Controller::redirect() to log the redirected url
 * (non-PHPdoc)
 * @see cake/libs/controller/Controller#redirect($url, $status, $exit)
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
 * Helper being tested
 * @var CommentWidgetHelper
 */
	public $CommentWidget = null;
	
/**
 * Controller with commentable related actions for testing purpose
 * @var ArticlesTestController
 */
	public $Controller = null;
	
/**
 * Current view object
 * @var View
 */
	public $View = null;
	
/**
 * Mock object for Jquery helper
 * @var JqueryHelper
 */
	public $Jquery = null;
	
/**
 * Start test method
 *
 * @return void
 */
	public function startTest($method) {
		parent::startTest($method);

		$this->CommentWidget = new CommentWidgetHelper();
		$this->CommentWidget->Form = new FormHelper();
		$this->CommentWidget->Html = new HtmlHelper();
		$this->Jquery = new JqueryHelper();
		$this->CommentWidget->Jquery = $this->Jquery;
		$this->CommentWidget->params['action'] = 'view';
		
		$this->Controller = ClassRegistry::init('ArticlesTestController');
		$this->View = new View($this->Controller);
		ClassRegistry::addObject('view', $this->View);
		
		if (!in_array($method, array('testInitialize', 'testOptions'))) {
			$this->CommentWidget->initialize();
		}
	}
	
/**
 * Test helper instance
 * 
 * @return void
 */
	public function testInstance() {
		$this->assertTrue(is_a($this->CommentWidget, 'CommentWidgetHelper'));
	}
	
/**
 * Test initialize
 * 
 * @return void
 */
	public function testInitialize() {
		$this->assertTrue(empty($this->CommentWidget->globalParams));
		$this->CommentWidget->initialize();
		$this->assertFalse(empty($this->CommentWidget->globalParams));
	}

/**
 * Test beforeRender callback
 * 
 * @return void
 */
	public function testBeforeRender() {
		$this->assertTrue(empty($this->View->viewVars));
		$this->CommentWidget->beforeRender();
		$this->assertFalse($this->CommentWidget->enabled);
		
		$this->View->viewVars['commentParams'] = array(
			'displayType' => 'flat',
			'viewComments' => 'commentsData');
		$this->CommentWidget->beforeRender();
		$this->assertTrue($this->CommentWidget->enabled);
	}
	
/**
 * Test options method
 * 
 * @return void
 */
	public function testOptions() {
		$this->assertTrue(empty($this->CommentWidget->globalParams));
		$options = array(
			'target' => 'test',
			'foo' => 'bar');
		$this->CommentWidget->options($options);
		$this->assertEqual(count($this->CommentWidget->globalParams), 8);
		$this->assertEqual($this->CommentWidget->globalParams['target'], 'test');
		$this->assertEqual($this->CommentWidget->globalParams['foo'], 'bar');
		
		$this->CommentWidget->options(array());
		$this->assertEqual(count($this->CommentWidget->globalParams), 8);
		$this->assertFalse($this->CommentWidget->globalParams['target']);
		$this->assertEqual($this->CommentWidget->globalParams['foo'], 'bar');
	}
	
/**
 * Test display method
 * 
 * @TODO Code me!
 * @return void
 */
	public function testDisplay() {
		
	}

/**
 * Test link method
 * 
 * @return void
 */
	public function testLink() {
		$result = $this->CommentWidget->link('Foobar', '/foo', array('class' => 'bar'));
		$expected = array(
			'a' => array('href' => '/foo', 'class' => 'bar'), 
			'Foobar', 
			'/a');
		$this->assertTags($result, $expected);
		
		$this->CommentWidget->options(array('target' => 'wrapper')); 
		$this->Jquery->expectOnce('link', array(
			'Foobar',
			'/foo',
			array('class' => 'bar', 'update' => 'wrapper'))
		);
		$this->Jquery->setReturnValueAt(0, 'link', '/ajaxFoo');
		$result = $this->CommentWidget->link('Foobar', '/foo', array('class' => 'bar'));
		$this->assertEqual($result, '/ajaxFoo');
	}
	
/**
 * Test prepareUrl method
 * 
 * @TODO Code me!
 * @return void
 */
	public function testPrepareUrl() {
		
	}
	
/**
 * Test allowAnonymousComment method
 * 
 * @TODO Code me!
 * @return void
 */
	public function testAllowAnonymousComment() {
		
	}
	
/**
 * Test element method
 * 
 * @TODO Code me!
 * @return void
 */
	public function testElement() {
		
	}

/**
 * Test treeCallback method
 * 
 * @TODO Code me!
 * @return void
 */
	public function testTreeCallback() {
		
	}

/**
 * End test method
 *
 * @return void
 */
	public function endTest($method) {
		unset($this->CommentWidget, $this->Controller, $this->View);
		ClassRegistry::flush();
	}

}
?>