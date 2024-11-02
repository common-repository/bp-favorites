<?php

$bp_favorites_admin = new BP_Favorites_Admin();

/**
 * BP_Favorites_Admin
 *
 * Loads plugin admin area
 *
 * @package BP Favorites
 * @subpackage Template Tags
 * @since BP Favorites (1.2-r2464)
 */
class BP_Favorites_Admin {

	function bp_favorites_admin () {
		// Attach the BP Favorites admin init action to the WordPress admin init action.
		add_action( 'admin_init',               array( $this, 'init' ) );

		// User profile edit/display actions
		add_action( 'edit_user_profile',        array( $this, 'user_profile_forums' ) );
		add_action( 'show_user_profile',        array( $this, 'user_profile_forums' ) );

		// User profile save actions
		add_action( 'personal_options_update',  array( $this, 'user_profile_update' ) );
		add_action( 'edit_user_profile_update', array( $this, 'user_profile_update' ) );

		// Add some general styling to the admin area
		add_action( 'admin_head',               array( $this, 'admin_head' ) );

		// Topic metabox actions
		add_action( 'admin_menu',               array( $this, 'metabox' ) );
		add_action( 'save_post',                array( $this, 'metabox_save' ) );
	}

	/**
	 * init()
	 *
	 * BP Favorites's dedicated admin init action
	 *
	 * @uses do_action
	 */
	function init () {
		do_action ( 'bp_favorites_admin_init' );
	}

	/**
	 * metabox ()
	 *
	 * Add the topic parent metabox
	 *
	 * @uses add_meta_box
	 */
	function metabox () {
		add_meta_box (
			'bp_favorites_metabox',
			__( 'Favorites', 'bp-favorites' ),
			'bp_favorites_metabox',
			BP_FAVORITES_POST_TYPE_ID,
			'normal'
		);

		do_action( 'bp_favorites_metabox' );
	}

	/**
	 * metabox_save ()
	 *
	 * Pass the topic post parent id for processing
	 *
	 * @param int $post_id
	 * @return int
	 */
	function metabox_save ( $post_id ) {
		if ( BP_FAVORITES_POST_TYPE_ID != get_post_type( $post_id ) )
			return $post_id;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		if ( !current_user_can( 'edit_post', $post_id ) )
			return $post_id;

		// A favorite has three pieces of key data, along with a few other
		// meta pieces we will use here just to not hit the DB extra times
		$bp_favorites_network_id = $_POST['bp_favorites_network_id'];
		$bp_favorites_site_id    = $_POST['bp_favorites_site_id'];
		$bp_favorites_post_id    = $_POST['bp_favorites_post_id'];

		switch_to_blog( $bp_favorites_site_id );
		
		$args = array(
			'ID'           => $post_id,
			'post_title'   => get_post_field( $bp_favorites_post_id, 'post_title' ),
			'post_content' => get_post_field( $bp_favorites_post_id, 'post_content' )
		);

		restore_current_blog();

		switch_to_blog( BP_FAVORITES_ROOT_BLOG );

		update_post_meta( $post_id, 'bp_favorites_network_id', $bp_favorites_network_id );
		update_post_meta( $post_id, 'bp_favorites_site_id',    $bp_favorites_site_id );
		update_post_meta( $post_id, 'bp_favorites_post_id',    $bp_favorites_post_id );

		wp_update_post( $args );

		restore_current_blog();

		do_action( 'bp_favorites_metabox_save' );

		return $post_id;
	}

	/**
	 * admin_head ()
	 *
	 * Add some general styling to the admin area
	 */
	function admin_head () {
		// Icons for top level admin menus
		$favorites_icon_url	= BP_FAVORITES_URL . '/images/icon-favorites.png';

		// Top level menu classes
		$favorites_class    = sanitize_html_class( BP_FAVORITES_POST_TYPE_ID );
?>
		<style type="text/css" media="screen">
		/*<![CDATA[*/
			#menu-posts-<?php echo $favorites_class; ?> .wp-menu-image {
				background: url(<?php echo $favorites_icon_url; ?>) no-repeat 0px -32px;
			}
			#menu-posts-<?php echo $favorites_class; ?>:hover .wp-menu-image,
			#menu-posts-<?php echo $favorites_class; ?>.wp-has-current-submenu .wp-menu-image {
				background: url(<?php echo $favorites_icon_url; ?>) no-repeat 0px 0px;
			}
		/*]]>*/
		</style>
