<?php
if (!defined('ABSPATH')) { exit; }

class Custom_Select2_Field extends \ElementorPro\Modules\Forms\Fields\Field_Base {
                        
        public function __construct() {
            parent::__construct();
            add_action('elementor/element/form/section_field_style/before_section_end', [$this, 'update_style_controls']);
        }

        public function get_type() {
            return 'custom_select2';
        }
        
        public function get_name() {
            return __('Select2 Personnalisé', 'votre-plugin');
        }

        /*// Charger les assets
        public function get_style_depends(): array {
            return ['select2'];
        }
        
        public function get_script_depends(): array {
            return [ 'select2', 'select2-inline' ];
        }*/
    
        // Ajouter les contrôles dans l'éditeur
        public function update_controls( $widget ): void {

            $elementor = \ElementorPro\Plugin::elementor();

            $control_data = $elementor->controls_manager->get_control_from_stack( $widget->get_unique_name(), 'form_fields' );

            if ( is_wp_error( $control_data ) ) {
                return;
            }
            $field_controls = [
                'options' => [
                    'name' => 'options',
                    'label' => __('Options', 'votre-plugin'),
                    'type' => \Elementor\Controls_Manager::TEXTAREA,
                    'description' => __('Entrez une option par ligne au format valeur|label', 'votre-plugin'),
                    'default' => "value1|Option 1\nvalue2|Option 2\nvalue3|Option 3",
                    'condition' => [
                        'field_type' => $this->get_type(),
                    ],
                ],
                'multiple' => [
                    'name' => 'multiple',
                    'label' => __('Sélection Multiple', 'votre-plugin'),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'default' => '',
                    'condition' => [
                        'field_type' => $this->get_type(),
                    ],
                ],
                'placeholder' => [
                    'name' => 'placeholder',
                    'label' => __('Placeholder', 'votre-plugin'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __('Sélectionnez une option', 'votre-plugin'),
                    'condition' => [
                        'field_type' => $this->get_type(),
                    ],
                ],
            ];

            $control_data['fields'] = $this->inject_field_controls( $control_data['fields'], $field_controls );

            $widget->update_control( 'form_fields', $control_data );
        }
        
        

        // Rendre le champ dans le frontend
        public function render($item, $item_index, $form) {
            $options_raw = $item['options'];
            $options = [];
            
            // Parse les options
            if ($options_raw) {
                $lines = explode("\n", $options_raw);
                foreach ($lines as $line) {
                    $parts = explode('|', trim($line));
                    if (count($parts) === 2) {
                        $options[$parts[0]] = $parts[1];
                    }
                }
            }
            
            $field_id = $form->get_id() . '_' . $item['custom_id'];
            $multiple = $item['multiple'] === 'yes' ? 'multiple' : '';
            $required = $item['required'] ? 'required' : '';
            
            // Générer le HTML du champ
            $form->add_render_attribute(
                'select' . $item['custom_id'],
                [
                    'name' => "fields[{$item['custom_id']}]" . ($multiple ? '[]' : ''),
                    'id' => $field_id,
                    'class' => ['custom-select2-field', 'elementor-field'],
                    'data-placeholder' => $item['placeholder'],
                ]
            );
            
            ?>
            <select <?php echo $form->get_render_attribute_string('select' . $item['custom_id']); ?> <?php echo $multiple; ?> <?php echo $required; ?>>
                <option value=""><?php echo esc_html($item['placeholder']); ?></option>
                <?php
                foreach ($options as $value => $label) {
                    echo '<option value="' . esc_attr($value) . '">' . esc_html($label) . '</option>';
                }
                ?>
            </select>
            <?php
        }
        
        
        
        // Validation du champ
        public function validation($field, $record, $ajax_handler) {
            if ($field['required'] && empty($field['value'])) {
                $ajax_handler->add_error(
                    $field['id'],
                    __('Ce champ est requis', 'votre-plugin')
                );
            }
        }

        public function update_style_controls($widget) {
            update_elementor_control($widget, 'field_background_color', function ($control_data) {
                $control_data['selectors']['{{WRAPPER}} .elementor-field-group .select2-container--default .select2-selection--single'] = 'background-color: {{VALUE}};';
                //$control_data['selectors']['{{WRAPPER}} .elementor-field-group .select2 .elementor-field-textual'] = 'background-color: {{VALUE}}; min-height:40px';
                //$control_data['selectors']['{{WRAPPER}} .mce-panel'] = 'background-color: {{VALUE}};';
                return $control_data;
            });
            update_elementor_control($widget, 'field_typography', function ($control_data) {
                if (!empty($control_data['selectors'])) {
                    $values = \reset($control_data['selectors']);
                    $control_data['selectors']['{{WRAPPER}} .select2-container--default .select2-selection--single .select2-selection__rendered'] = $values;
                    $control_data['selectors']['{{WRAPPER}} .select2-container--default .select2-selection--single .select2-selection__rendered'] = $values;
                    $control_data['selectors']['{{WRAPPER}} .select2-container--default .select2-selection--single, {{WRAPPER}} .select2-container--default .select2-selection--multiple'] = 'height: auto;';
                }
                return $control_data;
            });
            update_elementor_control($widget, 'field_text_color', function ($control_data) {
                $control_data['selectors']['{{WRAPPER}} .elementor-field-group .select2-container--default .select2-selection--single .select2-selection__placeholder'] = 'color: {{VALUE}};';
                $control_data['selectors']['{{WRAPPER}} .elementor-field-group .select2-container--default .select2-selection--single .select2-selection__rendered'] = 'color: {{VALUE}};';
                //$control_data['selectors']['{{WRAPPER}} .mce-panel'] = 'background-color: {{VALUE}};';
                return $control_data;
            });
            update_elementor_control($widget, 'field_border_color', function ($control_data) {
                $control_data['selectors']['{{WRAPPER}} .elementor-field-group .select2-container--default .select2-selection--single'] = 'border-color: {{VALUE}};';
                //$control_data['selectors']['{{WRAPPER}} .elementor-field-group .elementor-select-wrapper .select2 .elementor-field-textual'] = 'border-color: {{VALUE}};';
                //$control_data['selectors']['{{WRAPPER}} .elementor-field-group .mce-panel'] = 'border-color: {{VALUE}};';
                return $control_data;
            });
            update_elementor_control($widget, 'field_border_width', function ($control_data) {
                $control_data['selectors']['{{WRAPPER}} .elementor-field-group .select2-container--default .select2-selection--single'] = 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};';
                //$control_data['selectors']['{{WRAPPER}} .elementor-field-group .elementor-select-wrapper .select2 .elementor-field-textual'] = 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};';
                //$control_data['selectors']['{{WRAPPER}} .elementor-field-group .mce-panel'] = 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};';
                return $control_data;
            });
            update_elementor_control($widget, 'field_border_radius', function ($control_data) {
                $control_data['selectors']['{{WRAPPER}} .elementor-field-group .select2-container--default .select2-selection--single'] = 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};';
                ///$control_data['selectors']['{{WRAPPER}} .elementor-field-group .elementor-select-wrapper .select2 .elementor-field-textual'] = 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};';
                //$control_data['selectors']['{{WRAPPER}} .elementor-field-group .mce-panel'] = 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};';
                return $control_data;
            });
        }
    }