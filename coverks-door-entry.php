<?php
/*
Plugin Name: Coverks Door Entry
Plugin URI:  http://coverks.no/
Description: Allows Coverks members to open door.
Version:     1.0
Author:      Scott Basgaard
Author URI:  http://scottbasgaard.com/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: coverks-door-entry
*/

// Slack Helper
require_once( 'inc/slack.php' );

add_action( 'init', function() {

	add_action( 'coverks_app_home', 'coverks_door_unlock' );
	add_action( 'coverks_app_home_additional_buttons', 'coverks_light_check' );

} );

add_action( 'rest_api_init', 'myplugin_register_routes' );


function myplugin_register_routes() {

	register_rest_route( 'coverks/v1', 'openclose', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'coverks_door_openclose',
		'permission_callback' => function () {
			return current_user_can( 'edit_posts' );
		}
	) );

	register_rest_route( 'coverks/v1', 'open/(?P<id>\d+)', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'coverks_door_open',
		'permission_callback' => function () {
			return current_user_can( 'edit_posts' );
		}
	) );

	register_rest_route( 'coverks/v1', 'close/(?P<id>\d+)', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'coverks_door_close',
		'permission_callback' => function () {
			return current_user_can( 'edit_posts' );
		}
	) );

	register_rest_route( 'coverks/v1', 'lighton/(?P<id>\d+)', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'coverks_light_on',
		'permission_callback' => function () {
			return current_user_can( 'edit_posts' );
		}
	) );

	register_rest_route( 'coverks/v1', 'lightoff/(?P<id>\d+)', array(
		'methods'  => WP_REST_Server::READABLE,
		'callback' => 'coverks_light_off',
		'permission_callback' => function () {
			return current_user_can( 'edit_posts' );
		}
	) );

}

function coverks_door_openclose( WP_REST_Request $request ) {

	$current_user = wp_get_current_user();

	if ( isset( $_GET['latitude'] ) && isset( $_GET['longitude'] ) && $_GET['latitude'] != 0 && $_GET['longitude'] != 0 ) {

		$latitude = $_GET['latitude'];
		$longitude = $_GET['longitude'];

		update_user_meta( $current_user->ID, '_coverks_user_coordinates', array( 'latitude' => $latitude, 'longitude' => $longitude ) );

	}

	$url = "http://192.168.1.150:8000/open/key";
	$api_response = wp_remote_get( esc_url( $url ), array( 'timeout' => 300 ) );

	if ( is_wp_error( $response ) ) {
		 return "{$api_response->get_error_code()}: {$api_response->get_error_message()}";
	}

	$user_name    = $current_user->display_name;
	$message      = '*' . $user_name . '* opened the door!';

	$coordinates = get_user_meta( get_current_user_id(), '_coverks_user_coordinates', true );

	if ( $coordinates ) {

		$latitude = $coordinates['latitude'];
		$longitude = $coordinates['longitude'];

		$address = getAddress( $latitude, $longitude );
		$address = $address ? $address: '';

		if ( ! empty( $address ) ) {
			$message .= ' From ' . $address;
		}

	}

	coverks_post_to_slack( $message ,'#door', 'DoorBot', ':door:' );

	if ( function_exists( 'bp_activity_add' ) ) {

		$bp = buddypress();
		$user_id = get_current_user_id();
		$userlink = bp_core_get_userlink( $user_id );

		bp_activity_add( array(
		'user_id'   => $user_id,
		'action'    => sprintf( __( '%s has opened the doors.', 'buddypress' ), $userlink ),
		'content'   => '',
		'component' => 'coverks',
		'type'      => 'open_door'
		) );
	}

	return true;
}


function coverks_door_open( WP_REST_Request $request ) {

	$id = $request['id'];

	$url = "http://192.168.1.150:8000/unlock/" . $id . "/key";
	$api_response = wp_remote_get( esc_url( $url ), array( 'timeout' => 300 ) );

	if ( is_wp_error( $response ) ) {
		 return "{$api_response->get_error_code()}: {$api_response->get_error_message()}";
	}

	$current_user = wp_get_current_user();
	$user_name    = $current_user->display_name;
	$message      = '*' . $user_name . '* opened the door!';
	coverks_post_to_slack( $message ,'#door', 'DoorBot', ':door:' );

	return true;
}

