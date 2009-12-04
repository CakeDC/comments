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
	}

/**
 * 
 *
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
		$results = $this->Comment->recursive = -1;
		$results = $this->Comment->find('first');
		$this->assertTrue(!empty($results));
		$expected = array('Comment' => array(
			'id'  => '493d5b4d-c008-4a3b-9581-403a4a35e6b2',
			'user_id'  => null,
			'model'  => 'Entry',
			'foreign_key'  => 'a12cc22a-d022-11dd-8f06-00e018bfb339', //blogs.test entry
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

}
?>