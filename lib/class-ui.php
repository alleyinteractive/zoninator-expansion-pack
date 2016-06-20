<?php
namespace Zoninator_EP;

class UI {
	/**
	 * Filter the ajax search results to add the thumbnail.
	 *
	 * @param  array $data Current data returned in ajax response for this post.
	 * @param  WP_Post $post Current post.
	 * @return array Filtered data.
	 */
	public static function ajax_search_results( $data, $post ) {
		$data['thumbnail'] = get_the_post_thumbnail( $post->ID, 'thumbnail' );
		return $data;
	}

	/**
	 * Output the thumbnail in the column we added.
	 *
	 * @param  WP_Post $post Current post object.
	 */
	public static function column_thumbnail( $post ) {
		echo get_the_post_thumbnail( $post->ID, 'thumbnail' );
	}

	/**
	 * Output the post type in the appropriate column.
	 *
	 * @param  WP_Post $post Current post object.
	 */
	public static function column_post_type( $post ) {
		$pt_obj = get_post_type_object( $post->post_type );
		if ( $pt_obj ) {
			echo esc_html( $pt_obj->labels->singular_name );
		} else {
			echo esc_html( $post->post_type );
		}
	}

	/**
	 * Add columns to the Zoninator output.
	 *
	 * @param  array $columns Current columns to output.
	 * @return array Modified columns to output.
	 */
	public static function columns( $columns ) {
		$number = array_slice( $columns, 0, 1, true );
		$columns = array_slice( $columns, 1, null, true );
		return array_merge(
			$number,
			// array( 'thumbnail' => array( '\\Zoninator_EP\\UI', 'column_thumbnail' ) ),
			array( 'thumbnail' => array( __CLASS__, 'column_thumbnail' ) ),
			$columns,
			array( 'post_type' => array( __CLASS__, 'column_post_type' ) )
		);
	}
}
