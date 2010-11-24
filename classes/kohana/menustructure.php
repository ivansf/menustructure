<?php defined('SYSPATH') or die('No direct script access.');

abstract class Kohana_MenuStructure {

	protected $html;
	protected $items;
	/**
	 * Creates a new MenuStructure object.
	 *
	 * @param   array  configuration
	 * @return  MenuStructure
	 */
	public static function factory($items)
	{
		return new MenuStructure($items);
	}


	/**
	 * Constructor
	 *
	 * @param  $items
	 * @param  $config
 	 *
	 */
	protected function __construct($items) {
		$html = '';
		$root = 0;

		if (!is_array($items))
			$this->items = (array)$items;
		else
			$this->items = $items;
		
		foreach ( $this->items as $item )
			$children[$item['parent_id']][] = $item;

		$loop = !empty($children[$root] );
		$parent = $root;
		$stack = array();

		// HTML wrapper for the menu (open)
		$html .= '<ul>'  . "\n";

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
		$html .= '</ul>';
		$this->html = $html;
	}

	function get_menu() {
		return $this->html;
	}

	function _build_link($link = '', $title = '') {
		return Html::anchor($link, $title);
		//return '<a href="' . $link . '">' . $title .'</a>';
	}




}