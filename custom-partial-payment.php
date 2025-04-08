<?php
/*
Plugin Name: Custom Partial Payment for WooCommerce
Description: Enables partial payment for users who select "I am 18 years old".
Version: 1.0
Author: Shuvo Gosh
Author URI: https://github.com/Shuvoghosh7
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add custom checkout field
function custom_woocommerce_checkout_fields($fields)
{
    $fields['billing']['custom_field_one'] = array(
        'type' => 'select',
        'class' => array('form-row-wide'),
        'label' => __('Seleziona la tua età', 'theme_domain'),
        'required' => true,
        'options' => array(
            '' => 'Seleziona un opzione',
            '18' => 'Ho 18 anni',
            '18+' => 'Ho più di 18 anni'
        ),
    );

    // Add partial payment option
    $fields['billing']['partial_payment'] = array(
        'type' => 'checkbox',
        'class' => array('form-row-wide'),
        'label' => __('Abilita pagamento parziale', 'theme_domain'),
        'required' => false,
    );

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'custom_woocommerce_checkout_fields');


// Save custom checkout field
function save_custom_checkout_field($order_id)
{
    if (!empty($_POST['custom_field_one'])) {
        update_post_meta($order_id, 'custom_field_one', sanitize_text_field($_POST['custom_field_one']));
    }

    if (!empty($_POST['partial_payment'])) {
        update_post_meta($order_id, 'partial_payment', sanitize_text_field($_POST['partial_payment']));
    }
}
add_action('woocommerce_checkout_update_order_meta', 'save_custom_checkout_field');

// Handle partial payment logic
// Handle partial payment logic
function custom_partial_payment_logic($order_id)
{

    $partial_payment = get_post_meta($order_id, 'partial_payment', true);

    if ($partial_payment === '1') {
        $order = wc_get_order($order_id);
        $subtotal = $order->get_subtotal(); // Get the subtotal of the order
        $extra_charges = WC()->session->get('extra_price', 0); // Get extra charges from session

        $total = $subtotal + $extra_charges; 
        $partial_payment_percentage = get_option('partial_payment_percentage', 50) / 100;
        $partial_amount = $total * $partial_payment_percentage;
        // Update the total for partial payment
        $order->set_total($partial_amount); // Set the new total as partial payment
        $order->save();

        // Store due payment (remaining balance)
        $due_payment = $total - $partial_amount;
        update_post_meta($order_id, 'due_payment', $due_payment); // Save due payment in order meta
    }
}
add_action('woocommerce_checkout_order_processed', 'custom_partial_payment_logic');


// Enqueue custom checkout script
function custom_checkout_scripts()
{
    if (is_checkout()) {
        wp_enqueue_script('custom-checkout', plugin_dir_url(__FILE__) . 'js/custom-checkout.js', array('jquery'), '1.0.1', true);
        wp_enqueue_style('custom-checkout-style', plugin_dir_url(__FILE__) . 'css/style.css');
    }
}
add_action('wp_enqueue_scripts', 'custom_checkout_scripts');

// Add custom column to orders list
function add_wc_order_list_custom_column2($columns)
{
    $reordered_columns = array();

    foreach ($columns as $key => $column) {
        $reordered_columns[$key] = $column;

        if ($key === 'order_status') {
            // Insert after "Status" column
            $reordered_columns['custom_field_one'] = __('Età', 'theme_domain');
            $reordered_columns['partial_payment'] = __('Pagamento parziale', 'theme_domain');
            $reordered_columns['due_payment'] = __('Pagamento dovuto', 'theme_domain');
        }
    }
    return $reordered_columns;
}
add_filter('manage_woocommerce_page_wc-orders_columns', 'add_wc_order_list_custom_column2');

// Display custom column content
function display_wc_order_list_custom_column_content2($column, $order)
{
    if ($column == 'custom_field_one') {
        $order_id = $order->get_id();
        $custom_field_one = get_post_meta($order_id, 'custom_field_one', true);
        echo !empty($custom_field_one) ? esc_html($custom_field_one) : '<small>(<em>No Code</em>)</small>';
    }

    if ($column == 'partial_payment') {
        $order_id = $order->get_id();
        $partial_payment = get_post_meta($order_id, 'partial_payment', true);
        echo !empty($partial_payment) ? esc_html($partial_payment) : '<small>(<em>No</em>)</small>';
    }

    if ($column == 'due_payment') {
        $order_id = $order->get_id();
        $due_payment = get_post_meta($order_id, 'due_payment', true);
        echo !empty($due_payment) ? wc_price($due_payment) : '<small>(<em>Nessun pagamento dovuto</em>)</small>';
    }
}
add_action('manage_woocommerce_page_wc-orders_custom_column', 'display_wc_order_list_custom_column_content2', 10, 2);



// Display Total, Partial Payment, and Due Payment on the Order Page
function display_order_payment_info($order_id)
{
    $order = wc_get_order($order_id);

    // Get the extra charges and total
    $extra_charges = WC()->session->get('extra_price', 0); 
    $subtotal = $order->get_subtotal();
    $total = $subtotal + $extra_charges; 

    // Get partial payment and due payment
    $partial_payment = get_post_meta($order_id, 'partial_payment', true);
    $partial_payment_percentage = get_option('partial_payment_percentage', 50) / 100;
    $partial_amount = ($partial_payment === '1') ? $total * $partial_payment_percentage : 0;
    $due_payment = get_post_meta($order_id, 'due_payment', true); 

    // Add the information to the order page
    echo '<p><strong>Total:</strong> ' . wc_price($total) . '</p>';
    echo '<p><strong>Partial Payment:</strong> ' . wc_price($partial_amount) . '</p>';
    echo '<p><strong>Due Payment:</strong> ' . wc_price($due_payment) . '</p>';
}
add_action('woocommerce_thankyou', 'display_order_payment_info', 10, 1);
add_action('woocommerce_view_order', 'display_order_payment_info', 10, 1);


// Add custom setting field in WooCommerce General Settings
function custom_partial_payment_settings($settings)
{
    $settings[] = array(
        'title' => __('Impostazioni di pagamento parziale', 'theme_domain'),
        'type' => 'title',
        'id' => 'custom_partial_payment_settings'
    );

    $settings[] = array(
        'title' => __('Percentuale di pagamento parziale', 'theme_domain'),
        'desc' => __('Enter the percentage for partial payment (e.g., 50 for 50%)', 'theme_domain'),
        'id' => 'partial_payment_percentage',
        'type' => 'number',
        'css' => 'min-width:300px;',
        'custom_attributes' => array(
            'step' => 'any' // Allow any value without restrictions
        ),
        'default' => '50',
        'desc_tip' => true,
    );

    $settings[] = array(
        'type' => 'sectionend',
        'id' => 'custom_partial_payment_settings'
    );

    return $settings;
}
add_filter('woocommerce_general_settings', 'custom_partial_payment_settings');

// Save the custom setting
function save_custom_partial_payment_setting()
{
    if (isset($_POST['partial_payment_percentage'])) {
        update_option('partial_payment_percentage', sanitize_text_field($_POST['partial_payment_percentage']));
    }
}
add_action('woocommerce_update_options_general', 'save_custom_partial_payment_setting');

// Localize the partial payment percentage to use in JS
function custom_localize_script()
{
    if (is_checkout()) {
        $partial_payment_percentage = get_option('partial_payment_percentage', 50);
        wp_localize_script('custom-checkout', 'partialPaymentData', array(
            'percentage' => $partial_payment_percentage
        ));
    }
}
add_action('wp_enqueue_scripts', 'custom_localize_script');
