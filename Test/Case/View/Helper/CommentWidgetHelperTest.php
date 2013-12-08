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

App::uses('Controller', 'Controller');
App::uses('View', 'View');
App::uses('JsHelper', 'View/Helper');
App::import('Controller', 'Controller', false); 
//App::import('Core', array('ClassRegistry', 'Controller', 'View', 'Model', 'Security'));
App::import('Helper', array('Comments.CommentWidget', 'Html', 'Form', 'Session'));
App::import('Component', array('Comments.Comments'));

//Mock::generatePartial('AppHelper', 'JsHelper', array('link', 'get', 'effect'));

if (!class_exists('Article')) {
	class Article extends CakeTestModel {
	/**
	 * 
	 */
		public $name = 'Article';
	}
}

if (!class_exists('ArticlesTestController')) {
	class ArticlesTestController extends Controller {

	/**
	 * @var string
	 */
		public $name = 'ArticlesTest';

	/**
	 * @var array
	 */
		public $uses = array('Article');

	/**
	 * @var array
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
}

/**
 * Comment Widget Helper Test
 *
 * @package comments
 * @subpackage comment.tests.cases.helpers
 */
class CommentWidgetHelperTest extends CakeTestCase {

/**
 * fixtures property
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.Comments.comment',
		'plugin.Comments.user',
		'plugin.Comments.article');

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
 * Mock object for Js helper
 * @var JsHelper
 */
	public $Js = null;
	
/**
 * Start test method
 *
 * @return void
 */
	public function startTest($method) {
		$this->Request = new CakeRequest();
		$this->Controller = new ArticlesTestController($this->Request);
		$this->View = new View($this->Controller);

		$this->CommentWidget = new CommentWidgetHelper($this->View);
		$this->CommentWidget->Form = new FormHelper($this->View);
		$this->CommentWidget->Html = new HtmlHelper($this->View);
		$this->Js = new JsHelper($this->View);
		$this->CommentWidget->Js = $this->Js;
		$this->CommentWidget->params['action'] = 'view';

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
	
/**
 * Test helper instance
 * 
 * @return void
 */
	public function testInstance() {
		$this->assertTrue(is_a($this->CommentWidget, 'CommentWidgetHelper'));
	}


/**
 * Test beforeRender callback
 * 
 * @return void
 */
	public function testBeforeRender() {
		$this->assertTrue(empty($this->View->viewVars));
		$this->CommentWidget->beforeRender('view.ctp');
		$this->assertFalse($this->CommentWidget->enabled);
		
		$this->View->viewVars['commentParams'] = array(
			'displayType' => 'flat');
		$this->CommentWidget->beforeRender();
		$this->assertFalse($this->CommentWidget->enabled);
		
		$this->View->viewVars['commentParams'] = array(
			'displayType' => 'flat',
			'viewComments' => 'commentsData');
		$this->CommentWidget->beforeRender('view.ctp');
		$this->assertTrue($this->CommentWidget->enabled);
	}
	
/**
 * Test options method
 * 
 * @return void
 */
	public function _testOptions() {
		$this->assertTrue(empty($this->CommentWidget->globalParams));
		$this->Js->setReturnValue('get', $this->Js);
		$this->Js->setReturnValue('effect', '');
		$options = array(
			'target' => 'test',
			'foo' => 'bar');

		$this->CommentWidget->options($options);
		$this->assertEqual(count($this->CommentWidget->globalParams), 9);
		$this->assertEqual($this->CommentWidget->globalParams['target'], 'test');
		$this->assertEqual($this->CommentWidget->globalParams['foo'], 'bar');
		
		$this->CommentWidget->options(array());
		$this->assertEqual(count($this->CommentWidget->globalParams), 9);
		$this->assertFalse($this->CommentWidget->globalParams['target']);
		$this->assertEqual($this->CommentWidget->globalParams['foo'], 'bar');
	}
	
/**
 * Test display method
 * 
 * @return void
 */
	public function testDisplay() {
		$this->CommentWidget->enabled = false;
		$this->assertEqual($this->CommentWidget->display(), '');

		$this->__mockView();
		$this->CommentWidget->enabled = true;
		$countElementCall = 0;
		$initialParams = $this->CommentWidget->globalParams; 
		$Article = ClassRegistry::init('Article');
		Configure::write('Routing.admin', 'admin');

		// Test a basic display call
		$currArticle = $Article->findById(1);
		$this->View->passedArgs = array(
			'foo' => 'bar',
			'article-slug');
		$this->View->viewVars = array(
			'article' => $currArticle,
			'commentParams' => array(
				'viewComments' => 'commentsData',
				'modelName' => 'Article',
				'userModel' => 'User'),
		);
		$expectedParams = array(
			'comments/flat/main', 
			array_merge(
				$initialParams,
				array(
					'viewRecord' => $currArticle['Article'],
					'viewRecordFull' => $currArticle),
				$this->View->viewVars['commentParams'],
				array(
					'url' => array('article-slug'),
					'allowAddByAuth' => false,
					'allowAddByModel' => 1,
					'adminRoute' => 'admin',
					'isAddMode' => false,
					'theme' => 'flat')
				)
		);
		
		$expected = 'Here are your comments!';
		$this->View
			->expects($this->at(1))
			->method('element')
			->will($this->returnValue($expected));
			
		$result = $this->CommentWidget->display();
		$this->assertEqual($result, $expected);


		// Test a display call with options
		$expectedParams[0] = 'comments/threaded_custom/main';
		$expectedParams[1] = array_merge($expectedParams[1], array(
			'theme' => 'threaded_custom',
			'displayType' => 'threaded', 
			'subtheme' => 'custom'));
			
		$this->View
			->expects($this->at(1))
			->method('element')
			->will($this->returnValue($expected));
			
		$options = array(
			'displayType' => 'threaded',
			'subtheme' => 'custom');
		$result = $this->CommentWidget->display($options);
		$this->assertEqual($result, $expected);
	}

/**
 * Test display method with a custom url
 * 
 * @return void
 */
	public function testDisplayCustomUrl() {
		$this->__mockView();
		$countElementCall = 0;
		$initialParams = $this->CommentWidget->globalParams; 
		$Article = ClassRegistry::init('Article');
		Configure::write('Routing.admin', 'admin');

		// Test a basic display call
		$currArticle = $Article->findById(1);
		$this->View->passedArgs = array(
			'foo' => 'bar',
			'article-slug');
		$this->View->viewVars = array(
			'article' => $currArticle,
			'commentParams' => array(
				'viewComments' => 'commentsData',
				'modelName' => 'Article',
				'userModel' => 'User'),
		);
		$expectedParams = array(
			'comments/flat/main', 
			array_merge(
				$initialParams,
				array(
					'viewRecord' => $currArticle['Article'],
					'viewRecordFull' => $currArticle),
				$this->View->viewVars['commentParams'],
				array(
					'url' => array('action' => 'other', 'param1'),
					'allowAddByAuth' => false,
					'allowAddByModel' => 1,
					'adminRoute' => 'admin',
					'isAddMode' => false,
					'theme' => 'flat')
				)
		);
		$this->CommentWidget->options(array('url' => array('action' => 'other', 'param1')));
		$expected = 'Here are your comments!';
		
		$this->View
			->expects($this->at($countElementCall++))
			->method('element')
			->will($this->returnValue($expected));
		
		// $this->View->expectAt($countElementCall, 'element', $expectedParams);
		// $this->View->setReturnValueAt($countElementCall++, 'element', $expected);
		$result = $this->CommentWidget->display();
		$this->assertEqual($result, $expected);
	}

/**
 * Test link method
 * 
 * @return void
 */
	public function _testLink() {
		$result = $this->CommentWidget->link('Foobar', '/foo', array('class' => 'bar'));
		$expected = array(
			'a' => array('href' => '/foo', 'class' => 'bar'), 
			'Foobar', 
			'/a');
		$this->assertTags($result, $expected);

		$this->Js->setReturnValue('get', $this->Js);
		$this->Js->setReturnValue('effect', '');
		
		$this->CommentWidget->options(array('target' => 'wrapper', 'ajaxOptions' => array('update' => 'wrapper'))); 
		$this->Js->expectOnce('link', array(
			'Foobar',
			'/foo',
			array(
			'update' => 'wrapper',
			'class' => 'bar'))
		);
		$this->Js->setReturnValueAt(0, 'link', '/ajaxFoo');
		$result = $this->CommentWidget->link('Foobar', '/foo', array('class' => 'bar'));
		$this->assertEqual($result, '/ajaxFoo');
	}
	
/**
 * Test prepareUrl method
 * 
 * @return void
 */
	public function _testPrepareUrl() {
		$expected = $url = array(
			'controller' => 'articles',
			'action' => 'view',
			'my-first-article');
		$this->assertEqual($this->CommentWidget->prepareUrl($url), $expected);

		$this->Js->setReturnValue('get', $this->Js);
		$this->Js->setReturnValue('effect', '');
		
		$this->CommentWidget->options(array(
			'target' => 'placeholder',
			'ajaxAction' => 'add'));
		$expected['action'] = 'add';
		$this->assertEqual($this->CommentWidget->prepareUrl($url), $expected);
		
		$this->CommentWidget->options(array(
			'target' => 'placeholder',
			'ajaxAction' => array(
				'controller' => 'comments',
				'action' => 'add')));
		$expected = array(
			'controller' => 'comments',
			'action' => 'add',
			'my-first-article');
		$this->assertEqual($this->CommentWidget->prepareUrl($url), $expected);
	}
	
/**
 * Test allowAnonymousComment method
 * 
 * @return void
 */
	public function testAllowAnonymousComment() {
		$this->assertFalse($this->CommentWidget->globalParams['allowAnonymousComment']);
		$this->CommentWidget->options(array('allowAnonymousComment' => true));
		$this->assertTrue($this->CommentWidget->globalParams['allowAnonymousComment']);
	}
	
/**
 * Test element method
 * 
 * @return void
 */
	public function testElement() {
		$this->__mockView();
		$this->CommentWidget->options(array('theme' => 'flat'));

		$expectedParams = array(
			'comments/flat/view',
			array(
				'target' => false,
				'ajaxAction' => false,
				'displayUrlToComment' => false,
				'urlToComment' => '',
				'allowAnonymousComment'  => false,
				'url' => null,
				'ajaxOptions' => array(),
				'viewInstance' => null,
				'theme' => 'flat')
		);
		$expected = 'Comment element content';

		$this->View
			->expects($this->at(0))
			->method('element')
			->will($this->returnValue($expected));
		$this->assertEqual($this->CommentWidget->element('view'), $expected);

        // Test missing element in project elements path. The helper must try to search the element from the comments plugin
		
		$this->View
			->expects($this->at(1))
			->method('element')
			->will($this->returnValue('Not Found: /path/to/project/views/elements/comments/flat/view.ctp'));
        $this->assertEqual($this->CommentWidget->element('view'), 'Not Found: /path/to/project/views/elements/comments/flat/view.ctp');


		
		// Test params: they must be passed to the element "as is". Note that the theme has not effect on the element being fetched

        $this->View
			->expects($this->at(1))
			->method('element')
			->will($this->returnValue($expected));
        $this->assertEqual($this->CommentWidget->element('view', array('target' => 'wrapper', 'theme' => 'threaded')), $expected);

	}

/**
 * Mock the view object, update the CR and the testCase attribute with the mock object
 * 
 * @return void
 */
	private function __mockView() {
		// if (!class_exists('MockView')) {
			// Mock::generate('View');
		// }
		$this->View = $this->getMock('View', array(), array($this->Controller));
		
		$this->CommentWidget = new CommentWidgetHelper($this->View);
		$this->CommentWidget->Form = new FormHelper($this->View);
		$this->CommentWidget->Html = new HtmlHelper($this->View);
		$this->Js = new JsHelper($this->View);
		$this->CommentWidget->Js = $this->Js;
		$this->CommentWidget->params['action'] = 'view';

		// debug($this->View);
		// ClassRegistry::removeObject('view');
		// ClassRegistry::addObject('view', $this->View);
	}
}
