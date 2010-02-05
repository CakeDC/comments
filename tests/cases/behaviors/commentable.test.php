<?php
App::import('model', 'Comments.Comment');

if (!class_exists('Article')) {
	class Article extends CakeTestModel {

	/**
	 * 
	 */
		public $actsAs = array(
			'Comments.Commentable' => array(
				'commentModel' => 'Comments.Comment',
				'userModelAlias' => 'UserModel',
				'userModel' => 'User'));
	/**
	 * 
	 */
		public $useTable = 'articles';

	/**
	 * 
	 */
		public $name = 'Article';
	}
}

if (!class_exists('User')) {
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

		try {
			$this->Model->commentAdd(1);
			$this->fail();
		} catch (BlackHoleException $e) {
			$this->pass();
		}
		
		// If it's successfull, commentAdd returns the id of the newly created comment
		$options = array(
			'userId' => '47ea303a-3b2c-4251-b313-4816c0a800fa',
			'modelId' => '1',
			'modelName' => 'Article',
			'defaultTitle' => 'Specified default title',
			'data' => array(
				// The format here is incorrect. It must be "Comment => array(", default values must be used
				'body' => "Comment Test successful Captn!",
				'title' => 'Not the Default title'),
			'permalink' => 'http://testing.something.com');
		$result = $this->Model->commentAdd(0, $options);
		$this->assertFalse(empty($result));
		$this->assertTrue(is_string($result));
		$this->Model->Comment->id = $result;
		$this->assertEqual($this->Model->Comment->field('title'), $options['defaultTitle']);
		
		$this->Model->id = $options['modelId'];
		$oldCount = $this->Model->field('comments');
		$this->assertTrue(is_numeric($oldCount));
		
		// Testing adding a comment (approved by default)
		$options['data'] = array('Comment' => $options['data']);
		$result = $this->Model->commentAdd(0, $options);
		$this->assertTrue(is_string($result));
		$this->Model->Comment->id = $result;
		$this->assertEqual($this->Model->Comment->field('title'), $options['data']['Comment']['title']);
		$this->assertEqual($this->Model->field('comments'), ++$oldCount);
		
		// Test adding non approved comment
		$options['data']['Comment']['approved'] = 0;
		$result = $this->Model->commentAdd(0, $options);
		$this->assertTrue(is_string($result));
		$this->Model->id = $options['modelId'];
		$this->assertEqual($this->Model->field('comments'), $oldCount);
		
		// Test adding spam comment
		$options['data'] = array_merge($options['data'], array(
			'Other' => array(
				'title' => 'Free p0rn spam!')));
		$this->assertFalse($this->Model->commentAdd(0, $options));
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
	public function testCommentDelete() {
		$this->Model->id = 1;
		$initCounts = array(
			'Model' => $this->Model->field('comments'),
			'Comments' => $this->Model->Comment->find('count'));
		
		$this->assertTrue($this->Model->commentDelete(1));
		$this->assertEqual($this->Model->field('comments'), $initCounts['Model'] - 1);
		$this->assertEqual($this->Model->Comment->find('count'), $initCounts['Comments'] - 1);
		
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
		$options = array('userModel' => 'User');
		$result = $this->Model->commentBeforeFind($options);
		$expected = array(
			'Comment.approved' => 1,
			'Comment.is_spam' => array('clean', 'ham'));
		$this->assertEqual($result, $expected);
		
		$options = array_merge($options, array(
			'isAdmin' => true,
			'id' => 1));
		$result = $this->Model->commentBeforeFind($options);
		$expected = array(
			'Article.id' => 1,
			'Comment.is_spam' => array('clean', 'ham'));
		$this->assertEqual($result, $expected);
	}

}
?>
