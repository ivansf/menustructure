# What it does?

menustructure simply creates a tree navigation based on a multi-dimentional array

# Installation

Enable the module in the bootstrap

	Kohana::modules(array(
		// 'auth'       => MODPATH.'auth',       // Basic authentication
		// 'cache'      => MODPATH.'cache',      // Caching with multiple backends
		...
		// 'userguide'  => MODPATH.'userguide',  // User guide and API documentation
		'menustructure'  => MODPATH.'menustructure',  // enables menu structure
	));

### Building the data

As an example, create the following array in your controller. (I'm pretty sure you want to get it from a database).

	$items = array();
	$items[] = array('id' => 1, 'parent_id' => 0, 'title' => 'test 1', 'link' => 'welcome/test');
	$items[] = array('id' => 2, 'parent_id' => 0, 'title' => 'test 1', 'link' => 'test');
	$items[] = array('id' => 3, 'parent_id' => 2, 'title' => 'test 2', 'link' => '#');
	$items[] = array('id' => 5, 'parent_id' => 0, 'title' => 'test 2', 'link' => '#');
	$items[] = array('id' => 6, 'parent_id' => 5, 'title' => 'test 2', 'link' => '#');
	$items[] = array('id' => 6, 'parent_id' => 5, 'title' => 'test 123', 'link' => '#');

Pass the structure to the view

	$this->request->response = View::Factory('welcome/menu')
		->set('menu', MenuStructure::factory($items)->get_menu()));

# Options

You can pass an array with options. The available ones are:

	link_prepend - string - string goes before the uri.
	link_to_id - boolean - if you want to render links as the id.
	current_path - string - Pass the current path in order to mark the active link.

### Example

	MenuStructure::factory($items, array('link_prepend' => 'extraurl/', 'link_to_id' => true))->get_menu()

You should be able to print the entire navigation tree using a single query.