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
	}

	/**
	 * New way for getting the menu, first parsing it into a multidimensional array.
	 * Not very efficient but it allows to point first/last links and active and parent-active
	 * one.
	 *
	 * @return string
	 */
	function get_html_full_menu() {
		$menu = $this->get_array();

		// building our menu in html
		$mhtml = '<ul class="menustructure">' . "\n";
		$c1 = $c2 = $c3 = 0;
		foreach ($menu as $mid => $m) {

			if ($c1 === 0)
				$mhtml .= '<li class="first">';
			else if ($c1 === (count($menu) -1))
				$mhtml .= '<li class="last">';
			else
				$mhtml .= '<li>';

			$mhtml .= Html::anchor($m['link'], $m['title']) . "\n";

			//going level 2
			$c2 = $c3 = 0;
			if (isset($m['entries']) && is_array($m['entries'])) {
				$mhtml .= '<ul>' . "\n";
				foreach ($m['entries'] as $i2 => $m2) {

					// building level 2 menu item
					if ($c2 === 0)
						$mhtml .= '<li class="first">' . "\n";
					else if ($c2 === (count($m['entries']) - 1))
						$mhtml .= '<li class="last">' . "\n";
					else
						$mhtml .= '<li>' . "\n";

					$mhtml .= Html::anchor($m2['link'], $m2['title']) . "\n";

					//going level 3
					$c3 = 0;
					if (isset($m2['entries']) && is_array($m2['entries'])) {
						$mhtml .= '<ul>' . "\n";
						foreach ($m2['entries'] as $i3 => $m3) {

							// building level 3 menu item
							if ($c3 === 0)
								$mhtml .= '<li class="first">' . "\n";
							else if ($c3 === (count($m2['entries']) - 1))
								$mhtml .= '<li class="last">' . "\n";
							else
								$mhtml .= '<li>' . "\n";

							$mhtml .= Html::anchor($m3['link'], $m3['title']) . "\n";
							// no level 4 for now
							$c3++;
						}
						$mhtml .= '</ul>' . "\n";
					}
					$mhtml .= '</li>' . "\n";
					$c2++;
				}
				$mhtml .= '</ul>' . "\n";
			}
			$mhtml .= '</li>' . "\n";
			$c1++;
		}
		$mhtml .= '</ul>';
		echo $mhtml;
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

		$this->items[0]['first'] = true;


		foreach ($this->items as $item)
			$children[$item['parent_id']][] = $item;

		$loop = !empty($children[$root]);
		$parent = $root;
		$stack = array();
		
		$html .= '<ul>' . "\n"; // opening the list

		while ($loop && (($option = each($children[$parent])) || ($parent > $root))) {

			if (!$option) {
				$parent = array_pop($stack);
				$html .= '</ul>' . "\n" . '</li>' . "\n"; //closing a submenu
				$counter = 0;
			} else if (!empty($children[$option['value']['id']])) {
				$active = ($option['value']['link'] == $this->options['current_path']) ? true : false;
				$html .= '<li>' . $this->_build_link($option['value'], $active) . "\n";
				$html .= '<ul class="submenu">' . "\n";
				array_push($stack, $option['value']['parent_id']);
				$parent = $option['value']['id'];
			} else {
				$active = ($option['value']['link'] == $this->options['current_path']) ? true : false;
				$html .= '<li>' . $this->_build_link($option['value'], $active) . '</li>' . "\n";

			}
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

		// Checking if the menu is an object, if it is, assuming is an ORM object
		if (!is_array($this->items)) {
			$items_holder = array();
			foreach ($this->items as $i)
				$items_holder[] = array('id' => $i->id, 'parent_id' => $i->parent_id, 'title' => $i->title,
					'link' => $i->link);
			$this->items = $items_holder;
		}

		// Building the menu array
		$menu = array();
		foreach ($this->items as $i) {
			if (! $i['parent_id']) {

				// adding the top menu (level 1 items)
				$menu[$i['id']] = array(
					'title' => $i['title'],
					'link' => $i['link']
				);

				$level1_id = $i['id'];

				// going through this one childs looking for level 2 entries
				foreach ($this->items as $j) {
					if ($j['parent_id'] == $level1_id) {
						$menu[$level1_id]['entries'][$j['id']] = array(
							'title' => $j['title'],
							'link' => $j['link']
						);

						// going to find level 3 items
						$level2_id = $j['id'];
						foreach ($this->items as $k) {
							if ($k['parent_id'] == $level2_id) {
								$menu[$level1_id]['entries'][$level2_id]['entries'][$k['id']] = array(
									'title' => $k['title'],
									'link' => $k['link']
								);
							}
						}

					}
				}
			}
		}
		return $menu;
	}


	/**
	 * Builds the anchor to be inserted in the link.
	 * It will prepend a string if passed in the options
	 *
	 * @param string $link
	 * @param string $title
	 * @return string
	 */
	function _build_link($item, $active = false) {
		$link = '';
		$attributes = array();
		$attributes['class'] = '';
		if (isset($item['first']) && $item['first'])
			$attributes['class'] = 'first';
		if (isset($item['last']) && $item['last'])
			$attributes['class'] = 'last';
		if ($active)
			$attributes['class'] .= ' active';
		// checking if we want to append something to the link
		if (isset($this->options['link_prepend']))
			$link .= $this->options['link_prepend'];

		// Are we linking to the id instead of the link
		if (isset($this->options['link_to_id']))
			$link .= $item['id'];
		else
			$link .= $item['link'];

		return Html::anchor($link, $item['title'], $attributes);
	}

	function _build_option($item, $extra, $selected = null) {
		$str_selected = '';
		if ($selected == $item['id'])
			$str_selected = ' selected="selected"';
		return sprintf('<option value="%1$d" %4$s>%2$s %3$s</option>' . "\n",
			$item['id'], $extra, $item['title'], $str_selected);
	}

}