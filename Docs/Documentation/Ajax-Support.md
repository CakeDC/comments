Ajax support
------------

The plugin was tested with jquery engine for the CakePHP JsHelper. Since CakePHP JsHelper support many engines, theoretically you can choose any of supported js engines and comments plugin should support it.

To turn on ajax mode you need set pass two parameters to the helper:

```php
$this->CommentWidget->options(array(
	'target' => '#comments',
	'ajaxAction' => 'comments'
));
```

The next step is important: You will have to implement a special action in the controller, by default named ```comments```:

```php
public function comments($id = null) {
	$post = $this->Post->read(null, $id);
	$this->layout = 'ajax';
	$this->set(compact('post', 'id'));
}
```

It is also necessary to implement a comments view, that will just contain the previous block and will include the ajax element from comments plugin:

```php
$this->CommentWidget->options(array(
	'target' => '#comments',
	'ajaxAction' => 'comments'
));
echo $this->element('/ajax');
```

The comments action in the controller should be the same as the view action, the difference is only in the view.

If you should pass some more params into ```CommentWidget::display()``` method in ajax element you can call it with the additional ```displayOptions``` parameter:

```php
$this->CommentWidget->options(array(
	'target' => '#comments',
	'ajaxAction' => 'comments'
));
echo $this->element('/ajax', array(
	'displayOptions' => array(
		/* ... params ...  */
	)
));
```