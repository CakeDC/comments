<?php
class ArticleFixture extends CakeTestFixture {

/**
 * name property
 *
 * @var string 'AnotherArticle'
 * @access public
 */
	public $name = 'Article';

/**
 * fields property
 *
 * @var array
 * @access public
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'title' => array('type' => 'string', 'null' => false),
		'comments' => array('type' => 'integer', 'null' => false, 'default' => '0'));

/**
 * records property
 *
 * @var array
 * @access public
 */
	public $records = array(
		array('title' => 'First Article', 'comments' => 2),
		array('title' => 'Second Article', 'comments' => 0));

}
?>