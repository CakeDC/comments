Comment Widget Helper
=====================

Helper Parameters and Methods
-----------------------------

 * **target:** Used in ajax mode to specify block where comment widget stored.
 * **ajaxAction:** Array that specify route to the action or string containing action name. Used in ajax mode.
 * **displayUrlToComment:** Used if you want to have separate pages for each comment. By default false.
 * **urlToComment:** Used if you want to have separate pages for each comment. Contain url to view comment.
 * **allowAnonymousComment:** boolean var, that show if anonymous comments allowed.
 * **viewInstance:** View instance class, that used to generated the page.
 * **subtheme:** Parameter that allow to have several set of templates for one view type. So if you want to have two different representation of ```flat``` type for posts and images you just used two subthemes ```posts``` and ```images``` like ```flat\_posts``` and ```flat\_images```.

Template System Structure
-------------------------

The template system consists of several elements stored in comments plugin.

These are ```form```, ```item```, ```paginator``` and ```main```.

 * Main element is rendered and use all other to render all parts of comments system.
 * Item element is a just one comment block.
 * Paginator is supposed to used by 'flat' and 'tree' themes. Threaded type theme is not allowed to paginate comments.
 * Form element contains form markup to add comment or reply.

All elements are stored in the structure

```
views/elements/comments/<type>
```

where ```<type>``` is one of view types, ```flat```, ```tree``` or ```threaded```. It is possible to define any of this elements in any of your plugins or app using this comments system.

Sometimes we need to have several sets of templates for one view type. For example, if we want to have two different representation of ```flat``` type for posts and images views we just used two subthemes for ```flat```.

So in elements/comments we need to create folders ```flat\_posts``` and ```flat\_images``` and copy elements from ```/elements/comments/flat``` here and modify them.
