<?php
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
	}

}
?>