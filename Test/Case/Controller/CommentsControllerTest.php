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

App::uses('CommentsController', 'Comments.Controller');
App::uses('Comment', 'Comments.Model');

if (!class_exists('User')) {
	class User extends CakeTestModel {
		public $name = 'User';
	}
}

/**
 * Test Comments Controller
 *
 * @package comments
 * @subpackage comments.tests.cases.controllers
 */
class TestCommentsController extends CommentsController {

/**
 * Name
 *
 * @var string
 */
	public $name = 'Comments';

/**
 * Auto render
 * @var boolean
 */
	public $autoRender = false;

/**
 * Rendered view
 * @var string
 */
	public $renderedView = null;

/**
 * Redirect URL
 *
 * @var mixed
 */
	public $redirectUrl = null;

/**
 * Cake error method logged when cakeError is triggered
 *
 * @var string
 */
	public $cakeErrorMethod = null;

/**
 * Override controller method for testing
 */
	public function redirect($url, $status = null, $exit = true) {
		$this->redirectUrl = $url;
	}

/**
 * Override controller method for testing
 */
	public function render($action = null, $layout = null, $file = null) {
		$this->renderedView = $action;
	}

/**
 * Override controller method for testing
 */
	public function cakeError($method, $messages = array()) {
		$this->cakeErrorMethod = $method;
	}
}

/**
 * Comments Controller Test
 *
 * @package comments
 * @subpackage comments.tests.cases.controllers
 */
class CommentsControllerTest extends CakeTestCase {

/**
 * Controller being tested
 * @var TestCommentsController
 */
	public $Comments = null;

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.Comments.comment',
		'plugin.Comments.user',
		'plugin.Comments.article'
	);

/**
 * (non-PHPdoc)
 */
	public function setUp() {
		parent::setUp();
		$this->Request = new CakeRequest();
		$this->Response = new CakeResponse();
		$this->Request->params = array(
			'named' => array(),
			'pass' => array(),
			'url' => array());
		$this->Comments = new TestCommentsController($this->Request, $this->Response);
		$this->Comments->constructClasses();
		$this->Comments->startupProcess();
		$this->Comments->Comments->initialize($this->Comments);
		$this->Comments->Comment = ClassRegistry::init('Comments.Comment');
		debug(ClassRegistry::init('Comments.Comment'));
	}

/**
 * (non-PHPdoc)
 * @see cake/tests/lib/CakeTestCase#endTest($method)
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Comments);
	}

/**
 * Test Controller instance
 *
 * @return void
 */
	public function testCommentsControllerInstance() {
		$this->assertTrue(is_a($this->Comments, 'CommentsController'));
	}

/**
 * Test admin_index action
 *
 * @return void
 */
	public function testAdminIndex() {
		$this->Comments->admin_index();
		$this->assertEqual(count($this->Comments->viewVars['comments']), 4);
		$this->assertEqual($this->Comments->viewVars['comments'][0]['Comment']['id'], 1);

		$this->Comments->admin_index('clean');
		$this->assertEqual(count($this->Comments->viewVars['comments']), 3);

		$this->Comments->admin_index(null);
		$this->assertEqual(count($this->Comments->viewVars['comments']), 4);
	}

/**
 * Test admin_process action
 *
 * @return void
 */
	public function _testAdminProcessDelete() {
		$this->Comments->data['Comment'] = array(
			'1' => 1,
			'2' => 0,
			'3' => 0,
			'action' => 'delete');
		$this->Comments->admin_process();
		$comment1 = $this->Comments->Comment->findById(1);
		$this->assertFalse($comment1);
		$comment2 = $this->Comments->Comment->findById(2);
		$this->assertIsA($comment2, 'Array');
	}

	public function testAdminProcessHam() {
		$this->Comments->request->data['Comment'] = array(
			'1' => 1,
			'2' => 0,
			'action' => 'ham');
		$this->Comments->admin_process();
		$comment1 = $this->Comments->Comment->findById(1);
		$this->assertEqual($comment1['Comment']['is_spam'], 'ham');
	}

	public function testAdminProcessSpam() {
		$this->Comments->request->data['Comment'] = array(
			'1' => 1,
			'2' => 0,
			'action' => 'spam');
		$this->Comments->admin_process();
		$comment1 = $this->Comments->Comment->findById(1);
		$this->assertEqual($comment1['Comment']['is_spam'], 'spammanual');
	}

	public function testAdminProcessApprove() {
		$this->Comments->request->data['Comment'] = array(
			'2' => 0,
			'3' => 1,
			'action' => 'approve');
		$this->Comments->admin_process();
		$comment = $this->Comments->Comment->findById(3);
		$this->assertEqual($comment['Comment']['approved'], 1);
	}

	public function testAdminProcessDisapprove() {
		$this->Comments->request->data['Comment'] = array(
			'1' => 1,
			'2' => 0,
			'action' => 'disapprove');
		$this->Comments->admin_process();
		$comment = $this->Comments->Comment->findById(1);
		$this->assertEqual($comment['Comment']['approved'], 0);
	}

