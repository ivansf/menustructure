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
	$items['first/add'] = array('id' => 1, 'parent_id' => 0, 'title' => 'test 1', 'body' => 'body of main link');
	$items['test'] = array('id' => 2, 'parent_id' => 0, 'title' => 'test 1', 'body' => 'body from test');
	$items['linkmore'] = array('id' => 3, 'parent_id' => 2, 'title' => 'test 2');
	$items[''] = array('id' => 5, 'parent_id' => 0, 'title' => 'Index', 'body' => 'Body of the index!');
	$items['content/secondary'] = array('id' => 6, 'parent_id' => 5, 'title' => 'test 2');
	$items['another/path/see'] = array('id' => 6, 'parent_id' => 5, 'title' => 'test 123');

Pass the structure to the view

	$current = '';
	$current .= $seg1 ? $seg1 : '';
	$current .= $seg2 ? ('/' . $seg2): '';
	$current .= $seg3 ? ('/' . $seg3): '';
	$body = isset($items[$current]['body']) ? $items[$current]['body'] : '';
	$this->request->response = View::Factory('welcome/menu')
		->set('menu', MenuStructure::factory($items)->get_menu())
		->set('body', $body);

(TIP: Make sure your routes allow that many segments)

# Options

You can pass an array with options. The available ones are:

	link_prepend - string - string goes before the uri.

Example

	MenuStructure::factory($items, array('link_prepend' => 'extraurl/'))->get_menu()

You should be able to print the entire navigation tree using a single query.