<?php

defined( 'ABSPATH' ) || exit;

class DEF_Elementor_Widget_List {

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 * @access private
	 * @static
	 *
	 * @var EAP_Elementor_Widget_List The single instance of the class.
	 */

	private static $instance = null;
	private static $list     = array(
		'def_city_autocomplete'   => array(
			'name'    => 'def_city_autocomplete_field',
			'slug'    => 'city-autocomplete-field',
			'function'=> 'City_Autocomplete_Field',
			'title'   => 'City Autocomplete',
			'type'	  => 'widget'
		),
		'def_select2'   => array(
			'name'    => 'def_select2',
			'slug'    => 'select2',
			'function'=> 'Custom_Select2_Field',
			'title'   => 'Select 2',
			'type'	  => 'widget'
		),
		'def_select_posts'   => array(
			'name'    => 'def_select_posts',
			'slug'    => 'select-posts',
			'function'=> 'Select_Posts_Field',
			'title'   => 'Select CPT',
			'type'	  => 'widget'
		),
		'def_extension_icons'   => array(
			'name'    => 'def_extension_icons',
			'slug'    => 'icons',
			'function'=> '',
			'title'   => 'Icons Pre/Post Field',
			'type'	  => 'extension'
		),
	
	);



	/**
	 *
	 * Usage :
	 *  get full list >> get_list() []
	 *  get full list of active widgets >> get_list(true, '', 'active') // []
	 *  get specific widget data >> get_list(true, 'image-accordion') [] or false
	 *  get specific widget data (if active) >> get_list(true, 'image-accordion', 'active') [] or false
	 *
	 * @param bool $filtered
	 * @param string $widget
	 * @param string $check_method - active|list
	 *
	 * @return array|bool|mixed
	 */
	public function get_list() {
		$all_list = self::$list;

		//$all_list = apply_filters( 'xpro_elementor_addons_widgets_list', self::$list );

		return $all_list;
	}

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return Xpro_Elementor_Widget_List An instance of the class.
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
