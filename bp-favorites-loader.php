<?php
/*
Plugin Name: BP Favorites
Plugin URI: http://buddypress.org
Description: Favorites are a way for you to keep track of your favorite blog posts in a blogging network.
Author: John James Jacoby
Version: 1.0-bleeding
Site Wide Only: true
*/

/**
 * Set the version early so other plugins have an inexpensive
 * way to check if BP Favorites is already loaded.
 *
 * Note: Loaded does NOT mean initialized
 */
define ( 'BP_FAVORITES_VERSION', '1.0-bleeding' );

// Attach the BP Favorites tag registration to our own trusted init.
//add_action ( 'bp_favorites_init',        array( $this, 'register_taxonomies' ) );

// Include BuddyPress specific code
//add_action ( 'bp_include',               array( $this, 'includes' ) );

/** Could you imagine if we had this when we were 12?! */
$bp_favorites = new BP_Favorites();

/**
 * BP_Favorites
 *
 * tap tap tap... Is this thing on?
 *
 * @package BP Favorites
 * @subpackage Loader
 * @since BP Favorites (1.0)
 *
 */
class BP_Favorites {

	/**
	 * Arm the lasers
	 */
	function bp_favorites () {
		// Attach the BP Favorites loaded action to the WordPress plugins_loaded action.
		add_action ( 'plugins_loaded',           array( $this, 'loaded' ) );

		// Attach the BP Favorites initilization to the WordPress init action.
		add_action ( 'init',                     array( $this, 'init' ) );

		// Attach the BP Favorites constants to our own trusted action.
		add_action ( 'bp_favorites_loaded',      array( $this, 'constants' ) );

		// Attach the BP Favorites includes to our own trusted action.
		add_action ( 'bp_favorites_loaded',      array( $this, 'includes' ) );

		// Attach the BP Favorites includes to our own trusted action.
		add_action ( 'bp_include',               array( $this, 'bp_includes' ) );

		// Attach the BP Favorites text domain loader to our own trusted action
		add_action ( 'bp_favorites_init',        array( $this, 'textdomain' ) );

		// Attach the BP Favorites post type registration to our own trusted init.
		add_action ( 'bp_favorites_init',        array( $this, 'register_post_types' ) );

		// Setup BuddyPress Globals
		add_action ( 'bp_setup_globals',         array( $this, 'setup_globals' ) );

		// Setup BuddyPress Navigation
		add_action ( 'bp_setup_nav',             array( $this, 'setup_nav' ) );

		// What to do with BP Favorites is activated
		register_activation_hook   ( __FILE__,   array( $this, 'activation' ) );

		// What to do with BP Favorites is deactivated
		register_deactivation_hook ( __FILE__,   array( $this, 'deactivation' ) );
	}

	/**
	 * constants ()
	 *
	 * Default component constants that can be overridden or filtered
	 */
	function constants () {

		// Let plugins sneak in and predefine constants
		do_action( 'bp_favorites_constants_pre' );

		define ( 'BP_FAVORITES_DB_VERSION', '2000' );

		// Set the blog_id where favorites will be stored
		if ( !defined( 'BP_FAVORITES_ROOT_BLOG' ) )
			define( 'BP_FAVORITES_ROOT_BLOG', apply_filters( 'bp_favorites_root_blog', BP_ROOT_BLOG ? BP_ROOT_BLOG : 1 ) );

		// Turn debugging on/off
		if ( !defined( 'BP_FAVORITES_DEBUG' ) )
			define( 'BP_FAVORITES_DEBUG', WP_DEBUG );

		// The default favorites post type ID
		if ( !defined( 'BP_FAVORITES_POST_TYPE_ID' ) )
			define( 'BP_FAVORITES_POST_TYPE_ID', apply_filters( 'bp_favorites_post_type_id', 'bp_favorites' ) );

		// The default tag ID
		if ( !defined( 'BP_FAVORITES_TAG_ID' ) )
			define( 'BP_FAVORITES_TAG_ID', apply_filters( 'bp_favorites_tag_id', 'bp_favorites_tag' ) );

		// Default slug for root component
		if ( !defined( 'BP_FAVORITES_SLUG' ) )
			define( 'BP_FAVORITES_SLUG', apply_filters( 'bp_favorites_slug', 'favorites' ) );

		// Plugin directory and URL
		define( 'BP_FAVORITES_DIR', WP_PLUGIN_DIR . '/bp-favorites' );
		define( 'BP_FAVORITES_URL', plugins_url( $path = '/bp-favorites' ) );

		// Images URL
		define( 'BP_FAVORITES_IMAGES_URL', BP_FAVORITES_URL . '/images' );

		// All done, but you can add your own stuff here
		do_action( 'bp_favorites_constants' );
	}

