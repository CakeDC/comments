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

/**
 * Article Fixture
 *
 * @package comments
 * @subpackage comments.tests.fixtures
 */
class ArticleFixture extends CakeTestFixture {

/**
 * name property
 *
 * @var string
 */
	public $name = 'Article';

/**
 * fields property
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'), 		
		'title' => array('type' => 'string', 'null' => false),
		'comments' => array('type' => 'integer', 'null' => false, 'default' => '0'));

/**
 * records property
 *
 * @var array
 */
	public $records = array(
		array('id' => '1', 'title' => 'First Article', 'comments' => 2),
		array('id' => '2', 'title' => 'Second Article', 'comments' => 0));
}
