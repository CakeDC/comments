<?php
App::import('Core', 'ModelBehavior');
Mock::generatePartial('ModelBehavior', 'AntispamableBehavior', array('isSpam', 'setSpam', 'setHam'));

/**
 * 
 */
class CommentTestCase extends CakeTestCase {

/**
 * 
 */
	public $Comment = null;

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
 * 
 */
	public function startTest() {
		$this->Comment = ClassRegistry::init('Comments.Comment');
		$this->Comment->bindModel(array(
			'belongsTo' => array(
				'Article' => array(
					'foreignKey' => 'foreign_key'))));
	}

/**
 * @return void
 * @access public
 */
	public function endTest() {
		unset($this->Comment);
		ClassRegistry::flush(); 
	}

/**
 * 
 */
	public function testCommentInstance() {
		$this->assertTrue(is_a($this->Comment, 'Comment'));
	}

/**
 * 
 */
	public function testCommentFind() {
		$this->Comment->recursive = -1;
		$results = $this->Comment->find('first');
		$this->assertTrue(!empty($results));
		$expected = array('Comment' => array(
			'id'  => '1',
			'user_id'  => null,
			'model'  => 'Article',
			'foreign_key'  => '1',
			'parent_id'  => '0',
			'approved'  => 1,
			'name'  => null,
			'title'  => '-',
			'slug'  => '_',
			'body'  => 'This is a comment',
			'lft'  => 1,
			'rght'  => 2,
			'modified'  => '2008-12-22 16:39:19',
			'created'  => '2008-12-22 16:39:19',
			'author_name' => 'mark story',
			'author_email' => 'example@example.com',
			'author_url' => 'http://example.com',
			'is_spam' => 'clean',
			'comment_type' => 'comment',
		));
		$this->assertEqual($results, $expected);
	}

/**
 * testBeforeSave
 *
 * @return void
 * @access public
 */
	public function testBeforeSave() {
		Configure::write('Config.language', 'eng');
		$this->assertTrue($this->Comment->beforeSave());
		$this->assertEqual($this->Comment->data['Comment']['language'], 'eng');
	}

/**
 * testAfterSave
 *
 * @return void
 * @access public
 */
	public function testAfterSave() {
		$this->Comment->Behaviors->attach('Antispamable');
		$Antispamable = $this->Comment->Behaviors->Antispamable;
		$isSpamCallCount = 0;
		
		$this->Comment->id = 1;
		$Antispamable->setReturnValueAt($isSpamCallCount++, 'isSpam', true);
		$this->Comment->afterSave(true);
		$this->assertEqual($this->Comment->field('is_spam'), 'spam');
		$this->Comment->Article->id = 1;
		$this->assertEqual($this->Comment->Article->field('comments'), '1');
		
		$Antispamable->setReturnValueAt($isSpamCallCount++, 'isSpam', false);
		$this->Comment->afterSave(true);
		$this->assertEqual($this->Comment->field('is_spam'), 'clean');
		$this->assertEqual($this->Comment->Article->field('comments'), '1');
		
		$Antispamable->expectCallCount('isSpam', $isSpamCallCount);
	}

/**
 * testChangeCount
 *
 * @return void
 * @access public
 */
	public function testChangeCount() {
		$before = $this->Comment->Article->findById(1);
		
		$this->assertTrue($this->Comment->changeCount(1, 'up'));
		$after = $this->Comment->Article->findById(1);
		$this->assertEqual($after['Article']['comments'], $before['Article']['comments'] + 1);
		$this->assertFalse($this->Comment->changeCount(0, 'up'));
		
		$this->assertTrue($this->Comment->changeCount(1, 'down'));
		$after = $this->Comment->Article->findById(1);
		$this->assertEqual($after['Article']['comments'], $before['Article']['comments']);
	}
	
/**
 * Test markAsSpam method
 * 
 * @return void
 */
	public function testMarkAsSpam() {
		$this->Comment->Behaviors->attach('Antispamable');
		$Antispamable = $this->Comment->Behaviors->Antispamable;
		
		$this->assertFalse($this->Comment->markAsSpam('invalid'));
		
		$before = $this->Comment->Article->findById(1);
		$this->Comment->id = 1;
		$this->assertTrue($this->Comment->markAsSpam());
		$after = $this->Comment->Article->findById(1);
		$this->assertEqual($after['Article']['comments'], $before['Article']['comments'] - 1);
		$this->assertEqual($this->Comment->field('is_spam'), 'spammanual');
		$Antispamable->expectOnce('setSpam');
	}
	
/**
 * Test markAsSpam method
 * 
 * @return void
 */
	public function testMarkAsHam() {
		$this->Comment->Behaviors->attach('Antispamable');
		$Antispamable = $this->Comment->Behaviors->Antispamable;
		
		$this->assertFalse($this->Comment->markAsHam('invalid'));
		
		$before = $this->Comment->Article->findById(2);
		$this->Comment->id = 3;
		$this->assertTrue($this->Comment->markAsHam());
		$after = $this->Comment->Article->findById(2);
		$this->assertEqual($after['Article']['comments'], $before['Article']['comments'] + 1);
		$this->assertEqual($this->Comment->field('is_spam'), 'ham');
		$Antispamable->expectOnce('setHam');
	}
	
/**
 * Test delete method
 * 
 * @return void
 */
	public function testDelete() {
		$this->assertFalse($this->Comment->delete('invalid'));
		
		$before = $this->Comment->Article->findById(1);
		$this->Comment->id = 1;
		$this->assertTrue($this->Comment->delete());
		$after = $this->Comment->Article->findById(1);
		$this->assertEqual($after['Article']['comments'], $before['Article']['comments'] - 1);
		$this->assertFalse($this->Comment->exists(true));
	}

	public function testProcessDelete() {
		$data['Comment'] = array(
			'1' => 1,
			'2' => 0,
			'3' => 0);
		$this->Comment->process('delete', $data);
		$comment1 = $this->Comment->findById(1);
		$this->assertFalse($comment1);
		$comment2 = $this->Comment->findById(2);
		$this->assertIsA($comment2, 'Array');
	}

	public function testProcessHam() {
		$data['Comment'] = array(
			'1' => 1,
			'2' => 0);
		$this->Comment->process('ham', $data);
		$comment1 = $this->Comment->findById(1);
		$this->assertEqual($comment1['Comment']['is_spam'], 'ham');
	}
	
	public function testProcessSpam() {
		$data['Comment'] = array(
			'1' => 1,
			'2' => 0);
		$this->Comment->process('spam', $data);
		$comment1 = $this->Comment->findById(1);
		$this->assertEqual($comment1['Comment']['is_spam'], 'spammanual');
	}
	
	public function testProcessApprove() {
		$data['Comment'] = array(
			'2' => 0,
			'3' => 1);
		$this->Comment->process('approve', $data);
		$comment = $this->Comment->findById(3);
		$this->assertEqual($comment['Comment']['approved'], 1);
	}
	
	public function testProcessDisapprove() {
		$data['Comment'] = array(
			'1' => 1,
			'2' => 0);
		$this->Comment->process('disapprove', $data);
		$comment = $this->Comment->findById(1);
		$this->assertEqual($comment['Comment']['approved'], 0);
	}

}
?>