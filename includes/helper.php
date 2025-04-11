<?php
function update_elementor_control($widget, $control_name, $callback) {
    $elementor = \ElementorPro\Plugin::elementor();
    $control_data = $elementor->controls_manager->get_control_from_stack($widget->get_unique_name(), $control_name);
    if (is_wp_error($control_data)) {
        return;
    }
    $control_data = $callback($control_data);
    $widget->update_control($control_name, $control_data);
}