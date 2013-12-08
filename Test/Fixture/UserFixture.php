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

App::uses('Security', 'Utility');
 
/**
 * User Fixture
 *
 * @package comments
 * @subpackage comments.tests.fixtures
 */
class UserFixture extends CakeTestFixture {

/**
 * Name
 *
 * @var string $name
 */
	public $name = 'User';

/**
 * Table
 *
 * @var array $table
 */
	public $table = 'users';

/**
 * Fields
 *
 * @var array $fields
 */
	public $fields = array(
			'id' => array('type'=>'string', 'null' => false, 'length' => 36, 'key' => 'primary'),
			'account_type' => array('type' => 'string', 'null' => false, 'length' => 8),
			'url' => array('type'=>'string', 'null' => false, 'key' => 'unique'),
			'slug' => array('type'=>'string', 'null' => false),
			'username' => array('type'=>'string', 'null' => false),
			'email' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 255),
			'email_authenticated' => array('type'=>'boolean', 'null' => false, 'default' => '0'),
			'email_token' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 255),
			'email_token_expires' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
			'passwd' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 128),
			'password_token' => array('type'=>'string', 'null' => true, 'default' => NULL, 'length' => 128),
			'tos' => array('type'=>'boolean', 'null' => false, 'default' => '0'),
			'active' => array('type'=>'boolean', 'null' => false, 'default' => '0'),
			'public_master_key' => array('type'=>'text', 'null' => true, 'default' => NULL),
			'public_session_key' => array('type'=>'text', 'null' => true, 'default' => NULL),
			'private_session_key' => array('type'=>'text', 'null' => true, 'default' => NULL),
			'last_activity' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
			'is_admin' => array('type'=>'boolean', 'null' => true, 'default' => '0'),
			'created' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
			'modified' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
			'indexes' => array(
				'PRIMARY' => array('column' => 'id', 'unique' => 1),
				'UNIQUE_URL' => array('column' => 'url', 'unique' => 1))
			);

/**
 * Records
 *
 * @var array $records
 */
	public $records = array(
		array(
			'id'  => '47ea303a-3b2c-4251-b313-4816c0a800fa',
			'account_type'  => 'local',
			'url'  => '/user/phpnut',
			'slug' => 'phpnut',
			'username'  => 'phpnut',
			'email' => 'larry.masters@cakedc.com',
			'email_authenticated' => 1,
			'email_token' => 'testtoken',
			'email_token_expires' => '2008-03-25 02:45:46',
			'passwd'  => 'test', // test
			'password_token'  => 'testtoken',
			'tos' => 1,
			'active' => 1,
			'public_master_key'  => '',
			'public_session_key'  => '',
			'private_session_key'  => '',
			'last_activity'  => '2008-03-25 02:45:46',
			'is_admin' => 1,
			'created'  => '2008-03-25 02:45:46',
			'modified'  => '2008-03-25 02:45:46'
		),
		array(
			'id'  => '47ea303a-3cyc-k251-b313-4811c0a800bf',
			'account_type'  => 'remote',
			'url'  => '/user/floriank',
			'slug' => 'floriank',
			'username'  => 'floriank',
			'email' => 'florian.kraemer@cakedc.com',
			'email_authenticated' => '1',
			'email_token' => '',
			'email_token_expires' => '2008-03-25 02:45:46',
			'passwd'  => 'secretkey', // secretkey
			'password_token'  => '',
			'tos' => 1,
			'active' => 1,
			'public_master_key'  => '',
			'public_session_key'  => '',
			'private_session_key'  => '',
			'last_activity'  => '2008-03-25 02:45:46',
			'is_admin' => 0,
			'created'  => '2008-03-25 02:45:46',
			'modified'  => '2008-03-25 02:45:46'
		),
		array(
			'id'  => '37ea303a-3bdc-4251-b315-1316c0b300fa',
			'account_type'  => 'remote',
			'url'  => '/user/user1',
			'slug' => 'user1',
			'username'  => 'user1',
			'email' => 'testuser1@testuser.com',
			'email_authenticated' => 0,
			'email_token' => 'testtoken2',
			'email_token_expires' => '2008-03-28 02:45:46',
			'passwd'  => 'newpass', // newpass
			'password_token'  => '',
			'tos' => 0,
			'active' => 0,
			'public_master_key'  => '',
			'public_session_key'  => '',
			'private_session_key'  => '',
			'last_activity'  => '2008-03-25 02:45:46',
			'is_admin' => 0,
			'created'  => '2008-03-25 02:45:46',
			'modified'  => '2008-03-25 02:45:46'
		),
		array(
			'id' => '495e36a2-1f00-46b9-8247-58a367265f11',
			'account_type' => 'local',
			'url'  => '/user/oidtest',
			'slug' => 'oistest',
			'username'  => 'oidtest',
			'email' => 'oidtest@testuser.com',
			'email_authenticated' => 0,
			'email_token' => 'testtoken2',
			'email_token_expires' => '2008-03-28 02:45:46',
			'passwd'  => 'newpass', // newpass
			'password_token'  => '',
			'tos' => 0,
			'active' => 0,
			'public_master_key'  => '',
			'public_session_key'  => '',
			'private_session_key'  => '',
			'last_activity'  => '2008-03-25 02:45:46',
			'is_admin' => 0,
			'created'  => '2008-03-25 02:45:46',
			'modified'  => '2008-03-25 02:45:46'
		)
	);

/**
 * Constructor
 *
 */
	public function __construct() {
		parent::__construct();
		foreach ($this->records as &$record) {
			$record['passwd'] = Security::hash($record['passwd'], null, true);
		}
	}
}
