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
 * Comment Fixture
 *
 * @package comments
 * @subpackage comments.tests.fixtures
 */
class CommentFixture extends CakeTestFixture {

/**
 * Model name
 *
 * @var string $model
 */
	public $name = 'Comment';

/**
 * Table name
 *
 * @var string $useTable
 */
	public $table = 'comments';

/**
 * Fields definition
 *
 * @var array $fields
 */
	public $fields = array(
		'id' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
		'user_id' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 36),
		'model' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 30),
		'foreign_key' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 36),
		'parent_id' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 36),
		'approved' => array('type'=>'boolean', 'null' => false, 'default' => '1'),
		'name' => array('type'=>'string', 'null' => true, 'default' => NULL),
		'title' => array('type'=>'string', 'null' => false, 'default' => NULL),
		'slug' => array('type'=>'string', 'null' => false, 'default' => NULL, 'key' => 'index'),
		'body' => array('type'=>'text', 'null' => true, 'default' => NULL),
		'lft' => array('type'=>'integer', 'null' => true, 'default' => NULL),
		'rght' => array('type'=>'integer', 'null' => true, 'default' => NULL),
		'modified' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
		'created' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
		'author_name' => array('type'=>'string', 'null' => true, 'default' => NULL),
		'author_email' => array('type'=>'string', 'null' => true, 'default' => NULL),
		'author_url' => array('type'=>'string', 'null' => true, 'default' => NULL),
		'is_spam' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 20),
		'comment_type' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 32),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'COMMENT_SLUG' => array('column' => 'slug', 'unique' => 0)
		)
	);

/**
 * record set
 *
 * @var array $records
 */
	public $records = array(
		array(
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
			'comment_type' => 'comment'),
		array(
			'id'  => '2',
			'user_id'  => null,
			'model'  => 'Article',
			'foreign_key'  => '1',
			'parent_id'  => '0',
			'approved'  => 1,
			'name'  => null,
			'title'  => '-',
			'slug'  => '_',
			'body'  => 'This is a comment',
			'lft'  => 3,
			'rght'  => 4,
			'modified'  => '2008-12-22 16:39:19',
			'created'  => '2008-12-22 16:39:19',
			'author_name' => 'mark story',
			'author_email' => 'example@example.com',
			'author_url' => 'http://example.com',
			'is_spam' => 'clean',
			'comment_type' => 'comment',
		),
		array(
			'id'  => '3',
			'user_id'  => '47ea303a-3b2c-4251-b313-4816c0a800fa', // phpnut
			'model'  => 'Article',
			'foreign_key'  => '2',
			'parent_id'  => '0',
			'approved'  => 0,
			'name'  => null,
			'title'  => '-',
			'slug'  => '_',
			'body'  => 'This is a spam',
			'lft'  => 3,
			'rght'  => 4,
			'modified'  => '2008-12-22 16:39:19',
			'created'  => '2008-12-22 16:39:19',
			'author_name' => 'Larry Masters',
			'author_email' => 'example@example.com',
			'author_url' => 'http://example.com',
			'is_spam' => 'spam',
			'comment_type' => 'comment',
		),
		array(
			'id'  => '4',
			'user_id'  => '47ea303a-3b2c-4251-b313-4816c0a800fa', // phpnut
			'model'  => 'Article',
			'foreign_key'  => '2',
			'parent_id'  => '0',
			'approved'  => 0,
			'name'  => null,
			'title'  => '-',
			'slug'  => '_',
			'body'  => 'This is a clean comment',
			'lft'  => 3,
			'rght'  => 4,
			'modified'  => '2008-12-22 16:39:19',
			'created'  => '2008-12-22 16:39:19',
			'author_name' => 'Larry Masters',
			'author_email' => 'example@example.com',
			'author_url' => 'http://example.com',
			'is_spam' => 'clean',
			'comment_type' => 'comment',
		),

	);
}

