<?php
/**
 * Copyright 2009-2010, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2009-2010, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Controller', 'Controller');
App::uses('CommentsComponent', 'Comments.Controller/Component');

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
		public $components = array('Session', 'Comments.Comments' => array('userModelClass' => 'User'), 'Cookie', 'Auth');

	/**
	 * Redirect url
	 * @var mixed
	 */
		public $redirectUrl = null;

	/**
	 *
	 */
		public function beforeFilter() {
			parent::beforeFilter();
			$this->Comments->userModel = 'UserModel';
		}

	/**
	 *
	 */
		public function redirect($url, $status = NULL, $exit = true) {
			$this->redirectUrl = $url;
		}

		public function callback_commentsToggleApprove($modelId, $commentId) {
			return $this->Comments->callback_toggleApprove($modelId, $commentId);
		}
	}
}

/**
 * Comments Component Test
 *
 * @package comments
 * @subpackage comments.tests.cases.components
 */
class CommentsComponentTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.Comments.comment',
		'plugin.Comments.user',
		'plugin.Comments.article');

/**
 * setUp method
 *
 * @return void
 */
	function setUp() {
		parent::setUp();
		if (!defined('FULL_BASE_URL')) {
			define('FULL_BASE_URL', 'http://');
		}
		$this->Request = new CakeRequest();
		$this->Controller = new ArticlesTestController($this->Request);
		$this->Controller->constructClasses();

		// $this->Collection = $this->getMock('ComponentCollection');
		// if (!class_exists('MockAuthComponent2')) {
		//	 $this->getMock('AuthComponent', array('user'), array(&$this->Collection), 'MockAuthComponent2');
		// }
		//
		// $this->AuthComponent = new MockAuthComponent2($this->Collection);
		// $this->AuthComponent->enabled = true;
		// $this->Controller->Auth = $this->AuthComponent;


	}

/**
 * tearDown method
 *
 * @return void
 */
	function tearDown() {
		parent::tearDown();
		unset($this->Controller);
		ClassRegistry::flush();
	}

/**
 * testInitialize
 *
 * @return void
 */
	public function testInitialize() {
		$this->Controller = new ArticlesTestController();
		$this->Controller->constructClasses();
		$this->Controller->Components->init($this->Controller);
		$this->Controller->Comments->initialize($this->Controller, array());
		$this->assertEquals($this->Controller->helpers, array(
			'Comments.CommentWidget', 'Time', 'Comments.Cleaner', 'Comments.Tree'));
		$this->assertTrue($this->Controller->Article->Behaviors->attached('Commentable'));
		$this->assertEquals($this->Controller->Comments->modelName, 'Article');
	}

/**
 * testStartup
 *
 * @return void
 */
	public function testStartup() {
		$this->Controller->Comments->initialize($this->Controller, array());
		// debug($this->Controller->Article->hasMany);
		$this->assertTrue(isset($this->Controller->Article->hasMany['Comment']));
		$this->Controller->Comments->unbindAssoc = true;
		// debug($this->Controller->Comments);
		$this->Controller->Comments->startup($this->Controller);
		$this->assertFalse(isset($this->Controller->Article->hasMany['Comment']));
		// debug($this->Controller->Article->hasMany);

		$User = ClassRegistry::init('User');

		$this->Controller->Session->write('Auth', $User->find('first', array('conditions' => array('id' => '47ea303a-3b2c-4251-b313-4816c0a800fa'))));

		//$this->assertFalse(isset($this->Controller->viewVars['isAuthorized']));
		$this->Controller->Comments->unbindAssoc = false;
		$this->Controller->Comments->startup($this->Controller);
		$this->assertTrue($this->Controller->viewVars['isAuthorized']);
		$this->Controller->Session->delete('Auth');
		unset($User);
	}

/**
 * testBeforeRender
 *
 * @return void
 */
	public function testBeforeRender() {
		$this->Controller->Comments->initialize($this->Controller, array());
		$this->__setupControllerData();
		$this->Controller->request->action = 'view';
		$this->Controller->Comments->beforeRender();
		$this->assertTrue(isset($this->Controller->viewVars['commentParams']));
		$this->assertTrue(is_array($this->Controller->viewVars['commentParams']));
		$this->assertEquals($this->Controller->viewVars['commentParams'], array(
			'displayType' => 'flat',
			'viewComments' => 'commentsData',
			'modelName' => 'Article',
			'userModel' => 'UserModel'));
	}

