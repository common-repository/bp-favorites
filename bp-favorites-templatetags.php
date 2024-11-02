<?php

/* Start of loop functions ****************************************************/

/**
 * Starts the loop
 *
 * @global object $bp
 * @global WP_Query $favorites_template
 * @param array $args
 * @return object
 */
function bp_has_favorites ( $args = '' ) {
	global $bp, $favorites_template;

	$default = array (
		'author'		=> $bp->displayed_user->id,
		'post_type'		=> BP_FAVORITES_POST_TYPE_ID,
		'orderby'		=> 'menu_order'
	);

	$r = wp_parse_args( $args, $default );

	$favorites_template = new WP_Query( $r );

	return apply_filters( 'bp_has_favorites', $favorites_template->have_posts(), &$favorites_template );
}

/**
 * Sets up the post
 *
 * @global WP_Query $favorites_template
 * @return object The current post
 */
function bp_the_favorites () {
	global $favorites_template;
	return $favorites_template->the_post();
}

/**
 * Makes sure we have posts to display
 *
 * @global WP_Query $favorites_template
 * @return object Array of posts
 */
function bp_favorites () {
	global $favorites_template;
	return $favorites_template->have_posts();
}

/**
 * Echo out the row class
 */
function bp_favorites_css_class () {
	echo bp_get_favorites_css_class();
}
	/**
	 * Compute the row class
	 *
	 * @global WP_Query $favorites_template
	 * @return string
	 */
	function bp_get_favorites_css_class () {
		global $favorites_template;

		$class = false;

		if ( $favorites_template->current_post % 2 == 1 )
			$class .= 'alt';

		return apply_filters( 'bp_get_favorites_css_class', trim( $class ) );
	}

/**
 * Output the total of favorites
 */
function bp_favorites_total() {
	echo bp_get_favorites_total();
}
	/**
	 * Returns the total of favorites
	 * @return int
	 */
	function bp_get_favorites_total() {
		global $favorites_template;
		return apply_filters( 'bp_get_favorites_total', (int)$favorites_template->post_count );
	}

/**
 * Output the title of the transaction
 */
function bp_favorites_title () {
	echo bp_get_favorites_title();
}
	/**
	 * Get the title of the transaction
	 *
	 * @return string
	 */
	function bp_get_favorites_title () {
		return apply_filters( 'bp_get_favorites_title', get_the_title() );
	}

/**
 * Output the description of the transaction
 */
function bp_favorites_description () {
	echo bp_get_favorites_description();
}
	/**
	 * Returns the description of the transaction
	 * @return string
	 */
	function bp_get_favorites_description () {
		return apply_filters( 'bp_get_favorites_description', get_the_content() );
	}

/**
 * Output the date the transaction took place
 */
function bp_favorites_date () {
	echo bp_get_favorites_date();
}
	/**
	 * Returns the date the transaction took place
	 * @return string
	 */
	function bp_get_favorites_date () {
		return apply_filters( 'bp_get_favorites_date', get_the_date() );
	}

/**
 * Output the time the transaction took place
 */
function bp_favorites_time () {
	echo bp_get_favorites_time();
}
	/**
	 * Returns the time the transaction took place
	 * @return string
	 */
	function bp_get_favorites_time () {
		return apply_filters( 'bp_get_favorites_time', get_the_time() );
	}

/**
 * Output network of favorite item
 */
function bp_favorites_network() {
	echo bp_get_favorites_network();
}
	/**
	 * Returns network of favorite item
	 * @return int
	 */
	function bp_get_favorites_network() {
		$network_id = (int)get_post_meta( get_the_ID(), 'bp_favorites_network_id', true );
		return apply_filters( 'bp_get_favorites_network', $network_id );
	}

/**
 * Output site of favorite item
 */
function bp_favorites_site() {
	echo bp_get_favorites_site();
}
	/**
	 * Returns site of favorite item
	 * @return int
	 */
	function bp_get_favorites_site() {
		$site_id = (int)get_post_meta( get_the_ID(), 'bp_favorites_site_id', true );
		return apply_filters( 'bp_get_favorites_site', $site_id );
	}

/**
 * Output post of favorite item
 */
