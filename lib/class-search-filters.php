<?php
namespace Zoninator_EP;

class Search_Filters {
	use Singleton;

	public function setup() {
		add_action( 'zoninator_advanced_search_fields', [ $this, 'advanced_search_field_date' ], 25 );
		add_action( 'zoninator_advanced_filter_category', [ $this, 'category_dropdown_args' ], 25 );
		add_action( 'zoninator_advanced_search_fields', [ $this, 'advanced_search_field_post_types' ], 20 );

		add_filter( 'zoninator_search_args', [ $this, 'search_args' ] );
		add_filter( 'zoninator_recent_posts_args', [ $this, 'search_args' ] );

		add_filter( 'init', [ $this, 'alter_zoninator_core' ], 100, 1 );
	}

	/**
	 * Alter the core zoninator object after it's been loaded.
	 */
	public function alter_zoninator_core() {
		global $zoninator;
		if ( ! $zoninator ) {
			return;
		}

		remove_action( 'zoninator_advanced_search_fields', [ $zoninator, 'zone_advanced_search_date_filter' ], 20 );
	}

	/**
	 * Add a date dropdown to the Zoninator advanced search filters
	 *
	 * @return void
	 */
	public function advanced_search_field_date() {
		global $zoninator;

		$selected_date = $zoninator->_get_post_var( 'zone_advanced_filter_post_type', '', 'esc_attr' );
		$dates = [
			'today' => __( 'Today', 'zoninator-expansion-pack' ),
			'yesterday' => __( 'Yesterday', 'zoninator-expansion-pack' ),
			'this week' => __( 'This week', 'zoninator-expansion-pack' ),
			'this month' => __( 'This month', 'zoninator-expansion-pack' ),
		];
		for ( $i = 1; $i <= 12; $i++ ) {
			$date = strtotime( "-{$i} months" );
			$dates[ date( 'Y-m', $date ) ] = date( 'F Y', $date );
		}
		?>
		<select name="zone_advanced_filter_date" id="zone_advanced_filter_date">
			<option value=""><?php esc_html_e( 'Include posts from...', 'zoninator-expansion-pack' ); ?></option>
			<?php foreach ( $dates as $key => $value ) : ?>
				<option value="<?php echo esc_attr( $key ) ?>"<?php selected( $selected_date, $key ) ?>><?php echo esc_html( $value ) ?></option>
			<?php endforeach ?>
		</select>
		<?php
	}

	public function category_dropdown_args( $args ) {
		$args['orderby'] = 'NAME';
		$args['order'] = 'ASC';

		return $args;
	}

	/**
	 * Add a post types dropdown to the Zoninator advanced search filters
	 *
	 * @return void
	 */
	public function advanced_search_field_post_types() {
		global $zoninator;
		$selected_post_type = $zoninator->_get_post_var( 'zone_advanced_filter_post_type', '', 'esc_attr' );
		$post_types = $zoninator->get_supported_post_types();
		$post_types = array_map( 'get_post_type_object', $post_types );
		?>
		<select name="zone_advanced_filter_post_type" id="zone_advanced_filter_post_type">
				<option value=""><?php esc_html_e( 'All Content Types', 'wwd' ); ?></option>
				<?php foreach ( $post_types as $type ) : ?>
					<option value="<?php echo esc_attr( $type->name ) ?>"<?php selected( $selected_post_type, $type->name ) ?>><?php echo esc_html( $type->labels->name ) ?></option>
				<?php endforeach ?>
		</select>
		<?php
	}

	/**
	 * Filter the Zoninator search and recent posts ajax requests.
	 *
	 * @param array $args WP_Query args.
	 * @return array WP_Query args.
	 */
	public function search_args( $args ) {
		global $zoninator;

		$filter_post_type = $zoninator->_get_request_var( 'post_type', '', 'esc_attr' );
		if ( ! empty( $filter_post_type ) ) {
			$args['post_type'] = $filter_post_type;
		}

		$filter_date = $zoninator->_get_request_var( 'zep_date', '', 'esc_attr' );
		if ( ! empty( $filter_date ) ) {
			$args['date_query'] = [
				[ 'after' => $filter_date, 'inclusive' => true ],
			];
			if ( preg_match( '/\d{4}-\d\d/', $filter_date ) ) {
				$args['date_query'][] = [ 'before' => $filter_date, 'inclusive' => true ];
			}
		}

		return $args;
	}
}