<?php
		// Add extra actions to BP Favorites admin header area
		do_action( 'bp_favorites_admin_head' );
	}

	/**
	 * user_profile_update ()
	 *
	 * Responsible for showing additional profile options and settings
	 *
	 * @todo Everything
	 */
	function user_profile_update ( $user_id ) {
		if ( !bp_favorites_has_access() )
			return false;

		// Add extra actions to BP Favorites profile update
		do_action( 'bp_favorites_user_profile_update' );
	}

	/**
	 * user_profile_favorites ()
	 *
	 * Responsible for saving additional profile options and settings
	 *
	 * @todo Everything
	 */
	function user_profile_favorites ( $profileuser ) {

		if ( !bp_favorites_has_access() )
			return false;

?>
		<h3><?php _e( 'Favorites', 'bp-favorites' ); ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e( 'Favorites', 'bp-favorites' ); ?></th>
				<td>

				</td>
			</tr>
		</table>
<?php

		// Add extra actions to BP Favorites profile update
		do_action( 'bp_favorites_user_profile_favorites' );
	}
}

/**
 * bp_favorites_get_sites_from_network ()
 * 
 * Retrieve a list of the sites within the current network
 * 
 * @global object $wpdb
 * @return array
 */
function bp_favorites_get_sites_from_network () {
	global $wpdb;

	$query = "SELECT * FROM {$wpdb->blogs} WHERE site_id = '{$wpdb->siteid}' ORDER BY {$wpdb->blogs}.blog_id ";

	$sites = $wpdb->get_results( $query, ARRAY_A );

	return $sites;
}

/**
 * bp_favorites_admin_separator ()
 *
 * Forces a separator between BP Favorites top level menus, and WordPress content menus
 *
 * @package BP Favorites
 * @subpackage Template Tags
 * @since BP Favorites (1.2-r2464)
 *
 * @todo A better job at rearranging and separating top level menus
 * @global array $menu
 */
function bp_favorites_admin_separator () {
	global $menu;

	$menu[24] = $menu[25];
	$menu[25] = array( '', 'read', 'separator1', '', 'wp-menu-separator' );
}
add_action( 'admin_menu', 'bp_favorites_admin_separator' );

/**
 * bp_favorites_metabox ()
 *
 * The metabox that holds all of the additional topic information
 *
 * @package BP Favorites
 * @subpackage Template Tags
 * @since BP Favorites (1.2-r2464)
 *
 * @todo Alot ;)
 * @global object $post
 */
function bp_favorites_metabox () {
	global $post;

	// Load the sites on this network
	$sites          = bp_favorites_get_sites_from_network();

	switch_to_blog( BP_FAVORITES_ROOT_BLOG );

	$fav_network_id = get_post_meta( $post->ID, 'bp_favorites_network_id', true );
	$fav_site_id    = get_post_meta( $post->ID, 'bp_favorites_site_id', true );
	$fav_post_id    = get_post_meta( $post->ID, 'bp_favorites_post_id', true );

	restore_current_blog();

	switch_to_blog( $fav_site_id );

	$network_name = get_site_option( 'site_name' );
	$site_name    = get_blog_option( $fav_site_id, 'blogname' );
	$post_title   = get_the_title( $fav_post_id );

	restore_current_blog();
?>
	<h4><?php echo $network_name; ?></h4>
	<p>
		<label class="screen-reader-text" for="bp_favorites_network_id"><?php _e( 'Network', 'bp-favorites' ) ?></label>
		<span><?php _e( 'Network ID: ', 'bp-favorites' ); ?></span><input name="bp_favorites_network_id" type="text" size="4" id="bp_favorites_network_id" value="<?php echo $fav_network_id; ?>" />
	</p>

	<h4><?php echo get_blog_option( $fav_site_id, 'blogname' ); ?></h4>
	<p>
		<span><?php _e( 'Site ID: ', 'bp-favorites' ); ?></span><select name="bp_favorites_site_id">
<?php foreach( (array) $sites as $site ) { ?>
			<option value="<?php echo $site['blog_id'] ?>"<?php selected( $fav_site_id,  $site['blog_id'] ); ?>><?php echo $site['blog_id'] . ': ' . esc_url( get_blog_option( $site['blog_id'], 'home' ) ) ?></option>
<?php } ?>
		</select>
	</p>

	<h4><?php echo $post_title; ?></h4>
	<p>
		<label class="screen-reader-text" for="bp_favorites_post_id"><?php _e( 'Post', 'bp-favorites' ) ?></label>
		<span><?php _e( 'Post ID: ', 'bp-favorites' ); ?></span><input name="bp_favorites_post_id" type="text" size="4" id="bp_favorites_post_id" value="<?php echo $fav_post_id; ?>" />
	</p>

<?php

	

	do_action( 'bp_favorites_topic_metabox' );
}

?>