function coverks_door_close( WP_REST_Request $request ) {

	$id = $request['id'];

	$url = "http://192.168.1.150:8000/lock/" . $id . "/key";
	$api_response = wp_remote_get( esc_url( $url ),  array( 'timeout' => 300 ) );

	if ( is_wp_error( $response ) ) {
		 return "{$api_response->get_error_code()}: {$api_response->get_error_message()}";
	}

	// $current_user = wp_get_current_user();
	// $user_name    = $current_user->display_name;
	// $message      = '*' . $user_name . '* locked the door!';
	// coverks_post_to_slack( $message ,'#door', 'DoorBot', ':door:' );

	return true;
}

function coverks_light_on( WP_REST_Request $request ) {
	if ( ! $request['id'] ) {
		return false;
	}

	$response = coverks_telldus_api_light_on($request['id']);

	return $response;
}

function coverks_light_off( WP_REST_Request $request ) {
	if ( ! $request['id'] ) {
		return false;
	}

	$response = coverks_telldus_api_light_off($request['id']);

	return $response;
}

function coverks_light_check() {

	if ( current_user_can( 'edit_posts' ) ) {

		echo '<h2>Lights</h2>';
		echo '<h3>Working Space</h3>';
		echo '<a href="#" target="_self" class="button button-block button-primary no-ajax coverks-light-on" data-coverks-light-id="1794783">On</a>';
		echo '<a href="#" target="_self" class="button button-block button-primary no-ajax coverks-light-off" data-coverks-light-id="1794783">Off</a>';
		echo '<hr />';



	}

}

function coverks_door_unlock() {

	if ( current_user_can( 'edit_posts' ) ) {

		if ( current_user_can( 'manage_options' ) ) {

			// print_r( get_user_meta( get_current_user_id(), '_coverks_user_coordinates', true ) );

			echo '<h2>Admin</h2>';
			echo '<h3>Building Door</h3>';
			echo '<a href="#" target="_self" class="button button-block button-primary no-ajax coverks-door-unlock" data-coverks-door-id="2">Open</a>';
			echo '<a href="#" target="_self" class="button button-block button-primary no-ajax coverks-door-lock" data-coverks-door-id="2">Lock</a>';

			echo '<h3>Coverks Door</h3>';
			echo '<a href="#" target="_self" class="button button-block button-primary no-ajax coverks-door-unlock" data-coverks-door-id="1">Open</a>';
			echo '<a href="#" target="_self" class="button button-block button-primary no-ajax coverks-door-lock" data-coverks-door-id="1">Lock</a>';

			echo '<hr />';
			echo '<h2>Member</h2>';

		}

		echo '<p>This will open both doors. <b>Doors will automatically lock after 3 seconds.</b></p>';
		echo '<a href="#" target="_self" class="button button-block button-primary no-ajax coverks-door-open" data-coverks-latitude="0" data-coverks-longitude="0">Open Doors</a>';
		echo '<hr />';


		do_action( 'coverks_app_home_additional_buttons' );

		echo '<p>';
		echo '<a href="' . wp_logout_url( home_url() ) . '" target="_self" class="button button-block no-ajax">Log Out</a>';
		echo '</p>';

	} else {

		echo '<a href="' . wp_login_url( home_url() ) . '" target="_self" class="button button-block button-primary no-ajax">Log In</a>';

	}

	echo '<p class="help">Need help? Ring <a href="tel:95281216">952 81 216</a></p>';

}

add_action( 'wp_enqueue_scripts', function() {

	wp_enqueue_script( 'coverks-door-entry', plugins_url( '/script.js', __FILE__ ), array('jquery'), '1.0.1', true );

	wp_localize_script( 'coverks-door-entry', 'wpApiSettings', array(
		'root' => esc_url_raw( rest_url() ),
		'nonce' => wp_create_nonce( 'wp_rest' )
	) );

} );

function getAddress( $latitude, $longitude ) {

	if ( ! empty( $latitude ) && !empty( $longitude ) ){

		$geocodeFromLatLong = file_get_contents( 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($latitude).','.trim($longitude).'&sensor=false' );
		$output = json_decode($geocodeFromLatLong);
		$status = $output->status;

		$address = ($status=="OK")?$output->results[1]->formatted_address:'';

		if( ! empty( $address ) ){
			return $address;
		} else {
			return false;
		}
		} else {
			return false;
	}

}
