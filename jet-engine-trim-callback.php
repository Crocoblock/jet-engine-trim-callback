<?php
/**
 * Plugin Name: JetEngine - Trim string callback
 * Plugin URI:  #
 * Description: Adds new callback to Dynamic Field widget, which allows to return truncated string with specified width.
 * Version:     1.0.2
 * Author:      Crocoblock
 * Author URI:  https://crocoblock.com/
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

add_action( 'plugins_loaded', 'jet_engine_trim_cb_init' );
add_filter( 'jet-engine/listings/allowed-callbacks', 'jet_engine_trim_add_callback' );
add_filter( 'jet-engine/listing/dynamic-field/callback-args', 'jet_engine_trim_callback_args', 10, 3 );
add_filter( 'jet-engine/listings/allowed-callbacks-args', 'jet_engine_trim_callback_controls' );

function jet_engine_trim_cb_init() {

	define( 'JET_TRIM_CB_VERSION', '1.0.2' );

	define( 'JET_TRIM_CB__FILE__', __FILE__ );
	define( 'JET_TRIM_CB_PLUGIN_BASE', plugin_basename( JET_TRIM_CB__FILE__ ) );
	define( 'JET_TRIM_CB_PATH', plugin_dir_path( JET_TRIM_CB__FILE__ ) );

	add_action( 'init', function () {

		if ( ! function_exists( 'jet_engine' ) ) {
			return;
		}

		$pathinfo = pathinfo( JET_TRIM_CB_PLUGIN_BASE );

		jet_engine()->modules->updater->register_plugin( array(
			'slug'    => $pathinfo['filename'],
			'file'    => JET_TRIM_CB_PLUGIN_BASE,
			'version' => JET_TRIM_CB_VERSION,
		) );
	}, 12 );

}

function jet_engine_trim_callback_controls( $args ) {

	$args['jet_trim_cb_type'] = array(
		'label'   => esc_html__( 'Trimmed Type', 'jet-engine' ),
		'type'    => 'select',
		'default' => 'chars',
		'options' => array(
			'chars' => esc_html__( 'Chars', 'jet-engine' ),
			'words' => esc_html__( 'Words', 'jet-engine' ),
		),
		'condition' => array(
			'dynamic_field_filter' => 'yes',
			'filter_callback'      => array( 'jet_engine_trim_string_callback' ),
		),
	);

	$args['jet_trim_cb_length'] = array(
		'label'       => esc_html__( 'String length', 'jet-engine' ),
		'type'        => 'text',
		'label_block' => true,
		'description' => esc_html__( 'The length of the desired trim', 'jet-engine' ),
		'default'     => '20',
		'condition'   => array(
			'dynamic_field_filter' => 'yes',
			'filter_callback'      => array( 'jet_engine_trim_string_callback' ),
		),
	);

	return $args;
}

function jet_engine_trim_add_callback( $callbacks ) {
	$callbacks['jet_engine_trim_string_callback'] = 'Trim string by chars or words';
	return $callbacks;
}

function jet_engine_trim_string_callback( $field_value = null, $length = 20, $type = 'chars' ) {

	if ( 'words' === $type ) {
		return wp_trim_words( $field_value, absint( $length ), '...' );
	}

	$field_value = wp_strip_all_tags( $field_value );

	if ( function_exists( 'mb_strimwidth' ) ) {
		return mb_strimwidth( $field_value, 0, absint( $length ), '...' );
	} else {

		$str_length = strlen( $field_value );

		if ( $str_length <= $length ) {
			return $field_value;
		} else {
			return substr( $field_value, 0, $length ) . '...';
		}

	}

}

function jet_engine_trim_callback_args( $args, $callback, $settings = array() ) {

	if ( 'jet_engine_trim_string_callback' === $callback ) {
		$args[] = isset( $settings['jet_trim_cb_length'] ) ? $settings['jet_trim_cb_length'] : 20;
		$args[] = isset( $settings['jet_trim_cb_type'] ) ? $settings['jet_trim_cb_type'] : 'chars';
	}

	return $args;
}