	/**
	 * includes ()
	 *
	 * Include required files
	 *
	 * @uses is_admin If in WordPress admin, load additional file
	 */
	function includes () {

		// Let plugins sneak in and include code ahead of BP Favorites
		do_action( 'bp_favorites_includes_pre' );

		// Load the files
		require_once ( BP_FAVORITES_DIR . '/bp-favorites-templatetags.php' );

		// Quick admin check and load if needed
		if ( is_admin() )
			require_once ( BP_FAVORITES_DIR . '/bp-favorites-admin.php' );

		// All done, but you can add your own stuff here
		do_action( 'bp_favorites_includes' );
	}

	/**
	 * bp_includes ()
	 *
	 * Include BuddyPress specific file
	 *
	 */
	function bp_includes () {
		// Load BuddyPress specific code if BuddyPress is around to do it
		if ( defined( 'BP_VERSION' ) || did_action( 'bp_include' ) )
			require_once ( BP_FAVORITES_DIR . '/bp-favorites-buddypress.php' );
	}

	/**
	 * loaded()
	 *
	 * A BP Favorites specific action to say that it has started its
	 * boot strapping sequence. It's attached to the existing WordPress
	 * action 'plugins_loaded' because that's when all plugins have loaded. Duh. :P
	 *
	 * @uses do_action()
	 */
	function loaded () {
		do_action( 'bp_favorites_loaded' );
	}

	/**
	 * init ()
	 *
	 * Initialize BP Favorites as part of the WordPress initilization process
	 *
	 * @uses do_action Calls custom action to allow external enhancement
	 */
	function init () {
		do_action ( 'bp_favorites_init' );
	}

	/**
	 * Load the BP Favorites translation file for current language
	 */
	function textdomain () {
		$locale = apply_filters( 'bp_favorites_textdomain', get_locale() );

		$mofile = BP_FAVORITES_DIR . "/languages/bp-favorites-$locale.mo";

		load_textdomain( 'bp-favorites', $mofile );
	}

	/**
	 * register_post_types()
	 *
	 * Setup the post types and taxonomy for favorites
	 *
	 * @todo Finish up the post type admin area with messages, columns, etc...*
	 */
	function register_post_types() {

		if ( !bp_favorites_is_root_blog() )
			return false;

		// Favorite post type labels
		$favorite_labels = array (
			'name'                  => __( 'Favorites', 'bp-favorites' ),
			'singular_name'         => __( 'Favorite', 'bp-favorites' ),
			'add_new'               => __( 'New Favorite', 'bp-favorites' ),
			'add_new_item'          => __( 'Create New Favorite', 'bp-favorites' ),
			'edit'                  => __( 'Edit', 'bp-favorites' ),
			'edit_item'             => __( 'Edit Favorite', 'bp-favorites' ),
			'new_item'              => __( 'New Favorite', 'bp-favorites' ),
			'view'                  => __( 'View Favorite', 'bp-favorites' ),
			'view_item'             => __( 'View Favorite', 'bp-favorites' ),
			'search_items'          => __( 'Search Favorites', 'bp-favorites' ),
			'not_found'             => __( 'No favorites found', 'bp-favorites' ),
			'not_found_in_trash'    => __( 'No favorites found in Trash', 'bp-favorites' ),
			'parent_item_colon'     => __( 'Parent Favorite:', 'bp-favorites' )
		);

		// Favorite post type supports
		$favorites_supports = array (
			'title',
			'thumbnail',
			'editor'
		);

		// Register favorite post type
		register_post_type (
			BP_FAVORITES_POST_TYPE_ID,
			apply_filters( 'bp_favorites_register_post_type',
				array (
					'labels'            => $favorite_labels,
					'supports'          => $favorites_supports,
					'menu_position'     => '100',
					'capability_type'   => 'post',
					'menu_icon'         => '',
					'public'            => true,
					'show_ui'           => true,
					'can_export'        => true,
					'hierarchical'      => false,
					'rewrite'           => false,
					'query_var'         => false
				)
			)
		);

		/**
		 * Post types have been registered
		 */
		do_action ( 'bp_favorites_register_post_types' );
	}

