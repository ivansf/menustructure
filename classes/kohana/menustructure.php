<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Menu Structure Controller
 *
 * @package menustructure
 * @author Ivan Soto
 */
abstract class Kohana_MenuStructure {

	/**
	 * @var html containing the full list
	 */
	protected $html;

	/**
	 * @var array or object that contains the menu elements
	 */
	protected $items;

	/**
	 * @var optional options
	 */
	protected $options;

	/**
	 * Creates a new MenuStructure object.
	 *
	 * @param   array  configuration
	 * @options array
	 * 		link_prepend string - string goes before the uri.
	 * 		link_to_id boolean - uses id instead of link.
	 * @return  MenuStructure
	 */
	public static function factory($items, $options = array()) {
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
		$this->options = $options;
		$this->items = $items;
		$this->get_menu($items);
	}

	/**
	 * Gets the menu in html ready to render.
	 *
	 * @return string
	 */
	function get_menu() {

		$html = '';
		$root = 0;

		if (!is_array($this->items)) {

			// Assuming the menu is an ORM object
			$items_holder = array();
			foreach ($this->items as $i)
				$items_holder[] = array('id' => $i->id, 'parent_id' => $i->parent_id, 'title' => $i->title,
					'link' => $i->link);
			$this->items = $items_holder;
		}

		foreach ($this->items as $item)
			$children[$item['parent_id']][] = $item;

		$loop = !empty($children[$root]);
		$parent = $root;
		$stack = array();

		$html .= '<ul>' . "\n"; // opening the list

		while ($loop && (($option = each($children[$parent])) || ($parent > $root))) {
			if (!$option) {
				$parent = array_pop($stack);
				$html .= '</ul>' . "\n" . '</li>' . "\n";
			} else if (!empty($children[$option['value']['id']])) {
				$html .= '<li>' . $this->_build_link($option['value']) . "\n";
				$html .= '<ul class="submenu">' . "\n";

				array_push($stack, $option['value']['parent_id']);
				$parent = $option['value']['id'];
			} else
				$html .= '<li>' . $this->_build_link($option['value']) .
						'</li>' . "\n";
		}
		$html .= '</ul>'; // opening the list
		return $html;
	}


	/**
	 * Build the list as a select box
	 *
	 * @return string
	 */
	function get_select_options($selected = null) {

		$html = '';
		$root = 0;

		if (!is_array($this->items)) {

			// Assuming the menu is an ORM object
			$items_holder = array();
			foreach ($this->items as $i)
				$items_holder[] = array('id' => $i->id, 'parent_id' => $i->parent_id, 'title' => $i->title,
					'link' => $i->link);
			$this->items = $items_holder;
		}

		foreach ($this->items as $item)
			$children[$item['parent_id']][] = $item;

		$loop = !empty($children[$root]);
		$parent = $root;
		$stack = array();

		$html .= '<select id="menu_structure" name="menu_structure">' . "\n"; // opening the list
		//if (!$selected)
			$html .= '<option value="0">No parent</option>';
		$extra = '';
		while ($loop && (($option = each($children[$parent])) || ($parent > $root))) {
			if (!$option) {
				$parent = array_pop($stack);
				$extra = substr($extra, 0, -1);
				//$html .= '' . "\n" . '</option>' . "\n";
			} else if (!empty($children[$option['value']['id']])) {
				$html .= $this->_build_option($option['value'], $extra, $selected) . "\n";
				//$html .= '<optgroup label="submenu">' . "\n";
				$extra .= '-';
				array_push($stack, $option['value']['parent_id']);
				$parent = $option['value']['id'];
			} else
				$html .= $this->_build_option($option['value'], $extra, $selected);
		}
		$html .= '</select>'; // opening the list
		return $html;
	}

	/**
	 * Returns the menu list in an array.
	 *
	 * @return void
	 */
	function get_array() {
		// TODO: fully understand it and make this happen.
		return null;
	}


	/**
	 * Builds the anchor to be inserted in the link.
	 * It will prepend a string if passed in the options
	 *
	 * @param string $link
	 * @param string $title
	 * @return string
	 */
	function _build_link($item) {
		$link = '';
		// checking if we want to append something to the link
		if (isset($this->options['link_prepend']))
			$link .= $this->options['link_prepend'];

		// Are we linking to the id instead of the link
		if (isset($this->options['link_to_id']))
			$link .= $item['id'];
		else
			$link .= $item['link'];

		return Html::anchor($link, $item['title']);
	}

	function _build_option($item, $extra, $selected = null) {
		$str_selected = '';
		if ($selected == $item['id'])
			$str_selected = ' selected="selected"';
		return sprintf('<option value="%1$d" %4$s>%2$s %3$s</option>' . "\n",
			$item['id'], $extra, $item['title'], $str_selected);
	}

}