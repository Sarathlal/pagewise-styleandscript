<?php
/*
Plugin Name: Pagewise style & script
Plugin URI: http://sarathlal.com/wp-plugins/
Description: Customize pages by adding style and script on each page, post or even custom post types.
Version: 1.2
Text Domain: styleandscript
Author: sarathlal N
Author URI: http://sarathlal.com
* */


/**
 * The Class
 */
class PagewiseStyleandscript {

public function __construct() {
	add_action('add_meta_boxes', array($this, 'styleandscript_meta_box'));
	add_action('save_post', array($this, 'save_meta'));
	add_action('wp_head', array($this, 'page_style_to_wp_head'));
	add_action('wp_footer', array($this, 'page_script_to_wp_footer'));
	add_action('admin_init', array($this, 'styleandscript_admin_init'));
}

/**
 * Adds the meta box container.
 */
function styleandscript_meta_box() {
	$args = array('public'   => true );
	$post_types = get_post_types( $args );
	foreach ( $post_types  as $post_type ) {
		add_meta_box( '_page_style_meta', __( 'Styles', 'styleandscript' ), array($this,'page_style_meta_callback'), $post_type );
		add_meta_box( '_page_script_meta', __( 'Scripts', 'styleandscript' ), array($this,'page_script_meta_callback'), $post_type );
	}
}

/**
 * Outputs the content of the style meta box
 */
function page_style_meta_callback( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'styleandscript_nonce' );
    $styleandscript_stored_data = get_post_meta( $post->ID );
    $editable = ($this->check_user_permission() == true) ? "" : "readonly";

    echo '<p>';
		echo '<textarea name="page-style" id="page-style" style="width: 100%; height: 6em;" '.$editable.'>';
		if ( isset ( $styleandscript_stored_data['pagestyle'] ) ){
			 echo $styleandscript_stored_data['pagestyle'][0];
		}
		echo '</textarea>';
		echo '<label for="page-style" class="prfx-row-title">';
		if($this->check_user_permission() == true) {
			_e( "Add Style for this page", 'styleandscript' );
		} else {
			_e( "You don't have permission to write", 'styleandscript' );
		}
		echo '</label>';
		echo '</p>';
}

/**
 * Outputs the content of the script meta box
 */
function page_script_meta_callback( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'styleandscript_nonce' );
    $styleandscript_stored_data = get_post_meta( $post->ID );
    $editable = ($this->check_user_permission() == true) ? "" : "readonly";

    echo '<p>';
		echo '<textarea name="page-script" id="page-script" style="width: 100%; height: 6em;" '.$editable.'>';
		if ( isset ( $styleandscript_stored_data['pagescript'] ) ){
			echo $styleandscript_stored_data['pagescript'][0];
		}
		echo '</textarea>';
		echo '<label for="page-style" class="prfx-row-title">';
		if($this->check_user_permission() == true) {
			_e( "Add Scripts for this page", 'styleandscript' );
		} else {
			_e( "You don't have permission to write", 'styleandscript' );
		}
		echo '</label>';
		echo '</p>';
}

/**
 * Save the data
 */
function save_meta($post_id) {
    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'styleandscript_nonce' ] ) && wp_verify_nonce( $_POST[ 'styleandscript_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';

    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }

		// Checks for input and saves if needed
		if( isset( $_POST[ 'page-style' ] ) ) {
			update_post_meta( $post_id, 'pagestyle', $_POST[ 'page-style' ] );
		}

		if( isset( $_POST[ 'page-script' ] ) ) {
			update_post_meta( $post_id, 'pagescript', $_POST[ 'page-script' ] );
		}
 }

//Add the styles on head
function page_style_to_wp_head()
{
	global $post;
	// Retrieves the stored value from the database
	$pagestyle = get_post_meta( $post->ID, 'pagestyle', true );
	// Checks and displays the retrieved value
	if(!empty( $pagestyle )) {
		echo '<style>';
		echo $pagestyle;
		echo '</style>';
	}
}

//Add scripts on footer
function page_script_to_wp_footer(){

	global $post;
	// Retrieves the stored value from the database
	$pagescript = get_post_meta( $post->ID, 'pagescript', true );
	// Checks and displays the retrieved value
	if(!empty( $pagescript )) {
		echo '<script type="text/javascript">';
		echo $pagescript;
		echo '</script>';
	}
}

//Add settings in writing settings page
function styleandscript_admin_init(){
	register_setting(
		'writing',                 					// settings page
		'styleandscript_options'            		// option name
	);

	add_settings_field(
		'styleandscript_user_role',        			// id
		_e( 'Style & Script - User Role', 'styleandscript' ),      			// setting title
		array($this,'styleandscript_setting_input'),// display callback
		'writing',                 		   			// settings page
		'default'                          			// settings section
	);

}

// Display and selct the user role
function styleandscript_setting_input() {
	// get option 'user_role' value from the database
	$options = get_option( 'styleandscript_options' );
	$value = $options['user_role'];
	if(!$value) {
		$value = "administrator";
	}
	?>
	<select id='styleandscript_user_role' name='styleandscript_options[user_role]'>
		<?php wp_dropdown_roles($value); ?>
	</select>
	<?php
	_e( 'Member with this role can only add page wise style & script.', 'styleandscript' );
}

//Check current user role & give permission
public function check_user_permission() {
	global $current_user;
	$current_user = wp_get_current_user();
    $current_user_role = $current_user->roles ? $current_user->roles[0] : false;
	//Get plugin user role setting
 	$styleandscript_options = get_option( 'styleandscript_options' );
	$setting_value = $styleandscript_options['user_role'];
	if(($setting_value == $current_user_role) or (current_user_can('administrator'))){
		return true;
	} else {
		return false;
	}

}

}

$PagewiseStyleandscript = new PagewiseStyleandscript;