	/**
	 * register_taxonomies ()
	 *
	 * Register the built in BP Favorites taxonomies
	 *
	 * @package BP Favorites
	 * @subpackage Loader
	 * @since BP Favorites (1.0)
	 *
	 * @uses register_taxonomy()
	 * @uses apply_filters()
	 */
	function register_taxonomies () {

		if ( !bp_favorites_is_root_blog() )
			return false;

		// Favorite tag labels
		$favorite_tag_labels = array (
			'name'              => __( 'Favorite Tags', 'bp-favorites' ),
			'singular_name'     => __( 'Favorite Tag', 'bp-favorites' ),
			'search_items'      => __( 'Search Tags', 'bp-favorites' ),
			'popular_items'     => __( 'Popular Tags', 'bp-favorites' ),
			'all_items'         => __( 'All Tags', 'bp-favorites' ),
			'edit_item'         => __( 'Edit Tag', 'bp-favorites' ),
			'update_item'       => __( 'Update Tag', 'bp-favorites' ),
			'add_new_item'      => __( 'Add New Tag', 'bp-favorites' ),
			'new_item_name'     => __( 'New Tag Name', 'bp-favorites' ),
		);

		$favorite_tag_rewrite = array (
			'slug'              => 'tag'
		);

		// Register the favorite tag taxonomy
		register_taxonomy (
			BP_FAVORITES_TAG_ID,               // The tag ID
			BP_FAVORITES_POST_TYPE_ID,         // The post type ID
			apply_filters( 'bp_favorites_register_tag',
				array (
					'labels'                => $favorite_tag_labels,
					'rewrite'               => $favorite_tag_rewrite,
					'update_count_callback' => '_update_post_term_count',
					'query_var'             => 'favorite-tag',
					'hierarchical'          => false,
					'public'                => true,
					'show_ui'               => true,
				)
			)
		);

		/**
		 * Favorite tag taxonomy has been registered
		 */
		do_action ( 'bp_favorites_register_taxonomies' );
	}

	/**
	 * setup_globals()
	 *
	 * Sets up the plugin globals in the BuddyPress global array
	 *
	 * @global array $bp
	 * @global object $wpdb
	 */
	function setup_globals () {
		global $bp, $wpdb;

		// For internal identification
		$bp->favorites->id                              = BP_FAVORITES_POST_TYPE_ID;
		$bp->favorites->slug                            = BP_FAVORITES_SLUG;

		// Database
		$bp->favorites->version                         = BP_FAVORITES_VERSION;

		// Activity
		$bp->favorites->format_activity_function        = 'bp_favorites_format_activity';
		$bp->favorites->format_notification_function    = 'bp_favorites_format_notifications';

		// Register this in the active components array
		$bp->active_components[$bp->favorites->slug]    = $bp->favorites->id;

		// Allow external plugins to call additional actions
		do_action( 'bp_favorites_setup_globals' );
	}

