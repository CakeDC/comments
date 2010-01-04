<?php
App::import('model', 'Comments.Comment');

class Article extends CakeTestModel {

/**
 * 
 */
	public $actsAs = array(
		'Comments.Commentable' => array(
			'commentModel' => 'Comments.Comment'));
/**
 * 
 */
	public $useTable = 'articles';

/**
 * 
 */
	public $name = 'Article';
}

class User extends CakeTestModel {
/**
 * 
 */
	public $useTable = 'users';

/**
 * 
 */
	public $name = 'User';
}

/**
 * 
 */
class CommentableTest extends CakeTestCase {

/**
 * 
 */
	public $fixtures = array(
		'plugin.comments.comment',
		'plugin.comments.user',
		'plugin.comments.article');

/**
 * 
 */
	public $submitOptions = array(
		'userId' => '47ea303a-3b2c-4251-b313-4816c0a800fa',
		'modelId' => 'f4b367a0-d022-11dd-99bf-00e018bfb339',
		'modelName' => 'Article',
		'defaultTitle' => 'Specified default title',
		'data' => array(
			'body' => "Comment Test successful Captn!",
			'title' => 'Not the Default title'),
		'permalink' => 'http://testing.something.com');

/**
 * Model
 *
 * @var object
 * @access public
 */
	public $Model = null;

/**
 * Start test
 *
 * @return void
 * @access public
 */
	public function startTest() {
		$this->Model = Classregistry::init('Article');
		$this->Model->Comment->bindModel(array(
			'belongsTo' => array(
				'User')));
	}

/**
 * End test
 *
 * @return void
 * @access public
 */
	public function endTest() {
		unset($this->Model);
		ClassRegistry::flush(); 
	}

/**
 * Test behavior instance
 *
 * @return void
 * @access public
 */
	public function testBehaviorInstance() {
		$this->assertTrue(is_a($this->Model->Behaviors->Commentable, 'CommentableBehavior'));
	}

/**
 * testCommentAdd
 *
 * @return void
 * @access public
 */
	public function testCommentAdd(){ 
		//No data
		$expected = null;
		$this->assertEqual($expected, $this->Model->commentAdd(0));

		//Empty Data
		$expected = false;
		$this->assertEqual($expected, $this->Model->commentAdd(0, array()));

		//If it's successfull, commentAdd returns the id of the newly created comment
		$result = $this->Model->commentAdd(0, $this->submitOptions);
		$this->assertTrue(!empty($result));
		$this->assertTrue(is_string($result));


		try {
			$this->Model->commentAdd(1);
		} catch (BlackHoleException $e) {
			$this->pass();
		}
	}

/**
 * testCommentToggleApprove
 *
 * @return void
 * @access public
 */
	public function testCommentToggleApprove() {
		$comment = $this->Model->Comment->find('first');
		$this->assertEqual($comment['Comment']['approved'], 1);
		$this->assertTrue($this->Model->commentToggleApprove($comment['Comment']['id']));
		$comment = $this->Model->Comment->find('first');
		$this->assertEqual($comment['Comment']['approved'], 0);
		$this->assertTrue($this->Model->commentToggleApprove($comment['Comment']['id']));
		$comment = $this->Model->Comment->find('first');
		$this->assertEqual($comment['Comment']['approved'], 1);


		$this->assertFalse($this->Model->commentToggleApprove(21415));
	}

/**
 * commentDelete
 *
 * @return void
 * @access public
 */
	public function commentDelete() {
		$this->assertTrue($this->Model->commentDelete(1));
		$this->assertFalse($this->Model->commentDelete('does-not-exist'));
	}

/**
 * commentDelete
 *
 * @return void
 * @access public
 */
	public function testChangeCommentCount() {
		$this->assertTrue($this->Model->changeCommentCount('1', 'up'));
		$article = $this->Model->findById(1);
		$this->assertEqual($article['Article']['comments'], 3);
		$this->assertTrue($this->Model->changeCommentCount('1', 'down'));
		$article = $this->Model->findById(1);
		$this->assertEqual($article['Article']['comments'], 2);


		$this->assertFalse($this->Model->changeCommentCount('1', 'invalid'));
		$this->assertFalse($this->Model->changeCommentCount('invalid!', 'up'));
	}

/**
 * testCommentBeforeFind
 *
 * @return void
 * @access public
 */
	public function testCommentBeforeFind() {
		$result = $this->Model->commentBeforeFind(array('userModel' => 'User', 'id' => '1'));
		$this->assertIsA($result, 'array');
		//debug($result);
	}

}
?>