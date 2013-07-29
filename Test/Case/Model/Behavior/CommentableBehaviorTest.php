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

App::uses('Comment', 'Comments.Model');
App::uses('ModelBehavior', 'Model');

App::uses('CakeEventManager', 'Event');
App::uses('CommentEventListener', 'Comments.Test/Lib');


if (!class_exists('Article')) {
	class Article extends CakeTestModel {

/**
 * Callback data
 *
 * @param array
 */
		public $callbackData = array();

/**
 * Behaviors
 *
 * @param array
 */
		public $actsAs = array(
			'Comments.Commentable' => array(
				'commentModel' => 'Comments.Comment',
				'userModelAlias' => 'UserModel',
				'userModel' => 'User'));

/**
 * Table name
 *
 * @param string
 */
		public $useTable = 'articles';

/**
 * Name
 *
 * @param string
 */
		public $name = 'Article';
	}
}

if (!class_exists('ArticleIdNotDefault')) {
	class ArticleIdNotDefault extends CakeTestModel {
		public $callbackData = array();
		public $actsAs = array(
			'Comments.Commentable' => array(
				'commentModel' => 'Comments.Comment',
				'userModelAlias' => 'UserModel',
				'userModel' => 'User'));
		public $useTable = 'articles_not_default_id';
		public $name = 'ArticleIdNotDefault';
		public $primaryKey = 'articleidnotdefault_id';
	}
}

if (!class_exists('Article2')) {
	class Article2 extends CakeTestModel {

	/**
	 *
	 */
		public $callbackData = array();

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
		public $name = 'Article2';

/**
 * Before comment callback
 */
		public function beforeComment(&$data) {
			$data['Comment']['title'] = 'Changed in beforeComment!';
			$this->callbackData['beforeComment'] = $data;
			return true;
		}

/**
 * Before comment callback
 */
		public function afterComment(&$data) {
			$data['Comment']['body'] = 'Changed in afterComment!';
			$this->callbackData['afterComment'] = $data;
			return true;
		}
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
		'plugin.Comments.comment',
		'plugin.Comments.user',
		'plugin.Comments.article');

/**
 * Model
 *
 * @var object
 */
	public $Model = null;

/**
 * Start test
 *
 * @return void
 */
	public function startTest($method) {
		$this->Model = Classregistry::init('Article');
		$this->Model->Comment->bindModel(array(
			'belongsTo' => array(
				'User')));
	}

/**
 * End test
 *
 * @return void
 */
	public function endTest($method) {
		unset($this->Model);
		ClassRegistry::flush();
	}

/**
 * Test behavior instance
 *
 * @return void
 */
	public function testBehaviorInstance() {
		$this->assertTrue(is_a($this->Model->Behaviors->Commentable, 'CommentableBehavior'));
	}

/**
 * testCommentAdd
 *
 * @return void
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
			$this->assertFalse(true); 
			// @todo refactor $this->fail();
		} catch (BlackHoleException $e) {
			$this->assertTrue(true); 
			// @todo refactor $this->pass();
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
 */
	public function testCommentToggleApprove() {
		$comment = $this->Model->Comment->find('first', array('conditions' => array('Comment.id' => '1')));
		$this->assertEqual($comment['Comment']['approved'], true);
		$this->assertTrue($this->Model->commentToggleApprove($comment['Comment']['id']));
		$comment = $this->Model->Comment->find('first', array('conditions' => array('Comment.id' => '1')));
		$this->assertEqual($comment['Comment']['approved'], false);
		$this->assertTrue($this->Model->commentToggleApprove($comment['Comment']['id']));
		$comment = $this->Model->Comment->find('first', array('conditions' => array('Comment.id' => '1')));
		$this->assertEqual($comment['Comment']['approved'], true);

		$this->assertFalse($this->Model->commentToggleApprove(21415));
	}

/**
 * commentDelete
 *
 * @return void
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
 */
	public function testChangeCommentCount() {
		$this->assertTrue($this->Model->changeCommentCount('1', 'up'));
		$article = $this->Model->findById(1);
		$this->assertEqual($article['Article']['comments'], 3);
		$this->assertTrue($this->Model->changeCommentCount('1', 'down'));
		$article = $this->Model->findById(1);
		$this->assertEqual($article['Article']['comments'], 2);

		$this->assertFalse($this->Model->changeCommentCount('1', 'invalid'));
        //Invalid comment returns true because of update all statement
		$this->assertTrue($this->Model->changeCommentCount('invalid!', 'up'));
	}

/**
 * testCommentBeforeFind
 *
 * @return void
 */
	public function testCommentBeforeFind() {
		$options = array('userModel' => 'User');
		$result = $this->Model->commentBeforeFind($options);
		$expected = array(
			'conditions' => array(
				'Comment.approved' => 1,
				'Comment.is_spam' => array('clean', 'ham')));
		$this->assertEqual($result, $expected);

		$options = array_merge($options, array(
			'isAdmin' => true,
			'id' => 1));
		$this->Model->Comment->Behaviors->attach('Containable');
		$this->Model->Behaviors->attach('Containable');
		$result = $this->Model->commentBeforeFind($options);
		$expected = array(
			'conditions' => array(
				'Comment.model' => 'Article',
				'Article.id' => 1,
			'Comment.is_spam' => array('clean', 'ham')));
		$this->assertEqual($result, $expected);
		$this->assertTrue($this->Model->Behaviors->enabled('Containable'));
		$this->assertTrue($this->Model->Comment->Behaviors->enabled('Containable'));
	}

/**
 * testCommentBeforeFindIdNotDefault
 *
 * @return void
 */
	public function testCommentBeforeFindIdNotDefault() {
		$this->Model = Classregistry::init('ArticleIdNotDefault');
		$this->Model->Comment->bindModel(array(
			'belongsTo' => array(
				'User')));
		$options = array('userModel' => 'User');
		$result = $this->Model->commentBeforeFind($options);
		$this->assertEquals('articleidnotdefault_id', $this->Model->Comment->belongsTo['ArticleIdNotDefault']['fields'][0]);
	}
	
/**
 * testBeforeAndAfterCallbacks
 *
 * @return void
 */
	public function testBeforeAndAfterCallbacks() {
		$listener = new CommentEventListener();
		CakeEventManager::instance()->attach($listener);

		$this->Model = Classregistry::init('Article');
		$options = array(
			'userId' => '47ea303a-3b2c-4251-b313-4816c0a800fa',
			'modelId' => '1',
			'modelName' => 'Article',
			'defaultTitle' => 'Specified default title',
			'data' => array(
				'Comment' => array(
					'body' => "Comment Test successful Captn!",
					'title' => 'Not the Default title')),
			'permalink' => 'http://testing.something.com');
		$this->Model->commentAdd(0, $options);

		$commentId = $this->Model->Comment->id;
		$comment = $this->Model->Comment->read(null, $commentId);
		$this->assertEqual($comment['Comment']['title'], 'Changed in beforeComment!');
		$this->assertEqual($comment['Comment']['body'], 'Changed in afterComment!');
	}

}