	/**
	 * setup_nav
	 *
	 * Creates navigation in BuddyPress
	 *
	 * @global array $bp
	 */
	function setup_nav () {
		global $bp;

		// Add 'Favorites' to the main navigation
		bp_core_new_nav_item ( array (
			'name'                      => __( 'Favorites', 'bp-favorites' ),
			'slug'                      => $bp->favorites->slug,
			'position'                  => 70,
			'show_for_displayed_user'   => true,
			'screen_function'           => array( $this, 'screen_home' ),
			'default_subnav_slug'       => 'home',
			'item_css_id'               => $bp->favorites->id
		));

		// Create link for subnav to use as root
		$favorites_link = $bp->loggedin_user->domain . $bp->favorites->slug . '/';

		// Add the subnav items to the profile
		bp_core_new_subnav_item ( array (
			'name'              => __( 'Home', 'bp-favorites' ),
			'slug'              => 'home',
			'parent_url'        => $favorites_link,
			'parent_slug'       => $bp->favorites->slug,
			'screen_function'   => array( $this, 'screen_home' ),
			'position'          => 10,
			'user_has_access'   => true
		));

		bp_core_new_subnav_item ( array (
			'name'              => __( 'Friends', 'bp-favorites' ),
			'slug'              => 'friends',
			'parent_url'        => $favorites_link,
			'parent_slug'       => $bp->favorites->slug,
			'screen_function'   => array( $this, 'screen_friends' ),
			'position'          => 20,
			'user_has_access'   => true
		));

		bp_core_new_subnav_item ( array (
			'name'              => __( 'History', 'bp-favorites' ),
			'slug'              => 'history',
			'parent_url'        => $favorites_link,
			'parent_slug'       => $bp->favorites->slug,
			'screen_function'   => array( $this, 'screen_history' ),
			'position'          => 50,
			'user_has_access'   => true
		));

		if ( $bp->current_component == $bp->favorites->slug ) {
			if ( bp_is_my_profile() ) {
				$bp->bp_options_title = __( 'Favorite Summary', 'bp-favorites' );
			} else {
				$bp->bp_options_title = $bp->displayed_user->fullname;
			}
		}

		// Allow external plugins to call additional actions
		do_action( 'bp_favorites_setup_nav' );
	}

	/**
	 * add ()
	 *
	 * Does the dirty work of grabbing existing post and putting it in the favorites root blog
	 * 
	 * @param array $favorite
	 */
	function add ( $favorite = '' ) {

		// Default transaction arguments
		$defaults = array (
			'bp_favorites_network_id'   => 0,
			'bp_favorites_site_id'      => 0,
			'bp_favorites_post_id'      => 0,

			'bp_favorites_user_id'      => bp_loggedin_user_id(),
			'bp_favorites_date'         => bp_core_current_time()
		);

		// Get the difference
		$args = wp_parse_args ( $favorite, $defaults );

		// Switch to correct site
		switch_to_blog( $args['bp_favorites_site_id'] );

		// Get the post from that site
		if ( $post = get_post( $args['bp_favorites_post_id'], ARRAY_A ) ) {

			// Switch to correct site
			switch_to_blog( BP_FAVORITES_ROOT_BLOG );

			// Switch author to current user
			unset( $post['ID'] );
			$post['post_author'] = $args['bp_favorites_user_id'];
			$post['post_date']   = $args['bp_favorites_date'];
			$post['post_date']   = current_time( 'mysql', true );
			$post['post_type']   = BP_FAVORITES_POST_TYPE_ID;

			// Create the favorite
			$args['bp_favorites_id'] = wp_insert_post ( $post );

			// Update the meta data
			update_post_meta ( $args['bp_favorites_id'], 'bp_favorites_network_id', $args['bp_favorites_network_id'] );
			update_post_meta ( $args['bp_favorites_id'], 'bp_favorites_site_id',    $args['bp_favorites_site_id'] );
			update_post_meta ( $args['bp_favorites_id'], 'bp_favorites_post_id',    $args['bp_favorites_post_id'] );

			// Remove post from memory
			unset ( $post );

			// Go back to originating blog where favorite comes from
			restore_current_blog();
		}

		// Go back to current blog
		restore_current_blog();
	}