/**
 * testCallback_initType
 *
 * @return void
 */
	public function testCallback_initType() {
		$this->Controller->Comments->initialize($this->Controller, array());
		$this->Controller->Cookie->delete('Comments.Article');
		$this->Controller->passedArgs['comment_view_type'] = 'invalid_type';
		$this->assertEquals($this->Controller->Comments->callback_initType(), 'flat');

		$this->Controller->passedArgs['comment_view_type'] = 'tree';
		$this->assertEquals($this->Controller->Comments->callback_initType(), 'tree');

		unset($this->Controller->passedArgs['comment_view_type']);
		$this->assertEquals($this->Controller->Comments->callback_initType(), 'tree');

		$this->Controller->Cookie->write('Comments.Article', 'invalid_type');
		$this->assertEquals($this->Controller->Comments->callback_initType(), 'flat');
	}

/**
 * testCallback_view
 *
 * @return void
 */
	public function testCallback_view() {
		$this->Controller->Comments->initialize($this->Controller, array());
		$this->__setupControllerData();
		$this->Controller->Comments->callback_view('flat');
		$this->assertTrue(is_array($this->Controller->viewVars['commentsData']));
		$dataFlat = $this->Controller->viewVars['commentsData'];

		$this->__setupControllerData();
		$this->Controller->Comments->callback_view('non-existing-type');
		$this->assertTrue(is_array($this->Controller->viewVars['commentsData']));
		$this->assertEquals($this->Controller->viewVars['commentsData'], $dataFlat);

		$this->__setupControllerData();
		$this->Controller->viewVars = null;
		try {
			$this->Controller->Comments->callback_view('flat');
			$this->fail();
		} catch(Exception $e) {
			//$this->pass();
		}

		$this->__setupControllerData();
		$this->Controller->Article->unbindModel(
			array('hasMany' => array('Comment')));
		try {
			$this->Controller->Comments->callback_view('flat');
			$this->fail();
		} catch(Exception $e) {
			//$this->pass();
		}
	}

/**
 * testCallback_fetchDataTree
 *
 * @return void
 */
	public function testCallback_fetchDataTree() {
		$this->Controller->Comments->initialize($this->Controller, array());
		$this->__setupControllerData();
		$result = $this->Controller->Comments->callback_fetchDataTree(array(
			'id' => 1));
		$this->assertTrue(!empty($result));
		$this->assertEquals($result[0]['Comment']['model'], 'Article');
		$this->assertEquals($result[0]['Comment']['foreign_key'], 1);
	}

/**
 * testCallback_fetchDataFlat
 *
 * @return void
 */
	public function testCallback_fetchDataFlat() {
		$this->Controller->Comments->initialize($this->Controller, array());
		$this->__setupControllerData();
		$result = $this->Controller->Comments->callback_fetchDataFlat(array(
			'id' => 1));
		$this->assertTrue(!empty($result));
		$this->assertEquals($result[0]['Comment']['model'], 'Article');
		$this->assertEquals($result[0]['Comment']['foreign_key'], 1);
	}

/**
 * testCallback_fetchDataThreaded
 *
 * @return void
 */
	public function testCallback_fetchDataThreaded() {
		$this->Controller->Comments->initialize($this->Controller, array());
		$this->__setupControllerData();
		$result = $this->Controller->Comments->callback_fetchDataThreaded(array(
			'id' => 1));
		$this->assertTrue(!empty($result));
		$this->assertTrue(is_array($result[0]['children']));
		$this->assertEquals($result[0]['Comment']['foreign_key'], 1);
	}

/**
 * testCallback_fetchData
 *
 * @return void
 */
	public function testCallback_fetchData() {
		$this->Controller->Comments->initialize($this->Controller, array());
		$this->__setupControllerData();
		$result = $this->Controller->Comments->callback_fetchData(array(
			'id' => 1));
		// The behavior must be the same than callback_fetchDataFlat as it is just an alias
		$this->assertTrue(!empty($result));
		$this->assertEquals($result[0]['Comment']['model'], 'Article');
		$this->assertEquals($result[0]['Comment']['foreign_key'], 1);
	}