/**
 * Test admin_spam action
 *
 * @return void
 */
	public function testAdminSpam() {
		$this->Collection = $this->getMock('ComponentCollection');
		$this->Comments->Session = $this->getMock('SessionComponent', array('setFlash'), array($this->Collection));
		$this->Comments->Session->expects($this->once())
			->method('setFlash')
			->with(__d('comments', 'Wrong comment id'));
		$this->Comments->admin_spam(3232);
		$this->assertEqual($this->Comments->redirectUrl, array('action' => 'index'));

		$Article = ClassRegistry::init('Article');
		$items = Hash::extract($Article->read(array('Article.comments'), 1), '/Article/comments');
		$oldCount = array_shift($items);

		$this->Comments->Session = $this->getMock('SessionComponent', array('setFlash'), array($this->Collection));
		$this->Comments->Session->expects($this->once())
			->method('setFlash')
			->with(__d('comments', 'Antispam system informed about spam message.'));

		$this->Comments->admin_spam(1);
		$this->assertEqual($this->Comments->redirectUrl, array('action' => 'index'));

		$commentFlag = $this->Comments->Comment->read(array('Comment.is_spam'), 1);
		$this->assertEqual($commentFlag['Comment']['is_spam'], 'spammanual');
		$items = Hash::extract($Article->read(array('Article.comments'), 1), '/Article/comments');
		$newCount = array_shift($items);
		$this->assertEqual($newCount, $oldCount - 1);
	}

/**
 * Test admin_ham action
 *
 * @return void
 */
	public function testAdminHam() {
		$this->Collection = $this->getMock('ComponentCollection');
		$this->Comments->Session = $this->getMock('SessionComponent', array('setFlash'), array($this->Collection));
		$this->Comments->Session->expects($this->once())
			->method('setFlash')
			->with(__d('comments', 'Wrong comment id'));
		$this->Comments->admin_ham('invalid-comment');
		$this->assertEqual($this->Comments->redirectUrl, array('action' => 'index'));

		$Article = ClassRegistry::init('Article');
		$items = Hash::extract($Article->read(array('Article.comments'), 2), '/Article/comments');
		$oldCount = array_shift($items);

		$this->Collection = $this->getMock('ComponentCollection');
		$this->Comments->Session = $this->getMock('SessionComponent', array('setFlash'), array($this->Collection));
		$this->Comments->Session->expects($this->any())
			->method('setFlash')
			->with(__d('comments', 'Antispam system informed about ham message.'));
		$this->Comments->admin_ham(3);
		$this->assertEqual($this->Comments->redirectUrl, array('action' => 'index'));

		//$this->assertEqual($this->Comments->Session->read('Message.flash.message'), 'Antispam system informed about ham message.');
		$commentFlag = $this->Comments->Comment->read(array('Comment.is_spam'), 3);
		$this->assertEqual($commentFlag['Comment']['is_spam'], 'ham');

		$items = Hash::extract($Article->read(array('Article.comments'), 2), '/Article/comments');
		$newCount = array_shift($items);

		$this->assertEqual($newCount, $oldCount + 1);
		$this->Comments->Session->delete('Message.flash.message');
	}

/**
 * Test admin_view action
 *
 * @return void
 */
	public function testAdminView() {
		$this->Collection = $this->getMock('ComponentCollection');
		$this->Comments->Session = $this->getMock('SessionComponent', array('setFlash'), array($this->Collection));
		$this->Comments->Session->expects($this->once())
			->method('setFlash')
			->with(__d('comments', 'Invalid Comment.'));
		$this->Comments->admin_view('invalid-comment');
		$this->assertEqual($this->Comments->redirectUrl, array('action' => 'index'));
		$this->Comments->admin_view(1);
		$this->assertEqual($this->Comments->viewVars['comment']['Comment']['id'], 1);
	}

/**
 * Test admin_delete action
 *
 * @return void
 */
	public function testAdminDelete() {
		$this->Collection = $this->getMock('ComponentCollection');
		$this->Comments->Session = $this->getMock('SessionComponent', array('setFlash'), array($this->Collection));
		$this->Comments->Session->expects($this->once())
			->method('setFlash')
			->with(__d('comments', 'Invalid id for Comment'));
		$this->Comments->admin_delete('invalid-comment');
		$this->assertEqual($this->Comments->redirectUrl, array('action' => 'index'));

		$Article = ClassRegistry::init('Article');
		$items = Hash::extract($Article->read(array('Article.comments'), 1), '/Article/comments');
		$oldCount = array_shift($items);

		$this->Collection = $this->getMock('ComponentCollection');
		$this->Comments->Session = $this->getMock('SessionComponent', array('setFlash'), array($this->Collection));
		$this->Comments->Session->expects($this->once())
			->method('setFlash')
			->with(__d('comments', 'Comment deleted'));
		$this->Comments->admin_delete(1);
		$this->assertEqual($this->Comments->redirectUrl, array('action' => 'index'));
		$items = Hash::extract($Article->read(array('Article.comments'), 1), '/Article/comments');
		$newCount = array_shift($items);

		$this->assertEqual($newCount, $oldCount - 1);
	}

/**
 * Test requestForUser action
 *
 * @return void
 */
	public function testRequestForUser() {
		$this->Comments->requestForUser();
		$this->assertEqual($this->Comments->cakeErrorMethod, '404');

		$this->Comments->request->params['requested'] = true;
		$this->Comments->requestForUser();
		$ids = Hash::extract($this->Comments->viewVars['comments'], '/Comment/id');
		$this->assertEqual($ids, array(1, 2));
		$this->assertEqual($this->Comments->renderedView, 'comment');

		$this->Comments->requestForUser(null, 1);
		$ids = Hash::extract($this->Comments->viewVars['comments'], '/Comment/id');
		$this->assertEqual($ids, array(1));

		$this->Comments->requestForUser('47ea303a-3b2c-4251-b313-4816c0a800fa');
		$ids = Hash::extract($this->Comments->viewVars['comments'], '/Comment/id');
		$this->assertEqual($ids, array(4));
		$this->assertEqual($this->Comments->viewVars['userId'], '47ea303a-3b2c-4251-b313-4816c0a800fa');

		$this->Comments->request->params['named']['model'] = 'Other';
		$this->Comments->requestForUser();
		$this->assertTrue(empty($this->Comments->viewVars['comments']));
	}

}

