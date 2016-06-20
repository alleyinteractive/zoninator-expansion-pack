<?php
namespace Zoninator_EP;

class Term_Binding {
	use Singleton;

	protected $current_term;

	protected $taxonomies;

	public function setup() {
		$this->taxonomies = apply_filters( 'zoninator_ep_bind_taxonomies', [] );
		if ( ! empty( $this->taxonomies ) ) {
			foreach ( $this->taxonomies as $tax ) {
				add_action( 'created_' . $tax, [ $this, 'add_zone' ], 10, 2 );
				add_filter( $tax . '_row_actions', [ $this, 'show_term_row_link' ], 10, 2 );
			}

			add_action( 'zoninator_advanced_search_fields', [ $this, 'advanced_search_field_taxonomies' ], 15 );
			add_action( 'zoninator_advanced_filter_category', [ $this, 'category_dropdown_args' ], 25 );

			add_action( 'zoninator_post_init', [ $this, 'maybe_set_current_term' ] );
			add_filter( 'zoninator_recent_posts_args', [ $this, 'default_term' ] );

			add_action( 'admin_bar_menu', [ $this, 'add_to_admin_bar' ], 999 );
			add_action( 'wp_footer', [ $this, 'add_admin_bar_css' ], 999 );
			add_action( 'admin_footer', [ $this, 'add_admin_bar_css' ], 999 );

			add_action( 'admin_post_create_zone', [ $this, 'admin_post_create_zone' ] );
		}
	}

	/**
	 * Add a post types dropdown to the Zoninator advanced search filters
	 *
	 * @return void
	 */
	public function advanced_search_field_taxonomies() {
		global $zoninator;
		foreach ( $this->taxonomies as $tax ) {
			if ( 'category' == $tax ) {
				continue;
			}

			$selected_term = $zoninator->_get_post_var( "zone_advanced_filter_taxonomy_{$tax}", '', 'absint' );
			if ( ! $selected_term ) {
				$selected_term = $this->current_term;
			}
			$tax_obj = get_taxonomy( $tax );

			wp_dropdown_categories( [
				'taxonomy'        => $tax,
				'orderby'         => 'name',
				'show_option_all' => sprintf( __( 'All %s', 'zoninator-expansion-pack' ), $tax_obj->labels->name ),
				'selected'        => $selected_term,
				'name'            => 'zone_advanced_filter_taxonomy_' . $tax,
				'id'              => 'zone_advanced_filter_taxonomy_' . $tax,
				'hide_if_empty'   => true,
				'class'           => 'postform zep-tax-filter',
			] );
		}
	}

	/**
	 * Add a link to edit the zoninator zone in the Admin Bar if we're on a term
	 * page with a valid zone.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 */
	public function add_to_admin_bar( $wp_admin_bar ) {
		global $zoninator;

		if ( ! $zoninator ) {
			return;
		}

		if ( is_tax() || is_tag() || is_category() ) {
			$term = get_queried_object();
		}

		if ( ! isset( $term->term_id ) ) {
			return;
		}

		if ( ! in_array( $term->taxonomy, $this->taxonomies ) ) {
			return;
		}

		$zep_term = new Term( $term );
		if ( ! $zep_term->zone_exists() ) {
			return;
		}

		if ( current_user_can( $zoninator->_get_edit_zones_cap() ) ) {
			$wp_admin_bar->add_node( [
				'id'    => 'zoninator',
				'title' => __( 'Curate Zone', 'zoninator-expansion-pack' ),
				'href'  => $zep_term->get_zone_edit_url(),
			] );
		}
	}

	/**
	 * Add dashicon to zoninator link in the Admin Bar.
	 */
	public function add_admin_bar_css() {
		if ( is_user_logged_in() ) :
			?>
			<style>
			#wp-admin-bar-zoninator a:before { font-family: 'dashicons'; content: "\f503" !important; margin-top: 2px; }
			</style>
		<?php
		endif;
	}

	public function category_dropdown_args( $args ) {
		if ( empty( $args['selected'] ) && $this->current_term ) {
			$term = get_term( $this->current_term );
			if ( ! empty( $term->taxonomy ) && 'category' === $term->taxonomy ) {
				$args['selected'] = $this->current_term;
			}
		}

		return $args;
	}

	public function maybe_set_current_term() {
		global $zoninator;

		// If we're curating a term, default to filtering by that category
		if ( ! empty( $_GET['page'] ) && 'zoninator' === $_GET['page'] && ! empty( $_GET['zone_id'] ) ) {
			$zone = $zoninator->get_zone( absint( $_GET['zone_id'] ) );
			if ( $zone ) {
				$zone_slug = $zoninator->get_zone_slug( $zone );
				if ( preg_match( '/^(' . implode( '|', $this->taxonomies ) . ')_(.*)$/', $zone_slug, $matches ) ) {
					$get_term_by = function_exists( 'wpcom_vip_get_term_by' ) ? 'wpcom_vip_get_term_by' : 'get_term_by';
					$term = call_user_func( $get_term_by, 'slug', $matches[2], $matches[1] );

					if ( ! empty( $term->term_id ) ) {
						$this->current_term = $term->term_id;
					}
				}
			}
		}
	}

	public function default_term( $args ) {
		if ( $this->current_term ) {
			$term = get_term( $this->current_term );
			if ( ! empty( $term->taxonomy ) && empty( $args[ $term->taxonomy ] ) ) {
				$args[ $term->taxonomy ] = $term->slug;
			}
		}

		return $args;
	}

	/**
	 * Add the link to the row actions in the list of terms.
	 *
	 * @param array $actions
	 * @param WP_Term $term
	 * @return array
	 */
	public function show_term_row_link( $actions, $term ) {
		global $zoninator;
		if ( ! $zoninator ) {
			return;
		}

		$zep_term = new Term( $term );

		$tax_obj = get_taxonomy( $term->taxonomy );
		if ( current_user_can( $tax_obj->cap->manage_terms, $term->term_id ) ) {
			if ( $zep_term->zone_exists() ) {
				$actions['edit_zone'] = sprintf(
					'<a href="%s">%s</a>',
					esc_url( $zep_term->get_zone_edit_url() ),
					esc_html__( 'Curate&nbsp;Zone', 'zoninator-expansion-pack' )
				);
			}
		}

		return $actions;
	}

	/**
	 * Handles adding the default Top Stories zone whenever a new language is added
	 *
	 * @param int $term_id
	 * @param int $tt_id
	 */
	public function add_zone( $term_id, $tt_id ) {
		global $zoninator;
		if ( ! $zoninator ) {
			return;
		}

		// Prep the term objects
		$term = get_term( $term_id );
		$zep_term = new Term( $term );

		// Check if the zone already exists and if not add it
		if ( ! $zep_term->zone_exists() ) {
			$tax_obj = get_taxonomy( $term->taxonomy );

			$zone = $zoninator->insert_zone( $zep_term->get_zone_slug(), "{$tax_obj->labels->singular_name}: {$term->name}", [
				'description' => sprintf(
					__( 'Zone curation for the "%1$s" %2$s', 'zoninator-expansion-pack' ),
					$term->name,
					$tax_obj->labels->singular_name
				),
			] );
		}
	}
}
