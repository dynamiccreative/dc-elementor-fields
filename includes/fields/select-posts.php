<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor Form Field - Formations List
 *
 * Add a new "Formations List" field to Elementor form widget.
 *
 * @since 1.0.0
 */
class Select_Posts_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base {

	/**
	 * Get field type.
	 *
	 * Retrieve local-tel field unique ID.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Field type.
	 */
	public function get_type() {
		return 'select-posts';
	}

	/**
	 * Get field name.
	 *
	 * Retrieve local-tel field label.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Field name.
	 */
	public function get_name() {
		return esc_html__( 'Select Posts', 'dc-elementor-fields' );
	}

	/**
	 * Render field output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param mixed $item
	 * @param mixed $item_index
	 * @param mixed $form
	 * @return void
	 */
	public function render( $item, $item_index, $form ) {
		$cpt = !empty($item['cpt_name']) ? esc_attr($item['cpt_name']) : 'post';
		// Get all posts for the specified CPT
        $args = [
            'post_type' => $cpt,
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ];
        //$posts = get_posts($args);

		$form->add_render_attribute(
			'select' . $item_index,
			[
				'class' => 'elementor-field-textual elementor-size-sm elementor-field-select-posts',
				'title' => esc_html__( 'Formations', 'dc-elementor-fields' ),
				'name' => 'form_fields['.$item["custom_id"].']',
				'id' => 'form-field-'.$item["custom_id"],
			]
		);
		$requis = '';
		if ($item['required']) $requis = 'required="required"';
		//$slug = $item['slug-posttype'];
		//if (!$slug) $slug = 'formation';
		//$args = array( 'posts_per_page' => -1, 'post_type' => $slug );
    	$loop = new WP_Query( $args );
    	$s = "";
    	while ( $loop->have_posts() ) : $loop->the_post();
    		$se = '';
    		if (get_post_field( 'post_name' ) == $item['post-selected']) $se = 'selected';
    		$s .= '<option value="'.get_post_field( 'post_name' ).'" '.$se.'>'.mb_strimwidth(get_the_title(), 0, 60, '...', 'UTF-8').'</option>';
    	endwhile;
    	wp_reset_postdata();
    	if ($s) {
	    	echo '<div class="elementor-field elementor-select-wrapper remove-before">';
	    	echo '<div class="select-caret-down-wrapper"><svg aria-hidden="true" class="e-font-icon-svg e-eicon-caret-down" viewBox="0 0 571.4 571.4" xmlns="http://www.w3.org/2000/svg"><path d="M571 393Q571 407 561 418L311 668Q300 679 286 679T261 668L11 418Q0 407 0 393T11 368 36 357H536Q550 357 561 368T571 393Z"></path></svg></div>';
			echo '<select ' . $form->get_render_attribute_string( 'select' . $item_index ) . ' '.$requis.'><option value="">'.$item['field_label'].'</option>';
			echo $s;
			echo '</select></div>';
		} else {
			echo 'no data';
		}
	}

	/**
	 * Field validation.
	 *
	 * Validate local-tel field value to ensure it complies to certain rules.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param \ElementorPro\Modules\Forms\Classes\Field_Base   $field
	 * @param \ElementorPro\Modules\Forms\Classes\Form_Record  $record
	 * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
	 * @return void
	 */
	public function validation( $field, $record, $ajax_handler ) {
		if (!empty($field['required']) && empty($field['value'])) {
            $ajax_handler->add_error($field['id'], __('Ce champ est obligatoire', 'dc-elementor-fields'));
        }
	}

	/**
	 * Field constructor.
	 *
	 * Used to add a script to the Elementor editor preview.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
		//add_action( 'elementor/preview/init', [ $this, 'editor_preview_footer' ] );
	}

	
	/**
	 * Update form widget controls.
	 *
	 * Add input fields to allow the user to customize the credit card number field.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param \Elementor\Widget_Base $widget The form widget instance.
	 * @return void
	 */
	public function update_controls( $widget ) {
		$elementor = \ElementorPro\Plugin::elementor();

		$control_data = $elementor->controls_manager->get_control_from_stack( $widget->get_unique_name(), 'form_fields' );

		if ( is_wp_error( $control_data ) ) {
			return;
		}

		$field_controls = [
			'post-selected' => [
				'name' => 'post-selected',
				'label' => esc_html__( 'Slug Option Selected', 'dc-elementor-fields' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'condition' => [
					'field_type' => $this->get_type(),
				],
				'tab'          => 'content',
				'inner_tab'    => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],
			'cpt_name' => [
                'name' => 'cpt_name',
                'label' => __('Custom Post Type', 'dc-elementor-fields'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_custom_post_types(),
                'default' => 'post',
                'tab' => 'content',
                'inner_tab' => 'form_fields_content_tab',
                'tabs_wrapper' => 'form_fields_tabs',
                'condition' => [
					'field_type' => $this->get_type(),
				],
            ],
			/*'slug-posttype' => [
				'name' => 'slug-posttype',
				'label' => esc_html__( 'Slug Post Type', 'dc-form-local-field' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'condition' => [
					'field_type' => $this->get_type(),
				],
				'tab'          => 'content',
				'inner_tab'    => 'form_fields_content_tab',
				'tabs_wrapper' => 'form_fields_tabs',
			],*/
		];

		$control_data['fields'] = $this->inject_field_controls( $control_data['fields'], $field_controls );

		$widget->update_control( 'form_fields', $control_data );
	}

	private function get_custom_post_types() {
        $cpt_args = [
            'public' => true,
            '_builtin' => false,
        ];
        $post_types = get_post_types($cpt_args, 'objects');
        $options = ['post' => __('Post', 'dc-elementor-fields')];

        foreach ($post_types as $post_type) {
            $options[$post_type->name] = $post_type->labels->singular_name;
        }

        return $options;
    }

}
