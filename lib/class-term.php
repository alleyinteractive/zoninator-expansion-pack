<?php
namespace Zoninator_EP;

/**
 * Term object wrapper
 *
 * This object is a helper for binding terms and zones.
 */
class Term {

	/**
	 * The term.
	 *
	 * @var WP_Term
	 */
	protected $term = null;

	protected $zone_slug;

	/**
	 * Build the object.
	 *
	 * @param WP_Term $term Optional. If missing, the current page should be a
	 *                      term page.
	 */
	public function __construct( $term = null ) {
		$this->validate_term( $term );
	}

	/**
	 * Validate/set a term. If the term isn't set and we're on a term page, the
	 * current term is used.
	 *
	 * @param  WP_Term $term Optional. If absent, and we're on a term page, the
	 *                       current term is used.
	 */
	protected function validate_term( $term = null ) {
		if ( ! $term ) {
			$term = get_queried_object();
		}

		if ( ! empty( $term->term_id ) ) {
			$this->term = $term;
		}
	}

	/**
	 * Get the Zoninator zone slug for a given (or the current) term.
	 *
	 * @return string|boolean Zone slug if it exists, false if not.
	 */
	public function get_zone_slug() {
		if ( ! isset( $this->zone_slug ) ) {
			if ( ! $this->term || ! function_exists( 'z_get_zoninator' ) ) {
				return false;
			}

			$this->zone_slug = "{$this->term->taxonomy}_{$this->term->slug}";
		}

		return $this->zone_slug;
	}

	/**
	 * Does the term's zone slug exist?
	 *
	 * @return bool
     */
	public function zone_exists() {
		$zone_slug = $this->get_zone_slug();
		return $zone_slug && z_get_zoninator()->zone_exists( $zone_slug ) ? true : false;
	}

	/**
	 * Get the Zoninator query for this term.
	 *
	 * @param array $args Associative array of additional query arguments to
	 *                    pass into the query.
	 * @return WP_Query
	 */
	public function get_zone_query( $args = [] ) {
		if ( $this->zone_exists() ) {
			return z_get_zoninator()->get_zone_query( $this->get_zone_slug(), $args );
		}

		return new WP_Query();
	}

	/**
	 * Get all the posts in the zone.
	 *
	 * @return array|boolean WP_Post objects if set, false if not or on failure.
	 */
	public function get_zone_posts() {
		if ( $this->zone_exists() ) {
			return z_get_zoninator()->get_zone_posts( $this->get_zone_slug() );
		}

		return false;
	}

	/**
	 * Get the URL to edit the zone for this term.
	 *
	 * @return string|boolean Admin page URL or false
	 */
	public function get_zone_edit_url() {
		if ( $this->zone_exists() ) {
			$zone = z_get_zoninator()->get_zone( $this->get_zone_slug() );

			if ( z_get_zoninator()->_current_user_can_edit_zones( $zone->term_id ) ) {
				return add_query_arg( array(
					'page' => z_get_zoninator()->key,
					'action' => 'edit',
					'zone_id' => $zone->term_id,
				), admin_url( 'admin.php' ) );
			}
		}

		return false;
	}
}
