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


class CommentsComponentTest extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 * @access public
 */
	public $fixtures = array(
		'plugin.utils.article');
/**
 * setUp method
 *
 * @access public
 * @return void
 */
	function startTest() {
		$this->Controller = new ArticlesTestController();
		$this->Controller->constructClasses();
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
 * testPermalink method
 *
 * @todo finish it
 * @access public
 * @return void
 */
	public function testPermalink() {
		/*
		$this->Controller->params = array(
			'pass' => array(
				1),
			'named' => array(
				'controller' => 'Article',
				'action' => 'view'));
		$result = $this->Controller->Comments->permalink();
		$this->assertEqual($result, 'http://' . env('HTTP_HOST') . '/');
		*/
	}

}
?>