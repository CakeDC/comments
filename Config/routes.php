<?php
$admin = Configure::read('Routing.prefixes.0');
if (isset($admin)) {
	Router::connect('/' . $admin . '/comments', array('plugin' => 'comments', 'controller' => 'comments', 'admin' => true, 'prefix' => $admin));
	Router::connect('/' . $admin . '/comments/index/*', array('plugin' => 'comments', 'controller' => 'comments', 'admin' => true, 'prefix' => $admin));
	Router::connect('/' . $admin . '/comments/:action', array('plugin' => 'comments', 'controller' => 'comments', 'admin' => true, 'prefix' => $admin));
	Router::connect('/' . $admin . '/comments/comments/:action', array('plugin' => 'comments', 'controller' => 'comments', 'admin' => true, 'prefix' => $admin));
	Router::connect('/' . $admin . '/comments/:action/*', array('plugin' => 'comments', 'controller' => 'comments', 'admin' => true, 'prefix' => $admin));
	Router::connect('/' . $admin . '/comments/comments/:action/*', array('plugin' => 'comments', 'controller' => 'comments', 'admin' => true, 'prefix' => $admin));
}