	/**
	 * remove ()
	 *
	 * Does the dirty work of removing a post from the favorites root blog
	 *
	 * @global object $wpdb
	 * @global object $post
	 * @param array $favorite
	 */
	function remove ( $favorite = '' ) {
		global $wpdb, $post;

		// Default transaction arguments
		$defaults = array (
			'post_id'         => $_REQUEST['pid'],
			'post_author'     => bp_loggedin_user_id(),
			'guid'            => get_post_field( 'guid', $post->ID, 'db' )
		);

		// Get the difference
		$args = wp_parse_args ( $favorite, $defaults );
		$r    = extract ( $args );

		// Switch to correct site
		switch_to_blog( BP_FAVORITES_ROOT_BLOG );

		if ( !empty( $post_id ) ) {

			// Create the favorite
			wp_delete_post ( $post_id );

			// Update the meta data
			delete_post_meta ( $post_id, 'bp_favorites_network_id' );
			delete_post_meta ( $post_id, 'bp_favorites_site_id' );
			delete_post_meta ( $post_id, 'bp_favorites_post_id' );

		// Get the post from that site
		} else if ( $is_favorite = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE post_author = %d AND post_type = %s AND guid = %s LIMIT 1", $post_author, BP_FAVORITES_POST_TYPE_ID, $guid ) ) ) {

			// Create the favorite
			wp_delete_post ( $is_favorite->ID );

			// Update the meta data
			delete_post_meta ( $is_favorite->ID, 'bp_favorites_network_id' );
			delete_post_meta ( $is_favorite->ID, 'bp_favorites_site_id' );
			delete_post_meta ( $is_favorite->ID, 'bp_favorites_post_id' );

			// Remove post from memory
			unset ( $is_favorite );

		}

		// Go back to current blog
		restore_current_blog();
	}

	/**
	 * screen_home ()
	 *
	 * Home screen
	 */
	function screen_home () {
		do_action ( 'bp_favorites_screen_home' );
		bp_core_load_template ( apply_filters( 'bp_favorites_template_home', 'favorites/home' ) );
	}

	/**
	 * screen_friends ()
	 *
	 * Friends favorites screen
	 */
	function screen_friends () {
		do_action ( 'bp_favorites_screen_friends' );
		bp_core_load_template ( apply_filters( 'bp_favorites_template_friends', 'favorites/friends' ) );
	}

	/**
	 * screen_history ()
	 *
	 * History screen
	 */
	function screen_history () {
		do_action ( 'bp_favorites_screen_history' );
		bp_core_load_template ( apply_filters( 'bp_favorites_template_history', 'favorites/history' ) );
	}

	/**
	 * activation ()
	 *
	 * Placeholder for plugin activation sequence
	 *
	 * @package BP Favorites
	 * @subpackage Loader
	 * @since BP Favorites (1.0)
	 */
	function activation () {
		
		add_site_option( 'bp-favorites-db-version', BP_FAVORITES_DB_VERSION );
		
		register_uninstall_hook( __FILE__, array( $this, 'uninstall' ) );

		do_action( 'bp_favorites_activation' );
	}

	/**
	 * deactivation ()
	 *
	 * Placeholder for plugin deactivation sequence
	 *
	 * @package BP Favorites
	 * @subpackage Loader
	 * @since BP Favorites (1.0)
	 */
	function deactivation () {
		do_action( 'bp_favorites_deactivation' );
	}

	/**
	 * uninstall ()
	 *
	 * Placeholder for plugin uninstall sequence
	 *
	 * @package BP Favorites
	 * @subpackage Loader
	 * @since BP Favorites (1.0)
	 */
	function uninstall () {
		do_action( 'bp_favorites_uninstall' );
	}
}

?>
