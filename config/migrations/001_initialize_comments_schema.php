<?php
class M4a9bc72d1ac4476fbccb00e4beba7b47 extends CakeMigration {
/**
 * Migration description
 *
 * @var string
 * @access public
 */
	public $description = '';

/**
 * Dependency array. Define what minimum version required for other part of db schema
 *
 * Migration defined like 'app.m49ad0b91bd4c4bd482cc1de43461d00a' or 'plugin.PluginName.m49ad0d8518904f518db21bb43461d00a'
 * 
 * @var array $dependendOf
 * @access public
 */
	public $dependendOf = array();

/**
 * Shell object
 *
 * @var MigrationInterface
 * @access public
 */
	public $Shell;

/**
 * Migration array
 * 
 * @var array $migration
 * @access public
 */ 
	public $migration = array(
		'up' => array(
			'create_table' => array(
				'comments' => array(
					'id' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
					'parent_id' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 36),
					'foreign_key' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 36),
					'user_id' => array('type'=>'string', 'null' => false, 'default' => NULL, 'length' => 36),
					'lft' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'length' => 10),
					'rght' => array('type'=>'integer', 'null' => false, 'default' => NULL, 'length' => 10),
					'model' => array('type'=>'string', 'null' => false, 'default' => NULL),
					'approved' => array('type'=>'boolean', 'null' => false, 'default' => '1'),
					'is_spam' => array('type'=>'boolean', 'null' => false, 'default' => '0'),
					'title' => array('type'=>'string', 'null' => true, 'default' => NULL),
					'slug' => array('type'=>'string', 'null' => true, 'default' => NULL),
					'body' => array('type'=>'text', 'null' => true, 'default' => NULL),
					'author_name' => array('type'=>'string', 'null' => true, 'default' => NULL),
					'author_url' => array('type'=>'string', 'null' => true, 'default' => NULL),
					'author_email' => array('type' => 'string', 'length' => 128, 'default' => '', 'null' => false),
					'language' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 6),
					'is_spam' => array('type' => 'string', 'length' => 20, 'default' => 'clean', 'null' => false), // possible values: clean, spam, ham, spammanual
					'comment_type' => array('type' => 'string', 'length' => 32, 'default' => 'comment', 'null' => false), // possible values: comment, trackback, pingback
					'created' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
					'modified' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
					'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1))
				),
			),
			'create_field' => array(
				'users' => array(
					'comment_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10))),
		),
		'down' => array(
			'drop_table' => array('comments'),
			'drop_field' => array(
				'users' => array('comment_count')),
		)
	);

/**
 * before migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @access public
 */
	public function before($direction) {
		return true;
	}

/**
 * after migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @access public
 */
	public function after($direction) {
		return true;
	}

}
?>