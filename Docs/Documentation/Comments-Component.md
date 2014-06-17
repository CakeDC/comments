Comments Component
==================

During page rendering the comments component checks if some of the passed named url parameters are filled. If it is filled we perform operations like add/delete comment. The component works in background of code performed during controller action and needs just one find from controller.

Sometimes you want to know how much comments your user did. In this case all you need to do - add additional field with name "comments" into the table that keep all users information in you systems. It can be any table like users or profiles.

Component conventions
---------------------

The component needs to have one important convention for any actions where it is enabled:

To work properly, the component needs a specific variable to be set in every action using it. Its name should be either ```Inflector::variable(Controller::$modelClass)``` or ```Comments::$viewVariable``` should be set to other name of this view variable. That variable should contain single model record. for example you need to have next line in you view action:

```php
$this->set('post', $this->Post->read(null, $id));
```

If you plan to attach comments plugin to model that stored in some plugin its highly recommended to define ```Model::$fullName``` property for your model in ClassRegistry format. For example for Post model of Blogs plugin set ```$fullName = 'Blogs.Post'```.

It is also possible to define a ```permalink()``` function in your Post model. This method should return the correct url to the view page where comments displayed. This is required by most anti-spam systems if you plan to use it.

Component Callbacks
-------------------

It is possible to override or extend the most comments component methods in the controller. To do this we need to create a method with prefix callback_comments.

Examples:

* ```callback_add``` has to be named as ```callback_commentsAdd``` in the controller.
* ```callback_fetchData``` has to be named as ```callback_commentsFetchData``` the in controller.

Callbacks:

* **initType:** Method that set comment template system type based on vars.
* **view:** Handles controllers actions like list and add related comments.
* **fetchDataTree:** Paginateable tree representation of the comment data.
* **fetchDataFlat:** Flat representation of the comment data.
* **fetchDataThreaded:** Threaded comment data, one-paginateable, the whole data is fetched.
* **fetchData:** Default method, calls callback_fetchData.
* **prepareParams:** Prepares passed parameters.
* **add:** Add new comment action. Sometimes useful to override, to add some additional preprocessing. See example bellow.
* **getFormattedComment:** Fetch and format a comment message.
* **toggleApprove:** Handles approval of comments.
* **delete:** Delete action. Can be overloaded.
* **afterAdd:** Callback called after success adding new comment.

Extend or override any callback without changing component code.

```php
public function callback_commentsAdd($modelId, $commentId, $displayType, $data = array()) {
	if (!empty($this->request->data)) {
		/* ... */
		// Custom model method to manipulate or validate the comment
		if (!$this->Model->validateComment($this->request->data)) {
			$this->Session->setFlash(__('Please enter necessery information', true));
			return;
		}
		/* ... */
	}
	return $this->Comments->callback_add($modelId, $commentId, $displayType, $data);
}
```

Component Parameters
--------------------

The plugin uses several named parameters that are passed during comment operations like create, delete, reply or approve is performed.

* **comment\_view\_type:** Parameter that allow to specify what type of comments system used. Currently allowed to use one of ```flat```, ```threaded```, ```tree```. This parameter possible and useful to setup in beforeFilter to use only one type of view. If the user is allowed to choose between tree and flat, then the parameter can be dynamic.
* **comment_action:** This parameter used, to pass what action should performed.
* **comment:** Comment id passed here.
* **quote:** Boolean flag that show if you should use quote when generate reply to comment form.

Please note that the parameters listed here should not be used as named parameters in your app!

Component settings
------------------

All components parameters should be overwritten in beforeFilter method.

 * **actionNames:** Name of actions comments component should use. By default it ```view``` and ```comments```. So if you want to have comments on ```display``` action you need to set it in beforeFilter method.
 * **modelName:** Name of the commentable model. By default it is default controller's model name (```Controller::$modelClass```)
 * **assocName:** Name of the association for comments.
 * **userModel:** Name of the user model associated to comment. By default it is ```UserModel```.
 * **userModelClass:** Class name for the user model. By default it is ```User```. If you use another model for identity purpose you need to setup it here.
 * **unbindAssoc:** Enabled if this component should permanently unbind the association to the Comment model in order to not query the model for unnecessary data in the ```Controller::view()``` action.
 * **commentParams:** Parameters passed to view.
 * **viewVariable:** Name of view variable which contains model data for ```view()``` action. Needed just for PK value available in it. By default ```Inflector::variable(Comments::modelName)```.
 * **viewComments:** Name of view variable for comments data. By default ```commentsData```.
 * **allowAnonymousComment:** Flag to allow anonymous user make comments. By default it is ```false```.

There are two way to change settings values for component. You can change it in beforeFilter callback before the component is initialized, or pass parameters during initialization:


```php
public $components = array(
	'Comments.Comments' => array(
		'userModelClass' => 'Users.User'
	)
);
```
