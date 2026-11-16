<?php
/**
 * Plugin Name: wp-agent
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// ─── Settings page ───────────────────────────────────

/** 
Adding a Settings Field to wp-agent
All settings code lives in wp-agent.php (standard WP Settings API, no abstractions). To add a new field, make 3 edits to that file:
1. Register the option — inside wpagent_register_settings()
Add a register_setting block:
register_setting(
    'wpagent_settings_group',           // same option group
    'wpagent_my_new_option',            // unique option name (DB key in wp_options)
    array(
        'type'              => 'string',
        'sanitize_callback' => 'sanitize_text_field',  // or custom function
        'default'           => '',
    )
);
2. Register the field — same function, after the existing add_settings_field (wp-agent.php:41-47)
add_settings_field(
    'wpagent_my_new_option_field',      // unique field ID (cosmetic)
    'My Label',                         // visible label
    'wpagent_my_new_option_field_html', // render callback name
    'wpagent-settings',                 // page slug (unchanged)
    'wpagent_main_section'              // section ID (unchanged)
);
3. Add the render callback — next to wpagent_my_string_field_html (wp-agent.php:51-54)
function wpagent_my_new_option_field_html() {
    $value = get_option( 'wpagent_my_new_option', '' );
    echo '<input type="text" name="wpagent_my_new_option" value="' . esc_attr( $value ) . '" class="regular-text" />';
}
Critical: the input's name attribute must exactly match the option name, or options.php won't save it.
That's it for saving + display
- The form (wpagent_settings_page, wp-agent.php:56-69) auto-renders all fields via do_settings_sections — no change needed.
- Saving/sanitizing is automatic (options.php + the registered sanitize_callback).
Optional: use the value at runtime
- PHP: get_option( 'wpagent_my_new_option', '' )
- To expose to JS: add a key to the wp_localize_script array (wp-agent.php:82-85) and read it as wpAgentData.<key> in wp-agent-script.js.
Variations by field type
Type	        Input markup in render callback
Text	        <input type="text" ...>
Textarea	    <textarea name="...">
Checkbox	    <input type="checkbox" value="1"> (handle unchecked = not posted)
Select	        <select name="..."><option>...
If you want a checkbox, note it needs a hidden input trick or a custom sanitize to persist the "off" state

*/

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

    register_setting(
        'wpagent_settings_group',           // same option group
        'wpagent_greet_message',            // unique option name (DB key in wp_options)
        array(
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',  // or custom function
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

    add_settings_field(
        'wpagent_greet_message_field',      // unique field ID (cosmetic)
        'Greet Message',                         // visible label
        'wpagent_greet_message_string_field_html', // render callback name
        'wpagent-settings',                 // page slug (unchanged)
        'wpagent_main_section'              // section ID (unchanged)
    );
}
add_action( 'admin_init', 'wpagent_register_settings' );

function wpagent_my_string_field_html() {
    $value = get_option( 'wpagent_my_string', '' );
    echo '<input type="text" name="wpagent_my_string" value="' . esc_attr( $value ) . '" class="regular-text" />';
}

function wpagent_greet_message_string_field_html() {
    $value = get_option( 'wpagent_greet_message', '' );
    echo '<input type="text" name="wpagent_greet_message" value="' . esc_attr( $value ) . '" class="regular-text" />';
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
        'wpAgentGreetMessage' => get_option( 'wpagent_greet_message', ''),
    ));
}
add_action('wp_enqueue_scripts', 'wpagent_import_scripts');