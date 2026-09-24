<?php   
/**
 * Plugin Name: wp-agent
 */

    if ( ! defined( 'ABSPATH' ) ) {
	    exit; // Exit if accessed directly
    }

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
        ));
    }

    add_action('wp_enqueue_scripts', 'wpagent_import_scripts');
?>
