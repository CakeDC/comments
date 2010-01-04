<?php
App::import('Controller', 'Controller', false);
App::import('Component', 'Comments.Comments');

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
	public $components = array('Comments.Comments', 'Cookie');

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


class CommentsComponentTest extends CakeTestCase {
/**
 * Fixtures
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
	function startTest() {
		$this->Controller = new ArticlesTestController();
		$this->Controller->constructClasses();
		$this->Controller->Component->init($this->Controller);
		$this->Controller->Component->initialize($this->Controller);
	}

/**
 * tearDown method
 *
 * @access public
 * @return void
 */
	function endTest() {
		unset($this->Controller);
		ClassRegistry::flush();
	}

/**
 * testInitialize
 *
 * @access public
 * @return void
 */
	public function testInitialize() {
		$this->Controller = new ArticlesTestController();
		$this->Controller->constructClasses();
		$this->Controller->Component->init($this->Controller);
		$this->Controller->Component->initialize($this->Controller);
		$this->assertEqual($this->Controller->helpers, array(
			'Html', 'Form', 'Comments.CommentWidget'));
		$this->assertTrue($this->Controller->Article->Behaviors->attached('Commentable'));
		$this->assertEqual($this->Controller->Comments->modelName, 'Article');
	}

/**
 * testInitialize
 *
 * @access public
 * @return void
 */
	public function testStartup() {
		
	}

/**
 * testBeforeRender
 *
 * @access public
 * @return void
 */
	public function testBeforeRender() {
		
	}

/**
 * testCallback_initType
 *
 * @access public
 * @return void
 */
	public function testCallback_initType() {
		$this->Controller->Cookie->delete('Comments.Article');
		$this->Controller->passedArgs['comment_view_type'] = 'invalid_type';
		$this->assertEqual($this->Controller->Comments->callback_initType(), 'flat');

		$this->Controller->passedArgs['comment_view_type'] = 'tree';
		$this->assertEqual($this->Controller->Comments->callback_initType(), 'tree');

		unset($this->Controller->passedArgs['comment_view_type']);
		$this->assertEqual($this->Controller->Comments->callback_initType(), 'tree');

		$this->Controller->Cookie->write('Comments.Article', 'invalid_type');
		$this->assertEqual($this->Controller->Comments->callback_initType(), 'flat');
	}

/**
 * testCallback_view
 *
 * @access public
 * @return void
 */
	public function testCallback_view() {
		
	}

/**
 * testCallback_fetchDataTree
 *
 * @access public
 * @return void
 */
	public function testCallback_fetchDataTree() {
		
	}

/**
 * testCallback_fetchDataFlat
 *
 * @access public
 * @return void
 */
	public function testCallback_fetchDataFlat() {
		
	}

/**
 * testCallback_fetchDataThreaded
 *
 * @access public
 * @return void
 */
	public function testCallback_fetchDataThreaded() {
		
	}

/**
 * testCallback_fetchData
 *
 * @access public
 * @return void
 */
	public function testCallback_fetchData() {
		
	}

/**
 * testCallback_fetchDataThreaded
 *
 * @access public
 * @return void
 */
	public function testCallback_prepareParams() {
		
	}

/**
 * testCallback_add
 *
 * @access public
 * @return void
 */
	public function testCallback_add() {
		
	}

/**
 * testCallback_add
 *
 * @access public
 * @return void
 */
	public function testCallback_toggleApprove() {
		
	}

/**
 * testCallback_delete
 *
 * @access public
 * @return void
 */
	public function testCallback_delete() {
		
	}

/**
 * testFlash
 *
 * @access public
 * @return void
 */
	public function testFlash() {
		
	}

/**
 * testFlash
 *
 * @access public
 * @return void
 */
	public function testRedirect() {
		
	}

/**
 * testFlash
 *
 * @access public
 * @return void
 */
	public function testPermalink() {
		$this->Controller->params = array(
			'named' => array(
				'controller' => 'articles',
				'action' => 'view',
				'testnamed' => 'test'));
		$this->assertEqual($this->Controller->Comments->permalink(), 'http://' . env('HTTP_HOST') . '/articles/view/testnamed:test');
	}

}
?>