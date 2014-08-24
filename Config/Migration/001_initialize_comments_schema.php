<?php
/**
 * CakePHP Comments
 *
 * Copyright 2009 - 2014, Cake Development Corporation
 *                        1785 E. Sahara Avenue, Suite 490-423
 *                        Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @Copyright 2009 - 2014, Cake Development Corporation
 * @link      http://github.com/CakeDC/Comments
 * @package   plugins.comments
 * @license   MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Short description for class.
 *
 * @package		plugins.comments
 * @subpackage	pligins.comments.config.migrations
 */

class M4a9bc72d1ac4476fbccb00e4beba7b47 extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = '';

/**
 * Migration array
 * 
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'create_table' => array(
				'comments' => array(
					'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary'),
					'parent_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36),
					'foreign_key' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36),
					'user_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36),
					'lft' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 10),
					'rght' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 10),
					'model' => array('type' => 'string', 'null' => false, 'default' => null),
					'approved' => array('type' => 'boolean', 'null' => false, 'default' => '1'),
					'title' => array('type' => 'string', 'null' => true, 'default' => null),
					'slug' => array('type' => 'string', 'null' => true, 'default' => null),
					'body' => array('type' => 'text', 'null' => true, 'default' => null),
					'author_name' => array('type' => 'string', 'null' => true, 'default' => null),
					'author_url' => array('type' => 'string', 'null' => true, 'default' => null),
					'author_email' => array('type' => 'string', 'length' => 128, 'default' => '', 'null' => false),
					'language' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 6),
					'is_spam' => array('type' => 'string', 'length' => 20, 'default' => 'clean', 'null' => false), // possible values: clean, spam, ham, spammanual
					'comment_type' => array('type' => 'string', 'length' => 32, 'default' => 'comment', 'null' => false), // possible values: comment, trackback, pingback
					'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
				),
			),
		),
		'down' => array(
			'drop_table' => array('comments'),
		)
	);

/**
 * before migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean
 */
	public function before($direction) {
		return true;
	}

/**
 * after migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @return boolean
 */
	public function after($direction) {
		return true;
	}

}