/**
 * testCallback_fetchDataThreaded
 *
 * @return void
 */
	public function testCallback_prepareParams() {
		$this->Controller->Comments->initialize($this->Controller, array());
		$this->assertEquals($this->Controller->Comments->commentParams, array());
		$this->Controller->Comments->callback_prepareParams();
		$expected = array(
			'viewComments' => 'commentsData',
			'modelName' => 'Article',
			'userModel' => 'UserModel');
		$this->assertEquals($this->Controller->Comments->commentParams, $expected);

		$this->__setupControllerData();
		$this->Controller->passedArgs['comment_action'] = 'view';
		$this->Controller->Comments->callback_prepareParams();
		$expected = array_merge($expected, array(
			'userModel' => 'UserModel',
			'comment_action' => 'view'));
		$this->assertEquals($this->Controller->Comments->commentParams, $expected);
	}

/**
 * testCallback_add
 *
 * @return void
 */
	public function testCallback_add() {
		$this->Controller->Comments->initialize($this->Controller, array());
		$data = array(
			'Comment' => array(
				'title' => 'My first comment <script>XSS</script>',
				'body' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.')
		);
		$this->__setupControllerData();
		$this->Controller->data = $data;
		$User = ClassRegistry::init('User');
		$this->Controller->passedArgs[1] = '123';
		$this->Controller->Session->write('Auth', $User->find('first', array('conditions' => array('id' => '47ea303a-3b2c-4251-b313-4816c0a800fa'))));
		$this->Controller->Article->id = 1;
		$oldCount = $this->Controller->Article->field('comments');

		$this->Controller->Comments->callback_add(1, 1, 'flat');
		$created = $this->Controller->Article->Comment->find('first', array('order' => 'created DESC'));
		$expected = array(
			'approved' => 1,
			'body' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
			'foreign_key' => 1,
			'model' => 'Article',
			'parent_id' => 1,
			'title' => 'My first comment XSS',
			'user_id' => '47ea303a-3b2c-4251-b313-4816c0a800fa',
		);
		$result = array();
		foreach ($created['Comment'] as $key => $value) {
			if (isset($expected[$key])) {
				$result[$key] = $value;
			}
		}
		$this->assertTrue(is_array($created));
		$this->assertNotEqual($created['Comment']['id'], 3);
		ksort($result);
		$this->assertEquals($result, $expected);
		$this->assertEquals($this->Controller->Session->read('Message.flash.message'), 'The Comment has been saved.');
		$this->assertEquals($this->Controller->redirectUrl, array('123', '#' => 'comment' . $created['Comment']['id']));
		$this->assertEquals($this->Controller->Article->field('comments'), $oldCount + 1);

		$this->Controller->Session->delete('Message.flash.message');
		$this->Controller->Session->delete('Auth');
		unset($User);
	}

/**
 * testCallback_add
 *
 * @return void
 */
	public function testCallback_add_InAjaxMode() {
		$this->Controller->Comments->initialize($this->Controller, array());
		$data = array(
			'Comment' => array(
				'title' => 'My first comment <script>XSS</script>',
				'body' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.')
		);
		$this->__setupControllerData();
		$this->Controller->data = $data;
		$User = ClassRegistry::init('User');
		$this->Controller->passedArgs[1] = '123';
		$this->Controller->Session->write('Auth', $User->find('first', array('conditions' => array('id' => '47ea303a-3b2c-4251-b313-4816c0a800fa'))));
		$this->Controller->Article->id = 1;
		$oldCount = $this->Controller->Article->field('comments');

		$this->Controller->params['isAjax'] = true;
		$this->Controller->Comments->commentParams['displayType'] = 'flat';
		$this->Controller->Comments->callback_add(1, 1, 'flat');
		$created = $this->Controller->Article->Comment->find('first', array('order' => 'Comment.created DESC'));
		$expected = array(
			'approved' => 1,
			'body' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
			'foreign_key' => 1,
			'model' => 'Article',
			'parent_id' => 1,
			'title' => 'My first comment XSS',
			'user_id' => '47ea303a-3b2c-4251-b313-4816c0a800fa',
		);
		$result = array();
		foreach ($created['Comment'] as $key => $value) {
			if (isset($expected[$key])) {
				$result[$key] = $value;
			}
		}
		$this->assertTrue(is_array($created));
		$this->assertNotEqual($created['Comment']['id'], 3);
		ksort($result);
		$this->assertEquals($result, $expected);
		$this->assertEquals($this->Controller->viewVars['messageTxt'], 'The Comment has been saved.');
		$this->assertEquals($this->Controller->redirectUrl, null);
		$this->assertEquals($this->Controller->viewVars['redirect'], null);
		//array('123', '#' => 'comment' . $created['Comment']['id'])
		$this->assertEquals($this->Controller->Article->field('comments'), $oldCount + 1);

		$this->Controller->Session->delete('Message.flash.message');
		$this->Controller->Session->delete('Auth');
		unset($User);
		unset($this->Controller->params['isAjax']);
	}

/**
 * testCallback_add
 *
 * @return void
 */
	public function testCallback_toggleApprove() {
		$this->Controller->Comments->initialize($this->Controller, array());
		$this->__setupControllerData();
		$this->Controller->Article->id = 1;
		$oldCount = $this->Controller->Article->field('comments');

		try {
			$this->Controller->Comments->callback_toggleApprove(1, 1);
			$this->fail();
		} catch (BlackHoleException $e) {
			//$this->pass();
		}

		$this->Controller->passedArgs['comment_action'] = 'toggle_approve';
		
		$User = ClassRegistry::init('User');
		$this->Controller->Session->write('Auth', $User->find('first', array('conditions' => array('id' => '47ea303a-3b2c-4251-b313-4816c0a800fa'))));

		$this->Controller->Comments->callback_toggleApprove(1, 1);
		$comment = $this->Controller->Article->Comment->findById(1);
		$this->assertEquals($comment['Comment']['approved'], false);
		$this->assertEquals($this->Controller->Session->read('Message.flash.message'), 'The Comment status has been updated.');
		$this->assertEquals($this->Controller->Article->field('comments'), $oldCount - 1);
		$this->assertNull($this->Controller->redirectUrl);

		$this->Controller->Comments->callback_toggleApprove(1, 'unexisting-id');
		$this->assertEquals($this->Controller->Session->read('Message.flash.message'), 'Error appear during comment status update. Try later.');
		$this->assertNull($this->Controller->redirectUrl);

		$this->__setupControllerData();
		$this->Controller->passedArgs['comment_action'] = 'toggle_approve';
		$this->Controller->passedArgs['comment'] = 1;

		$this->Controller->Comments->callback_view('flat');
		$comment = $this->Controller->Article->Comment->findById(1);
		$this->assertEquals($comment['Comment']['approved'], true);
		$this->assertEquals($this->Controller->Session->read('Message.flash.message'), 'The Comment status has been updated.');
		$this->assertEquals($this->Controller->Article->field('comments'), $oldCount);
		$this->assertNull($this->Controller->redirectUrl);
		$this->assertTrue(is_array($this->Controller->viewVars['commentsData']));


		// user1 is admin
		$this->Controller->passedArgs['comment_action'] = 'toggle_approve';
		try {
			//$this->Controller->Comments->callback_toggleApprove(1, 1);
		} catch (BlackHoleException $e) {
			$this->fail('Fail toggle approve 1,1');
			//$this->pass();
		}

		
		$this->Controller->Session->delete('Message.flash.message');
		$this->Controller->Session->delete('Auth');
		unset($User);
		
		
	}

/**
 * testCallback_delete
 *
 * @return void
 */
	public function testCallback_delete() {
		$this->Controller->Comments->initialize($this->Controller, array());
		$this->__setupControllerData();
		$this->Controller->Article->id = 1;
		$oldCount = $this->Controller->Article->field('comments');

		$this->Controller->Comments->callback_delete(1, 1);
		$comment = $this->Controller->Article->Comment->findById(1);
		$this->assertFalse($comment);
		$this->assertEquals($this->Controller->Session->read('Message.flash.message'), 'The Comment has been deleted.');
		$this->assertEquals($this->Controller->Article->field('comments'), $oldCount - 1);
		$this->assertEquals($this->Controller->redirectUrl, array());

		$this->Controller->Comments->callback_delete(1, 'unexisting-id');
		$this->assertEquals($this->Controller->Session->read('Message.flash.message'), 'Error appear during comment deleting. Try later.');
		$this->assertEquals($this->Controller->redirectUrl, array());

		$this->Controller->Session->delete('Message.flash.message');
	}

/**
 * testFlash
 *
 * @return void
 */
	public function testFlash() {
		$this->Controller->Comments->initialize($this->Controller, array());
		$message = 'Test Message';

		$this->Controller->params['isAjax'] = false;
		$this->Controller->Comments->flash($message);
		$this->assertEquals($this->Controller->Session->read('Message.flash.message'), $message);

		$this->Controller->params['isAjax'] = true;
		$this->Controller->Comments->flash('Test Message');
		$this->assertEquals($this->Controller->viewVars['messageTxt'], $message);
	}

/**
 * testRedirect
 *
 * @return void
 */
	public function testRedirect() {
		$this->Controller->Comments->initialize($this->Controller, array());
		$url = array('controller' => 'tests', 'action' => 'index');

		$this->Controller->params['isAjax'] = false;
		$this->Controller->Comments->redirect($url);
		$this->assertEquals($this->Controller->redirectUrl, $url);

		$this->Controller->params['isAjax'] = true;
		$this->Controller->Comments->redirect($url);
		$this->assertEquals($this->Controller->viewVars['redirect'], $url);
		$this->assertEquals($this->Controller->viewVars['ajaxMode'], true);
	}

/**
 * testRedirect with passed arguments to be persisted in params
 * Named parameters used internally by the component must not be persisted as it can
 * lead to infinite loops
 *
 * @return void
 */
	public function testRedirectPersistParams() {
		// Here 'comment_action' => 'add' is a named param used internally
		$this->Controller->Comments->initialize($this->Controller, array());
		$this->Controller->passedArgs = array('foo', 'custom' => 'important', 'comment_action' => 'add');
		$url = array('controller' => 'tests', 'action' => 'index', 'other' => 'value');
		$expected = array('controller' => 'tests', 'action' => 'index', 'foo', 'custom' => 'important', 'other' => 'value');

		$this->Controller->params['isAjax'] = false;
		$this->Controller->Comments->redirect($url);
		$this->assertEquals($this->Controller->redirectUrl, $expected);

		$this->Controller->params['isAjax'] = true;
		$this->Controller->Comments->redirect($url);
		$this->assertEquals($this->Controller->viewVars['redirect'], $expected);
		$this->assertEquals($this->Controller->viewVars['ajaxMode'], true);
	}

/**
 * testFlash
 *
 * @return void
 */
	public function testPermalink() {
		$this->Request = new CakeRequest('/articles/view/1');
		$this->Controller = new ArticlesTestController($this->Request);
		$this->Controller->constructClasses();
		$this->__setupControllerData();
		debug($this->Controller);
		$this->Controller->request->params = array(
			'name' => array(
				'controller' => 'articles',
				'action' => 'view'),
			'named' => array(
				'testnamed' => 'test'));
		$this->Controller->params = array(
			'url' => '/articles/view/1',
			'name' => array(
				'controller' => 'articles',
				'action' => 'view'),
			'named' => array(
				'testnamed' => 'test'));
		$this->Controller->Comments->initialize($this->Controller, array());
		$result = $this->Controller->Comments->permalink();
		$expected = 'http://' . env('HTTP_HOST') . '/articles/view/testnamed:test';
		debug(array($result, $expected));
		$this->assertEquals($result, $expected);
	}

/**
 * Setup fake controller data
 *
 * @return void
 */
	protected function __setupControllerData() {
		$this->Controller->params = array(
			'named' => array(),
			'url' => array());
		$this->Controller->request->params = array(
			'named' => array(),
			'url' => array());
		$this->Controller->Article->id = 1;
		$this->Controller->viewVars['article'] = array(
			'Article' => array(
				'id' => 1));
		$this->Controller->Comments->Controller = $this->Controller;
	}
}
