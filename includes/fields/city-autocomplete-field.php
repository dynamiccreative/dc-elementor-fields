<?php
// includes/city-autocomplete-field.php

if (!defined('ABSPATH')) { exit; }

if (!class_exists('City_Autocomplete_Field')) {
    class City_Autocomplete_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base {
        
        public function get_type() {
            return 'google_city_autocomplete';
        }
        
        public function get_name() {
            return 'Google City Autocomplete';
        }
        
        // Rend le champ dans le formulaire
        public function render($item, $item_index, $form) {
            $form->add_render_attribute(
                'input' . $item_index,
                [
                    'class' => 'elementor-field-textual city-autocomplete',
                    'placeholder' => $item['placeholder'],
                ]
            );
            
            echo '<input ' . $form->get_render_attribute_string('input' . $item_index) . '>';
        }
        
        // Met à jour la valeur du champ
        public function update_field($value, $data) {
            return sanitize_text_field($value);
        }
    }
}

/**
 * Form Validation hook for tel
 * Validate the Tel field is in XXXXXXXXX format.
 */
//add_action( 'elementor_pro/forms/validation/tel', 'field_validation_tel', 10, 3 );
function field_validation_tel ( $field, $record, $ajax_handler ) {
    // Match this format XXXXXXXXX, 0612102030
    if ( preg_match( '/[0-9]{10}/', $field['value'] ) !== 1 ) {
        $ajax_handler->add_error( $field['id'], 'Veillez à ce que le numéro de téléphone soit au format XXXXXXXXX, ex: 0612102030' );
    }
}