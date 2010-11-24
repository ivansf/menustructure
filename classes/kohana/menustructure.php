<?php defined('SYSPATH') or die('No direct script access.');

abstract class Kohana_MenuStructure {

	protected $html;
	protected $items;
	protected $options;
	/**
	 * Creates a new MenuStructure object.
	 *
	 * @param   array  configuration
	 * @options array
	 * 		link_prepend string - string goes before the uri.
	 * @return  MenuStructure
	 */
	public static function factory($items, $options = array())
	{
		return new MenuStructure($items, $options);
	}


	/**
	 * Constructor
	 *
	 * @param  $items
	 * @param  $config
 	 *
	 */
	protected function __construct($items, $options) {
		$html = '';
		$root = 0;
		$this->options = $options;

		if (!is_array($items)) {
			$this->items = $items;
			// Assuming the menu is an ORM object
			$items_holder = array();
			foreach ($this->items as $i)
				$items_holder[] = array('id' => $i->id, 'parent_id' => $i->parent_id, 'title' => $i->title,
					'link' => $i->link);
			$this->items = $items_holder;
		} else
			$this->items = $items;
		
		foreach ( $this->items as $item )
			$children[$item['parent_id']][] = $item;

		$loop = !empty($children[$root] );
		$parent = $root;
		$stack = array();

		$html .= '<ul>'  . "\n"; // opening the list

		while ( $loop && ( ( $option = each( $children[$parent] ) ) || ( $parent > $root ) ) ) {
			if (!$option) {
				$parent = array_pop( $stack );
				$html .= '</ul>'  . "\n" . '</li>'  . "\n";
			} else if ( !empty( $children[$option['value']['id']] ) ) {
				$html .= '<li>'. $this->_build_link($option['value']['link'], $option['value']['title'])  . "\n";
				$html .= '<ul class="submenu">' . "\n";

				array_push( $stack, $option['value']['parent_id'] );
				$parent = $option['value']['id'];
			} else
				$html .= '<li>' . $this->_build_link($option['value']['link'], $option['value']['title']) .
						'</li>' . "\n";
		}
		$html .= '</ul>'; // opening the list
		$this->html = $html;
	}

	/**
	 * Gets the menu in html ready to render.
	 *
	 * @return string
	 */
	function get_menu() {
		return $this->html;
	}

	/**
	 * Builds the anchor to be inserted in the link.
	 * It will prepend a string if passed in the options
	 *
	 * @param string $link
	 * @param string $title
	 * @return string
	 */
	function _build_link($link = '', $title = '') {
		// checking if we want to append something to the link
		if (isset($this->options['link_prepend']))
			$link = $this->options['link_prepend'] . $link;
		
		return Html::anchor($link, $title);
	}




}