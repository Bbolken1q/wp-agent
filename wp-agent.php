<?php
/**
 * Plugin Name: wp-agent
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// ─── Settings page ───────────────────────────────────

function wpagent_add_admin_menu() {
    add_options_page(
        'WP Agent Settings',
        'WP Agent',
        'manage_options',
        'wpagent-settings',
        'wpagent_settings_page'
    );
}
add_action( 'admin_menu', 'wpagent_add_admin_menu' );

function wpagent_register_settings() {
    register_setting(
        'wpagent_settings_group',
        'wpagent_my_string',
        array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        )
    );

    add_settings_section(
        'wpagent_main_section',
        'Main Settings',
        null,
        'wpagent-settings'
    );

    add_settings_field(
        'wpagent_my_string_field',
        'Api server address',
        'wpagent_my_string_field_html',
        'wpagent-settings',
        'wpagent_main_section'
    );
}
add_action( 'admin_init', 'wpagent_register_settings' );

function wpagent_my_string_field_html() {
    $value = get_option( 'wpagent_my_string', '' );
    echo '<input type="text" name="wpagent_my_string" value="' . esc_attr( $value ) . '" class="regular-text" />';
}

function wpagent_settings_page() {
    ?>
    <div class="wrap">
        <h1>WP Agent Settings</h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'wpagent_settings_group' );
            do_settings_sections( 'wpagent-settings' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// setup
function wpagent_import_scripts() {
    wp_register_style('wpagent_style',
        plugin_dir_url(__FILE__).'assets/wp-agent-style.css');
    wp_enqueue_style( 'wpagent_style' );

    wp_register_script('wpagent_script',
        plugin_dir_url(__FILE__) . '/wp-agent-script.js',
        array(), '1.0', true );
    wp_enqueue_script( 'wpagent_script' );

    wp_localize_script( 'wpagent_script', 'wpAgentData', array(
        'iconUrl' => plugin_dir_url(__FILE__) . 'assets/ai-icon.svg',
        'wpAgentAdress' => get_option( 'wpagent_my_string', '' ),
    ));
}
add_action('wp_enqueue_scripts', 'wpagent_import_scripts');