function bp_favorites_post() {
	echo bp_get_favorites_post();
}
	/**
	 * Returns post of favorite item
	 * @return int
	 */
	function bp_get_favorites_post() {
		$post_id = (int)get_post_meta( get_the_ID(), 'bp_favorites_post_id', true );
		return apply_filters( 'bp_get_favorites_post', $post_id );
	}

/* End of loop functions ******************************************************/

function bp_favorites_do_transaction( $args = '' ) {

	$transaction_date = bp_core_current_time();

}
//add_action( 'bp_favorites_give_favorites', 'bp_favorites_do_transaction' );

function bp_favorites_user_permalink() {
	echo bp_favorites_get_user_permalink();
}
	function bp_favorites_get_user_permalink() {
		global $bp;
		return $bp->loggedin_user->domain . $bp->favorites->slug . '/';
	}

function bp_favorites_favorite_tabs() {
	global $bp, $favorites_template;

	// Don't show these tabs on a user's own profile
	if ( bp_is_my_profile() )
		return false;

	$current_tab = $bp->current_action
?>
	<ul class="content-header-nav">
		<li<?php if ( 'summary' == $current_tab || empty( $current_tab ) ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->displayed_user->domain . $bp->favorites->slug ?>/summary/"><?php _e( 'Summary', 'bp-favorites' );  ?></a></li>
		<li<?php if ( 'give' == $current_tab ) : ?> class="current"<?php endif; ?>><a href="<?php echo $bp->displayed_user->domain . $bp->favorites->slug ?>/give/"><?php _e( 'Give', 'bp-favorites' )  ?></a></li>
	</ul>
<?php
	do_action( 'bp_favorites_favorite_tabs', $current_tab );
}


/**
 * bp_favorites_has_access()
 *
 * Make sure user can perform special tasks
 *
 * @return bool $can_do
 */
function bp_favorites_has_access() {

	if ( is_super_admin() )
		$has_access = true;
	else
		$has_access = false;

	return apply_filters( 'bp_favorites_has_access', $has_access );
}

/**
 * bp_favorites_is_root_blog()
 *
 * Check if current blog is the Favorites root blog
 *
 * @global object $current_blog
 * @return bool
 */
function bp_favorites_is_root_blog () {
	global $current_blog;

	if ( BP_FAVORITES_ROOT_BLOG == $current_blog->blog_id )
		return true;

	return false;
}

function bp_favorites_add_link() {
	echo bp_get_favorites_add_link();
}
	function bp_get_favorites_add_link() {
		global $current_blog, $post;

		$request_url = $_SERVER['REQUEST_URI'];
		$favorite_url = add_query_arg( array( 'bp_favorites_action' => 'add', 'nid' => $current_blog->site_id, 'bid' => $current_blog->blog_id, 'pid' => $post->ID ), $request_url );

		return apply_filters( 'bp_get_favorites_add_link', wp_nonce_url( $favorite_url, 'bp_favorites_add' ) );
	}

function bp_favorites_remove_link() {
	echo bp_get_favorites_remove_link();
}
	function bp_get_favorites_remove_link() {
		global $post, $wpdb;

		// Default transaction arguments
		$favorite = array (
			'post_author'       => bp_loggedin_user_id(),
			'post_type'         => BP_FAVORITES_POST_TYPE_ID,
			'guid'              => get_post_field( 'guid', $post->ID, 'raw' ),
		);

		$r = extract ( $favorite );

		// Switch to correct site
		switch_to_blog( BP_FAVORITES_ROOT_BLOG );

		$is_favorite = $wpdb->get_row( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_author = %d AND post_type = %s AND guid = %s LIMIT 1", $post_author, $post_type, $guid ) );

		restore_current_blog();

		$request_url = $_SERVER['REQUEST_URI'];
		$favorite_url = add_query_arg( array( 'bp_favorites_action' => 'remove', 'bid' => BP_FAVORITES_ROOT_BLOG, 'pid' => $is_favorite->ID ), $request_url );

		return apply_filters( 'bp_get_favorites_add_link', wp_nonce_url( $favorite_url, 'bp_favorites_remove' ) );
	}

function bp_favorites_is_added ( $args = '' ) {
	global $current_blog, $post, $wpdb;

	// Default transaction arguments
	$defaults = array (
		'post_author'       => bp_loggedin_user_id(),
		'post_type'         => BP_FAVORITES_POST_TYPE_ID,
		'guid'              => get_post_field( 'guid', $post->ID, 'db' ),
	);

	// Get the difference
	$favorite = wp_parse_args ( $args, $defaults );
	$r        = extract ( $favorite );

	// Switch to correct site
	switch_to_blog( BP_FAVORITES_ROOT_BLOG );

	$is_favorite = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE post_author = %d AND post_type = %s AND guid = %s LIMIT 1", $post_author, $post_type, $guid ) );

	restore_current_blog();

	// Get the post from that site
	if ( !empty( $is_favorite ) )
		return $is_favorite->ID;

	return false;
}

function bp_favorites_add ( $args = '' ) {
	global $bp_favorites;

	if ( isset( $_REQUEST['bp_favorites_action'] ) && ( 'add' === $_REQUEST['bp_favorites_action'] ) && isset( $_REQUEST['nid'] ) && isset( $_REQUEST['bid'] ) && isset( $_REQUEST['pid'] ) ) {
		check_admin_referer( 'bp_favorites_add' );

		// Default transaction arguments
		$defaults = array (
			'bp_favorites_network_id'   => $_REQUEST['nid'],
			'bp_favorites_site_id'      => $_REQUEST['bid'],
			'bp_favorites_post_id'      => $_REQUEST['pid'],

			'bp_favorites_user_id'      => bp_loggedin_user_id(),
			'bp_favorites_date'         => bp_core_current_time()
		);

		// Get the difference
		$favorite = wp_parse_args ( $args, $defaults );

		$bp_favorites->add( $favorite );

		bp_core_add_message ( __( 'That post was successfully added to your favorites!', 'bp-favorites' ) );
	}
	
}
add_action( 'wp', 'bp_favorites_add', 3 );

function bp_favorites_remove ( $args = '' ) {
	global $bp_favorites;

	if ( isset( $_REQUEST['bp_favorites_action'] ) && ( 'remove' === $_REQUEST['bp_favorites_action'] ) && isset( $_REQUEST['bid'] ) && isset( $_REQUEST['pid'] ) ) {
		check_admin_referer( 'bp_favorites_remove' );

		// Default transaction arguments
		$defaults = array (
			'post_id'       => $_REQUEST['pid'],
			'post_author'   => bp_loggedin_user_id(),
			'guid'          => bp_loggedin_user_id(),
		);

		// Get the difference
		$favorite = wp_parse_args ( $args, $defaults );

		$bp_favorites->remove( $favorite );

		bp_core_add_message ( __( 'That post was successfully removed from your favorites!', 'bp-favorites' ) );
	}

}
add_action( 'wp', 'bp_favorites_remove', 3 );

function bp_favorites_add_form ( $args = '' ) {
	global $current_site, $current_blog;

	// Default transaction arguments
	$defaults = array (
		'bp_favorites_network_id'   => $_REQUEST['nid'],
		'bp_favorites_site_id'      => $_REQUEST['bid'],
		'bp_favorites_post_id'      => $_REQUEST['pid'],

		'bp_favorites_user_id'      => bp_loggedin_user_id(),
		'bp_favorites_date'         => bp_core_current_time()
	);

	// Get the difference
	$favorite = wp_parse_args ( $args, $defaults );
	$r = extract( $favorite );

?>
		<form action="" id="bp_favorites_form">
			<button type="submit" class="button" name="submit"><?php _e( 'Add Favorite', 'bp-favorites' ); ?></button>
			<input type="hidden" name="nid" id="nid" value="<?php echo $bp_favorites_network_id; ?>" />
			<input type="hidden" name="bid" id="bid" value="<?php echo $bp_favorites_site_id; ?>" />
			<input type="hidden" name="pid" id="pid" value="<?php echo $bp_favorites_post_id; ?>" />
			<?php wp_nonce_field( 'bp_favorites_add' ); ?>

		</form>
<?php
}

?>
