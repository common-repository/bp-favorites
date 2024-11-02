<?php

function bp_favorites_record_favorite ( $post_id, $post, $user_id = false ) {
	global $bp, $wpdb;

	$post_id = (int)$post_id;
	$blog_id = (int)$wpdb->blogid;

	if ( !$user_id )
		$user_id = (int)$bp->loggedin_user->id;

	// This is to stop infinate loops with Donncha's sitewide tags plugin
	if ( (int)get_site_option('tags_blog_id') == (int)$blog_id )
		return false;

	// Don't record this if it's not a post
	if ( $post->post_type != 'post' )
		return false;

	if ( !$is_recorded = BP_Favorites_Favorite::is_recorded( $post_id, $blog_id, $user_id ) ) {
		if ( 'publish' == $post->post_status && '' == $post->post_password ) {

			$recorded_post = new BP_Favorites_Favorite;
			$recorded_post->user_id = $user_id;
			$recorded_post->blog_id = $blog_id;
			$recorded_post->post_id = $post_id;
			$recorded_post->date_created = strtotime( $post->post_date );

			$recorded_post_id = $recorded_post->save();

			$post_permalink = bp_favorite_get_permalink( $post, $blog_id );

			$activity_content = sprintf( __( '%s added a new favorite: %s', 'bp-favorites' ), bp_core_get_userlink( (int)$user_id ), '<a href="' . $post_permalink . '">' . $post->post_title . '</a>' );

			/* Record this in activity streams */
			bp_favorites_record_activity( array(
				'user_id' => (int)$post->post_author,
				'content' => apply_filters( 'bp_blogs_activity_new_post', $activity_content, &$post, $post_permalink ),
				'primary_link' => apply_filters( 'bp_blogs_activity_new_post_primary_link', $post_permalink, $post_id ),
				'component_action' => 'new_blog_post',
				'item_id' => $recorded_post_id,
				'recorded_time' => strtotime( $post->post_date )
			) );
		}
	}

	do_action( 'bp_favorites_new_favorite', $existing_post, $is_private, $is_recorded );
}
//add_action( 'save_post', 'bp_favorites_record_favorite', 10, 2 );

/********************************************************************************
 * Activity & Notification Functions
 *
 * These functions handle the recording, deleting and formatting of activity and
 * notifications for the user and for this specific component.
 */

function bp_favorites_register_activity_actions () {
	global $bp;

	if ( !function_exists( 'bp_activity_set_action' ) )
		return false;

	bp_activity_set_action( $bp->favorites->id, 'new_favorite', __( 'New favorite added', 'bp-favorites' ) );

	do_action( 'bp_favorites_register_activity_actions' );
}
add_action( 'init', 'bp_favorites_register_activity_actions' );

function bp_favorites_record_activity ( $args = '' ) {
	global $bp;

	if ( !function_exists( 'bp_activity_add' ) )
		return false;

	/**
	 * Because blog, comment, and blog post code execution happens before anything else
	 * we may need to manually instantiate the activity component globals
	 */
	if ( !$bp->activity && function_exists('bp_activity_setup_globals') )
		bp_activity_setup_globals();

	$defaults = array(
		'user_id'           => $bp->loggedin_user->id,
		'content'           => false,
		'primary_link'      => false,
		'component_name'    => $bp->blogs->id,
		'component_action'  => false,
		'item_id'           => false,
		'secondary_item_id' => false,
		'recorded_time'     => bp_core_current_time(),
		'hide_sitewide'     => false
	);

	$r = wp_parse_args( $args, $defaults );
	extract ( $r, EXTR_SKIP );

	return bp_activity_add (
		array (
			'user_id'           => $user_id,
			'content'           => $content,
			'primary_link'      => $primary_link,
			'component_name'    => $component_name,
			'component_action'  => $component_action,
			'item_id'           => $item_id,
			'secondary_item_id' => $secondary_item_id,
			'recorded_time'     => $recorded_time,
			'hide_sitewide'     => $hide_sitewide
		)
	);
}

function bp_favorites_delete_activity ( $args = true ) {
	if ( function_exists( 'bp_activity_delete_by_item_id' ) ) {
		extract($args);

		bp_activity_delete_by_item_id (
			array (
				'item_id'           => $item_id,
				'component_name'    => $component_name,
				'component_action'  => $component_action,
				'user_id'           => $user_id,
				'secondary_item_id' => $secondary_item_id
			)
		);
	}
}

?>
