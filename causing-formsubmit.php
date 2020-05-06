<?php
	
/*
Plugin Name: Shortcode Generator
Plugin URI: http://causingdesignscom.kinsta.cloud/
Description: This plugin generates shortcodes for each post from the custom post type 'causing_forms
Author: John Mark Causing
Author URI:  http://causingdesignscom.kinsta.cloud/
*/




// START -  Admin column title and data
// ####################################
//
// hint: register custom admin column headers
// 'causing-forms' = custom post type name


	// START 
	// ####
	// This is to edit the title column data in custom post type 'causing_forms'
	// It will only change the column data of HTML Name.
add_action('admin_head-edit.php', 'cforms_register_custom_admin_titles');

function cforms_register_custom_admin_titles() {
	
	add_filter(
		'the_title',
		'cforms_custom_admin_titles',
		99,
		2
	);
}
function cforms_custom_admin_titles( $title, $post_id) {

	$html_name = get_post_meta( $post_id , 'cforms_name', true );
	$output = $html_name;
	return $output;

}

	// This is to edit the title column data in custom post type 'causing_forms'
	// It will only change the data of Subscriber Column.
	// ####
	// END


// this is column title header in the custom post type 'causing_forms'
// manage_edit-causing_forms_columns <-- 'causing_forms' is the slug of custom post type
add_filter('manage_edit-causing_forms_columns', 'cforms_subscriber_column_headers');

function cforms_subscriber_column_headers( $columns ) {
	// creating custom column header data
	// __( ) is for language so WP can detect if you are using a different language like Spanish
	$columns = array(
		'cb' => '<input type="checkbox">',
		'title' => __('HTML Name'),
		'shortcodes' => __('Shortcodes')

	);

	// returning new columns
	return $columns;
}


// This is the data column of custom post type 'causing_forms'
// You can put other data here like ID, email, shortcode, etc.
// 'causing-forms' = custom post type name
add_filter('manage_causing_forms_posts_custom_column', 'cforms_subscriber_column_data', 1, 2);

function cforms_subscriber_column_data( $column, $post_id ) {

	// setup our return text
	$output = '';

	switch ( $column ) {
			
				case 'shortcodes':
					// get the custom email data
					
					$shortcode_string = 'cforms the_post_id=' . $post_id . '';
					$my_shortcode =  '['.  $shortcode_string .']';
					$output .= $my_shortcode;

				break;				
            
	}

	// print the column data
	echo $output;
}

// ##################################
// END -  Admin column title and data



// START - Metaboxes / fields / html for custom post type called 'causing_forms'
// ############################################################################
 function cforms_add_subscriber_metaboxes( $post ) {

	add_meta_box(
		'just-a-metabox-id',
		'Form Details',
		'cforms_html',
		'causing_forms',
		'normal',
		'default'
	);
}

add_action('add_meta_boxes_causing_forms', 'cforms_add_subscriber_metaboxes');

// START -- HTML meta box function
// ###############################
function cforms_html() {

	global $post;
	$post_id = $post->ID;
	$shortcode_string = 'cforms the_post_id=' . $post_id . '';
	$my_shortcode =  '['.  $shortcode_string .']';

	wp_nonce_field( basename( __FILE__), 'causing_forms_nonce'); // insert hidden field to verify later when sumitting the post

	// storing variables from the form name and html fields
	$form_name = (!empty( get_post_meta($post_id, 'cforms_name', true))) ? get_post_meta($post_id, 'cforms_name', true) : '';
	$from_html = (!empty( get_post_meta($post_id, 'cforms_html', true))) ? get_post_meta($post_id, 'cforms_html', true) : '';

	?>

<!-- HTML source of this metabox -->		
<style>
	.slb-field-row {
		display: flex;
		flex-flow: row nowarp;
		felx: 1 1;
	}
 
	.slb-field-container {
		position: relative;
		flex: 1 1;
		margin-right: 1em;
	}

	.slb-field-container label span {
		color: red;
	}

	.slb-field-container ul {
		list-style: none;
		margin-top: 0;
	}

	.slb-field-container ul label {
		font-weight: normal;
}

	</style>
		<div class="slb-field-row">
			<div class="slb-field-container">
				<label for="">Form Name <span>*</span> </label>
				<input type="name" name="cforms_name" require="" class="widefat" value="<?php echo $form_name ?>" />
			</div>
		</div>
		</br>
		<div class="slb-field-row">
			<div class="slb-field-container">
				<label for="">HTML Text Box <span>*</span></label><br>
				<textarea name="cforms_html" id="w3mission" value="<?php echo htmlspecialchars($from_html); ?>" rows="4" cols="50"><?php
					
					
					if (!$from_html) {
						echo htmlspecialchars("<h1 style='color:red;'> This is the default HTML code!</h1>");
					}
					else { echo htmlspecialchars($from_html); } 
					
					?></textarea>
			</div>
			<div class="slb-field-container">
				<label for="">HTML Preview </label>
				<div class="html_preview"> 
					<?php echo wp_kses_post($from_html); ?>
				</div>
			</div>
		</div>

	<h1>This is your shortcode for this form: <b>  <?php echo $my_shortcode . ' </b>';  ?> </h1>

<?php

}
// ###############################
// END -- HTML meta box function


