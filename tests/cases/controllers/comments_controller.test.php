<?php
App::import('Controller', 'Comments.Comments');

class TestComments extends CommentsController {
	var $autoRender = false;
}

class CommentsControllerTest extends CakeTestCase {
	var $Comments = null;

	function setUp() {
		$this->Comments = new TestComments();
	}

	function testCommentsControllerInstance() {
		$this->assertTrue(is_a($this->Comments, 'CommentsController'));
	}

	function tearDown() {
		unset($this->Comments);
	}
}
?>