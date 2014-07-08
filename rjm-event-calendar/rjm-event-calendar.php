<?php
/*
Plugin Name: RJM Event Calendar
Plugin URI:  https://github.com/rickmakkee/RJM-Wordpress-Event-Calendar
Description: Simple event calendar
Author: Rick Makkee
Version: 0.1
Author URI: http://rickmakkee.nl
License: MIT License
*/

// -----------------------------------------------------
// 1. Custom Post Type Registration (rjmevent)
// -----------------------------------------------------
function rjm_create_event_postype() {
 
	$labels = array(
    'name' => _x('Events', 'rjmevent'),
    'singular_name' => _x('Events', 'rjmevent'),
    'add_new' => _x('New event', 'rjmevent'),
    'add_new_item' => __('Add new event'),
    'edit_item' => __('Edit event'),
    'new_item' => __('New event'),
    'view_item' => __('Show'),
    'search_items' => __('Search events'),
    'not_found' =>  __('No events were found'),
    'not_found_in_trash' => __('No events were found in trash'),
    'parent_item_colon' => '',
	);
	 
	$args = array(
    'label' => __('Events'),
    'labels' => $labels,
    'public' => true,
    'can_export' => true,
    'show_ui' => true,
    '_builtin' => false,
    'capability_type' => 'post',
    'hierarchical' => false,
    'rewrite' => array( "slug" => "events" ),
    'supports'=> array('title', 'editor') ,
    'show_in_nav_menus' => true,
    'taxonomies' => array( 'rjmevent_cat' )
	);
	 
	register_post_type( 'rjmevent', $args); 
}

add_action( 'init', 'rjm_create_event_postype' );

// -----------------------------------------------------
// 2. Change columns to show title, categories, date, time and description
// ----------------------------------------------------- 
function rjm_events_edit_columns($columns) {
 
	$columns = array(
    "cb" => "<input type=\"checkbox\" />",
    "title" => "Title",
    "col_ev_date" => "Date",
    "col_ev_times" => "Time",
    "col_ev_desc" => "Description",
  );
	return $columns;
}
	 
function rjm_events_custom_columns($column) {

	global $post;
	$custom = get_post_custom();
	switch ($column)
	{
  	case "col_ev_date":
	    // Laat begin en eind datum zien
	    $startd = $custom["events_startdate"][0];
	    $endd = $custom["events_enddate"][0];
	    $startdate = date("d-m-Y", $startd);
	    $enddate = date("d-m-Y", $endd);
	    
	    if($startdate == $enddate){
  	    echo $startdate;
	    }else{
	      echo $startdate . '<br /><em>' . $enddate . '</em>';
	    }
  	break;
  	case "col_ev_times":
	    // Laat begin en eind tijd zien
	    $startt = $custom["events_startdate"][0];
	    $endt = $custom["events_enddate"][0];
	    $time_format = get_option('time_format');
	    $starttime = date($time_format, $startt);
	    $endtime = date($time_format, $endt);
	    echo $starttime . ' - ' .$endtime;
  	break;
  	case "col_ev_desc";
  	  the_excerpt();
  	break;
	}
}

add_filter ("manage_edit-rjmevent_columns", "rjm_events_edit_columns");
add_action ("manage_posts_custom_column", "rjm_events_custom_columns");

// -----------------------------------------------------
// 3. Add extra content box for date and time
// ----------------------------------------------------- 
function rjm_events_create() {
  add_meta_box('rjm_events_meta', 'Date and time', 'rjm_events_meta', 'rjmevent');
}

add_action( 'admin_init', 'rjm_events_create' );
 