// ############################################################################
// END - Metaboxes / fields / html for custom post type called 'causing_forms'




// ###############################
// START -- save data from custom post type 'causing_forms'
// This is triggered when the post is saved

function cforms_save_causing_forms_meta( $post_id, $post ) {

	// Verify nonce
	if ( !isset($_POST['causing_forms_nonce']) || !wp_verify_nonce( $_POST['causing_forms_nonce'], basename( __FILE__) ) ) {
		return $post_id;
	}

	// get the post type object
	$post_type = get_post_type_object( $post->post_type );

	// check if the current user has perssion to edit the post
	if ( !current_user_can( $post_type->cap->edit_post, $post_id) ) {
		return $post_id;
	}

	// Get the posted data and sanitize it
 	$form_name = ( isset($_POST['cforms_name']) ) ? sanitize_text_field( $_POST['cforms_name']) : '';
	$from_html = ( isset($_POST['cforms_html']) ) ? $_POST['cforms_html']: '';

	// update / insert post meta
	update_post_meta($post_id, 'cforms_name', $form_name);
	update_post_meta($post_id, 'cforms_html', wp_kses_post($from_html));
}
add_action('save_post', 'cforms_save_causing_forms_meta', 10, 2); 


// END -- save data from custom post type 'causing_forms'
// ###############################



// ###############################
// START -- Generate shortcodes for each post from custom post type 'causing_forms'

// Load causing_forms_query() function after WP loads.
add_action( 'init', 'causing_forms_query' );


function causing_forms_query() {
 	add_shortcode('cforms', 'cforms_scode'); 
}

function cforms_scode( $attr, $content="") {

	// connect to database with post type 'causing_forms' to get the ID of each post. You can use post_title if you want.
	global $wpdb; 
	$post_type = 'causing_forms';
	$my_query = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'causing_forms' AND post_status IN ('draft', 'publish') ");

	if ( !is_null($my_query) ) { // check if query is not empty

		// Loop start 
		foreach( $my_query as $cforms_query) {		

			$cforms_id =  $cforms_query->ID; // post_id variable

			$args = shortcode_atts( array( 
				'the_post_id' => $cforms_id  // pass arguments post_id to shortcodes so that shortcodes can have parameters like id. ex: [cforms ID=12]
			), $attr );						// ID is dynamic so it will detect which post ID and content to look for.


			// get the value from the wp_postmeta table with the key of post ID
			$html_box = get_post_meta( $args['the_post_id'], 'cforms_html', true );
		
			// content of shortcodes
			// displays the ID and content of html_field from ACF (html codes)
			$output = '
				<div class="shortcode_class_id_'.  $args['the_post_id'] . '">
						'. $html_box .' 
				</div>
 			';	

			// return the conent of shortcodes according to post ID.
			return $output;	 

		}
		// Loop end
	}

} 

// END -- Generate shortcodes for each post from custom post type 'causing_forms'
// ###############################


// These codes were generated from CPT plugin so we no longer require to use that plugin
// Custom post type creation 'Causing Forms'
function cptui_register_my_cpts_causing_forms() {

	/**
	 * Post Type: Causing Forms.
	 */

	$labels = [
		"name" => __( "HTML Shortcodes", "twentytwenty" ),
		"singular_name" => __( "Causing Form", "twentytwenty" ),
	];

	$args = [
		"label" => __( "Causing Forms", "twentytwenty" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => true,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => [ "slug" => "causing_forms", "with_front" => true ],
		"query_var" => true,
		"menu_icon" => "dashicons-feedback",
		"supports" => false,
	];

	register_post_type( "causing_forms", $args );
}
add_action( 'init', 'cptui_register_my_cpts_causing_forms' );




/* ACF plugin. You can use this in the future if you want. This will link the ACF files so that you don't need the ACF plugin anymore */

// 4.1 
// Include ACF to this plugin
// Advanced Custom Field Settings
// Define path and URL to the ACF plugin.


/* include_once( plugin_dir_path( __FILE__ ) .'includes/acf/acf.php' );


// (Optional) Hide the ACF admin menu item.
add_filter('acf/settings/show_admin', 'my_acf_settings_show_admin');
function my_acf_settings_show_admin( $show_admin ) {
    return true;
}

 */
