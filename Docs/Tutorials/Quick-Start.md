Quick Start
===========

We have a Post model and want to have comments on the /posts/view page. So we need to choose how we want to display the comments - flat, threaded, or as tree.  In our example we will use the flat type comments. First let us add the following code in the PostsController:

```php
class PostsController extends AppControlle {

/**
 * Components
 *
 * @var array
 */
	public $components = array(
		'Comments.Comments' => array(
			'userModelClass' => 'Users.User' // Customize the User class
		)
	);
}
```

By default the component assumes that the action that will be used for comments is named 'view', but we can override it inside the beforeFilter method.

Inside the view (in this case it will be View/Posts/view.ctp) we will add the next lines at the end of the view file.

```php
<div id="post-comments">
	<?php $this->CommentWidget->options(array('allowAnonymousComment' => false));?>
	<?php echo $this->CommentWidget->display();?>
</div>
```

You should now be able to comment on that page.
