<?php
class AllCommentsPluginTest extends PHPUnit_Framework_TestSuite {

/**
 * Suite define the tests for this suite
 *
 * @return void
 */
	public static function suite() {
		$suite = new PHPUnit_Framework_TestSuite('All Comments Plugin Tests');

		$basePath = CakePlugin::path('Comments') . DS . 'Test' . DS . 'Case' . DS;
		// controllers
		$suite->addTestFile($basePath . 'Controller' . DS . 'CommentsControllerTest.php');
		// controllers
		$suite->addTestFile($basePath . 'Controller' . DS . 'Component' . DS . 'CommentsComponentTest.php');
		// behaviors
		$suite->addTestFile($basePath . 'Model' . DS . 'Behavior' . DS . 'CommentableBehaviorTest.php');
		// models
		$suite->addTestFile($basePath . 'Model' . DS . 'CommentTest.php');
		// helpers
		$suite->addTestFile($basePath . 'View' . DS . 'Helper' . DS . 'CommentWidgetHelperTest.php');
		return $suite;
	}

}