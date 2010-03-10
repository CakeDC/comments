Support http://github.com/CakeDC/Comments/issues

Copyright 2009 - 2010, Cake Development Corporation
                        1785 E. Sahara Avenue, Suite 490-423
                        Las Vegas, Nevada 89104
                        http://cakedc.com

The comments plugin will allow you to make any kind of data comment-able.

## Comments plugin

Comments plugin is a universal comment system that can be attached to any controller with a few lines of code.

### Sample of usage

We have Post model and want to have comments on the /posts/view page.
So we need to choose how we want to display the comments - flat, or as tree.
In our example we want to use the flat type comments.

First let us add the following code in the PostsController:

	public $components = array('Comments.Comments' => array('userModelClass' => 'Users.User'));

	public function beforeFiler() {
		parent::beforeFiler();
		$this->passedArgs['comment_view_type'] = 'flat';
	}


By default the component assumes that the action that will be used for comments is named 'view', but we can override it inside the beforeFilter method.

Inside the view (in our case it will views/posts/view.ctp) we will add the next lines at the end of the view file.

	<div id="post-comments">
		<?php $commentWidget->options(array('allowAnonymousComment' => false));?>
		<?php echo $commentWidget->display();?>
	</div>


### How it works 

During page rendering the comments component checks if some of the passed named url parameters are filled. 
If it is filled we perform operations like add/delete comment. The component works in background of code performed during controller action and needs just one find from controller.

Sometimes you want to know how much comments your user did. In this case all you need to do - add additional field with name "comments" into the table that keep all users information in you systems. It can be any table like users or profiles.

### Component conventions

The component needs to have one important convention for any actions where it is enabled:

To work properly, the component needs a specific variable to be set in every action using it. Its name should be either Inflector::variable(Controller::$modelClass) or Comments::$viewVariable should be set to other name of this view variable. That variable should contain single model record. for example you need to have next line in you view action:
	
		$this->set('post', $this->Post->read(null, $id));

If you plan to attach comments plugin to model that stored in some plugin highly recommended to define Model::$fullName property for your model in ClassRegistry format.
For example for Post model of Blogs plugin set $fullName = 'Blogs.Post'.
Also possible you will need to define permalink function in your Post model. This method should return correct url to view page where comments displayed.
This required by most antispam systems if you plan to use it.

### Component callbacks

It is possible to override or extend the most comments component methods in the controller.
To do this we need to create method with prefix callback_comments
Examples: 

 * callback_add will named as callback_commentsAdd in controller,
 * callback_fetchData will named as callback_commentsFetchData in controller.

Callbacks:

 * add - add new comment action. Sometimes useful to override, to add some additional preprocessing. See example bellow.
 * initType - method that set comment template system type based on vars.
 * delete - delete action. Can be overloaded.
 * afterAdd - callback called after success adding new comment.

  Possible to extend or override any callback without changing component code.
  
  
	public function callback_commentsAdd($modelId, $commentId, $displayType, $data = array()) {
		if (!empty($this->data)) {
			...
			///perform some validation and field manipulations here. all value need to store into the $data.
			$data['Comment']['author_name'] = $this->Auth->user('username');
			$data['Comment']['author_email'] = $this->Auth->user('email'); 			
			
			$valid = true;
			if (empty($this->data['Comment']['author_name'])) {
				$valid = false;
			}
			if (!$valid) {
				$this->Session->setFlash(__('Please enter necessery information', true));
				return; 				
			}
			...
		}
		return $this->Comments->callback_add($modelId, $commentId, $displayType, $data);
	} 
  

### Component parameters

Plugin use several named parameters that passed during comment operations like create, delete, reply or approve is performed.

* comment_view_type - Parameter that allow to specify what type of comments system used. Currently allowed to use one of 'flat', 'threaded', 'tree'. This parameter possible and useful to setup in beforeFilter to use only one type of view. If user allowed to choose between tree and flat, then it parameter can be dynamic.
* comment_action - this parameter used, to pass what action should performed. N
* comment - comment id passed here.

Please not that parameters listed here should not be used as named parameters in your app!

### Component settings

All components parameters should be overwritten in beforeFilter method.

 * actionNames -  Name of actions comments component should use. By default it 'view' and 'comments'. So if you want to have comments on 'display' action you need to set it in beforeFilter method.
 * modelName - Name of 'commentable' model. By default it is default controller's model name (Controller::$modelClass)
 * assocName - Name of association for comments
 * userModel - Name of user model associated to comment. By default it is UserModel. Important to have different name with User model name.
 * userModelClass - Class name for the user model. By default it is User. If you use other model for identity purpose you need to setup it here.
 * unbindAssoc - enabled if this component should permanently unbind association to Comment model in order to not
 * query model for not necessary data in Controller::view() action
 * commentParams - Parameters passed to view.
 * viewVariable -  Name of view variable which contains model data for view() action. Needed just for PK value available in it. By default Inflector::variable(Comments::modelName)
 * viewComments - Name of view variable for comments data. By default 'commentsData'
 * allowAnonymousComment - Flag to allow anonymous user make comments. By default it is false.

Exists two way to change settings values for component. You can change it in beforeFilter callback before component will initialized, or pass parameters during initialization:


	public $components = array('Comments.Comments' => array('userModelClass' => 'Users.User'));

 
### Helper parameters and methods

 * target - used in ajax mode to specify block where comment widget stored
 * ajaxAction - array that specify route to the action or string containing action name. Used in ajax mode.
 * displayUrlToComment - used if you want to have separate pages for each comment. By default false.
 * urlToComment - used if you want to have separate pages for each comment. Contain url to view comment.
 * allowAnonymousComment - boolean var, that show if anonymous comments allowed
 * viewInstance - View instance class, that used to generated the page.
 * subtheme - parameter that allow to have several set of templates for one view type. So if you want to have two different representation of 'flat' type for posts and images you just used two subthemes 'posts' and 'images' like 'flat_posts' and 'flat_images'.

#### Template system structure
The template system consists of several elements stored in comments plugin.

It is 'form', 'item', 'paginator' and 'main'.

 * Main element is rendered and use all other to render all parts of comments system.
 * Item element is a just one comment block.
 * Paginator is supposed to used by 'flat' and 'tree' themes. Threaded type theme is not allowed to paginate comments.
 * Form element contains form markup to add comment or reply.

All elements are stored in the structure views/elements/...type..., where ...type... is one of view types: 'flat', 'tree', 'threaded'.
We can define elements in own plugin or app that used comments or use default templates.
Sometimes we need to have several sets of templates for one view type. For example, if we want to have two different representation of 'flat' type for posts and images views we just used two subthemes for 'flat'.
So in elements/comments we need to create folders 'flat_posts' and 'flat_images' and copy elements from '/elements/comments/flat' here and modify them.

### Ajax support

The plugin was tested with jquery engine. Since cakephp js helper support many engines, theoretically you can choose any of supported js engines and comments plugin should support it.

To turn on ajax mode you need set pass two parameters to the helper:

	<?php $commentWidget->options(array(
		'target' => '#comments',
		'ajaxAction' => 'comments'));
	?>


Also necessary to implement comments view, that will just contains previous block and will include ajax element from comments plugin:

	<?php 
		$commentWidget->options(array(
		'target' => '#comments',
		'ajaxAction' => 'comments'));
		echo $this->element('ajax', array('plugin' => 'comments'));
	?>


The comments action in controller should be same as view action, the difference only in view.