function rjm_events_meta () {

	global $post;
	$custom = get_post_custom($post->ID);
	$meta_sd = $custom["events_startdate"][0];
	$meta_ed = $custom["events_enddate"][0];
	$meta_st = $meta_sd;
	$meta_et = $meta_ed;
	 
	// Grab Wordpress time format
	$date_format = get_option('date_format');
	$time_format = get_option('time_format');
	 
	// Populate today if date is empty, 00:00 for time
	if ($meta_sd == null) { $meta_sd = time(); $meta_ed = $meta_sd; $meta_st = 0; $meta_et = 0;}
	
	// Convert to time format
	$clean_sd = date("Y-m-d", $meta_sd);
	$clean_ed = date("Y-m-d", $meta_ed);
	$clean_st = date($time_format, $meta_st);
	$clean_et = date($time_format, $meta_et);
	 
	// Security
	echo '<input type="hidden" name="events-nonce" id="events-nonce" value="' .
	wp_create_nonce( 'events-nonce' ) . '" />';
	 
	// Output
	?>
	<div class="tf-meta">
		<table cellspacing="0">
			<tr>
				<td width="100"><label>Start date</label></td>
				<td><input name="events_startdate" class="tfdate" value="<?php echo $clean_sd; ?>" /></td>
			</tr>
			<tr>
				<td><label>Start time</label></td>
				<td><input name="events_starttime" value="<?php echo $clean_st; ?>" /><em style="margin-left:10px">Use 24 hour time format</em></td>
			</tr>
			<tr>
				<td><label>End date</label></td>
				<td><input name="events_enddate" class="tfdate" value="<?php echo $clean_ed; ?>" /></td>
			</tr>
			<tr>
				<td><label>End time</label></td>
				<td><input name="events_endtime" value="<?php echo $clean_et; ?>" /><em style="margin-left:10px">Use 24 hour time format</em></td>
			</tr>
		</table>
	</div>
	<?php
}

// -----------------------------------------------------
// 4. Save data
// ----------------------------------------------------- 
function rjm_save_events(){
	
	global $post;
	ob_start();
	
	// Check nonce security
	if ( !wp_verify_nonce( $_POST['events-nonce'], 'events-nonce' )) {
	    return $post->ID;
	}
	
	// Check if user can edit this post
	if ( !current_user_can( 'edit_post', $post->ID )){
	    return $post->ID;
	}
	
	// Check if start date is send
	if(!isset($_POST["events_startdate"])){
		return $post;
	}
	
	// Convert start date and time to unix timestamp and update the post and meta data
	$data_start = strtotime ( $_POST["events_startdate"] . $_POST["events_starttime"] );
	update_post_meta($post->ID, "events_startdate", $data_start );
	
	// Check is end date is send
	if(!isset($_POST["events_enddate"])){
		return $post;
	}
	
	// Convert end date and time to unix timestamp and update the post and meta data
	$data_eind = strtotime ( $_POST["events_enddate"] . $_POST["events_endtime"]);
	update_post_meta($post->ID, "events_enddate", $data_eind );
	
}

add_action ('save_post', 'rjm_save_events');

// -----------------------------------------------------
// 5. Include jQuery datepicker
// -----------------------------------------------------
function rjm_events_styles() {

  global $post_type;
  if( 'rjmevent' != $post_type )
      return;
      
  // Include jQuery UI css for datepicker styles
  wp_enqueue_style('ui-datepicker', plugins_url( '/css/jquery-ui-1.8.9.custom.css', __FILE__ ) );
  
}
 
function rjm_events_scripts() {

  global $post_type;
  if( 'rjmevent' != $post_type )
      return;
  
  // Include jQuery UI and jQuery UI datepicker Javascript
  wp_enqueue_script('jquery-ui', plugins_url( '/js/jquery-ui-1.8.9.custom.min.js', __FILE__ ), array('jquery'));
  wp_enqueue_script('ui-datepicker', plugins_url( '/js/jquery.ui.datepicker.js', __FILE__ ));
  
  // Include custom Javascript and give the plugins url to the JS file
  wp_register_script( 'customJS_script', plugins_url( '/js/scripts.js', __FILE__) );
  $rjm_pluginUrl = plugins_url();
  wp_localize_script( 'customJS_script', 'rjmTemplateUrl', $rjm_pluginUrl );
  wp_enqueue_script( 'customJS_script' );
  
}
 
add_action( 'admin_print_styles-post.php', 'rjm_events_styles', 1000 );
add_action( 'admin_print_styles-post-new.php', 'rjm_events_styles', 1000 );
 
add_action( 'admin_print_scripts-post.php', 'rjm_events_scripts', 1000 );
add_action( 'admin_print_scripts-post-new.php', 'rjm_events_scripts', 1000 );

