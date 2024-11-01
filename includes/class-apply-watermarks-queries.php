<?php
namespace GPLSCore\GPLS_PLUGIN_WMFW;

use GPLSCore\GPLS_PLUGIN_WMFW\Helpers;
use GPLSCore\GPLS_PLUGIN_WMFW\Watermarks_Templates;

/**
 * Apply Watermarks Templates | Images - Posts Finder Queries.
 */
class Apply_Watermarks_Queries {

	use Helpers;

	/**
	 * Distinct Date Options For All CPTs.  [ cpt_slug ] => array( array( month => , year => , post_type => ) )
	 *
	 * @var array
	 */
	protected static $cpts_date_options = array();

	/**
	 * Distinct Date Options For All CPTs.  [ cpt_slug ] => array( array( author_obj => , post_type => ) )
	 *
	 * @var array
	 */
	protected static $cpts_author_options = array();

	/**
	 * CPTs Taxonomies and Terms Array Mapping.  [ cpt_slug ] => array of taxonomies names => array of terms objects
	 *
	 * @var array
	 */
	public static $cpts_taxonomies_terms = array();

	/**
	 * Find Images By Posts and Custom Post Types.
	 *
	 * @param array|string $cpt
	 * @param array        $posts
	 * @return array
	 */
	public static function find_images_by_posts( $cpt_names, $terms, $direct_posts_ids ) {
		$cpts_ids = array();
		// process the specific cpt posts IDs.
		foreach ( $cpt_names as $cpt_name => $cpt_options_arr ) :
			if ( ! empty( $terms[ $cpt_name ] ) ) :
				$cpts_ids = array_merge( $cpts_ids, self::get_cpt_for_terms( $cpt_name, $cpt_options_arr, $terms ) );
			else :
				$cpts_ids = array_merge( $cpts_ids, self::get_cpt_posts_ids( $cpt_name, $cpt_options_arr ) );
			endif;
		endforeach;
		if ( ! empty( $direct_posts_ids ) ) {
			$cpts_ids = array_merge( $cpts_ids, $direct_posts_ids );
		}
		$attachments_ids = self::get_attachments_by_posts_ids( array_unique( $cpts_ids ), true );

		return $attachments_ids;
	}

	/**
	 * Get Related Attachments to given Posts IDS.
	 *
	 * @param array $posts_ids
	 * @return array
	 */
	public static function get_attachments_by_posts_ids( $posts_ids, $only_images = false ) {
		return self::get_cpt_related_attachments( $posts_ids, $only_images );
	}

	/**
	 * Get CPT Posts IDs.
	 *
	 * @param string $cpt_name Custom Post Type.
	 * @param array  $cpt_options_arr Custom Post Type Options.
	 * @return array
	 */
	private static function get_cpt_posts_ids( $cpt_name, $cpt_options_arr ) {
		return self::plain_cpt_query( $cpt_name, $cpt_options_arr );
	}

	/**
	 * Get CPT For Selected Terms.
	 *
	 * @param string $cpt_name
	 * @param array  $cpt_options_arr
	 * @param array  $terms
	 * @return void
	 */
	private static function get_cpt_for_terms( $cpt_name, $cpt_options_arr, $terms = array() ) {
		$cpt_ids = self::plain_cpt_query( $cpt_name, $cpt_options_arr );
		$cpt_ids = self::dynamic_cpt_by_terms( $cpt_ids, $cpt_name, $terms );
		return $cpt_ids;
	}

	/**
	 * Conditional Query for CPTs with Terms.
	 *
	 * @param array  $cpt_ids
	 * @param string $cpt_name
	 * @param array  $terms
	 * @return array
	 */
	private static function dynamic_cpt_by_terms( $cpt_ids, $cpt_name, $terms ) {
		global $wpdb;
		if ( empty( $cpt_ids ) ) {
			return array();
		}

		foreach ( $terms[ $cpt_name ] as $taxonomy_name => $taxonomy_terms_arr ) :
			$cpt_ids = $wpdb->get_col(
				"SELECT
					p.id
				FROM
					$wpdb->posts p
				INNER JOIN
					$wpdb->term_relationships tr
				ON
					p.ID = tr.object_id
				INNER JOIN
					$wpdb->term_taxonomy tt
				ON
					tr.term_taxonomy_id = tt.term_taxonomy_id
				WHERE
					p.ID IN ('" . implode( "','", $cpt_ids ) . "')
				AND
					tt.term_id IN ('" . implode( "','", $taxonomy_terms_arr ) . "')
				"
			);
		endforeach;

		return $cpt_ids;
	}

	/**
	 * Plain Custom Post Type Query.
	 *
	 * @param string $cpt_name
	 * @param array  $cpt_options_arr
	 * @return array
	 */
	private static function plain_cpt_query( $cpt_name, $cpt_options_arr ) {
		global $wpdb;
		$cpts_ids = array();
		$query    =
			"SELECT
				p.ID
			FROM
				{$wpdb->prefix}posts p
			WHERE
				p.post_type = '{$cpt_name}'
		";

		if ( ! empty( $cpt_options_arr['statuses'] ) ) {
			$posts_statuses = $cpt_options_arr['statuses'];
			$query         .= " AND p.post_status IN ('" . implode( "','", $posts_statuses ) . "') ";
		} else {
			$query .= " AND p.post_status != 'auto-draft'";
		}

		if ( ! empty( $cpt_options_arr['authors'] ) ) {
			$posts_authors = $cpt_options_arr['authors'];
			$query        .= " AND p.post_author IN ('" . implode( "','", $posts_authors ) . "') ";
		}

		if ( ! empty( $cpt_options_arr['start_date'] ) ) {
			$posts_start_date = $cpt_options_arr['start_date'];
			$query           .= $wpdb->prepare( ' AND p.post_date >= %s', gmdate( 'Y-m-d', strtotime( $posts_start_date ) ) );
		}

		if ( ! empty( $cpt_options_arr['end_date'] ) ) {
			$post_end_date = $cpt_options_arr['end_date'];
			$query        .= $wpdb->prepare( ' AND p.post_date < %s', gmdate( 'Y-m-d', strtotime( '+1 month', strtotime( $post_end_date ) ) ) );
		}

		$cpts_ids = $wpdb->get_col( $query );
		return $cpts_ids;
	}

	/**
	 * Get Posts IDs Related Attachments.
	 *
	 * @param array $cpts_ids
	 * @return array
	 */
	private static function get_cpt_related_attachments( $cpts_ids, $only_images = false ) {
		global $wpdb;
		$attachments = array();
		// Direct inherited Attachments
		$query = "SELECT
				p.ID
			FROM
				{$wpdb->prefix}posts AS p
			INNER JOIN
				{$wpdb->prefix}posts AS pp
			ON
				p.post_parent = pp.id
			WHERE
				pp.id IN ('" . implode( "','", $cpts_ids ) . "')
			AND
				p.post_type = 'attachment'
			AND
				p.post_status = 'inherit'";

		if ( $only_images ) {
			$query .= " AND p.post_mime_type LIKE 'image/%'";
		}

		$query .= ' UNION ALL ';

			// attachment added as a custom meta by ID ( thumbnails )

		$query .= "SELECT
				p.ID
			FROM
				{$wpdb->prefix}posts AS p			# attachment
			INNER JOIN
				{$wpdb->prefix}postmeta AS pm		# target CPT's postmeta
			ON
				p.ID = pm.meta_value
			INNER JOIN
				{$wpdb->prefix}posts AS pp			# target CPT
			ON
				pp.ID = pm.post_id
			WHERE
				pp.id IN ('" . implode( "','", $cpts_ids ) . "')
			AND
				p.post_type = 'attachment'
			AND
				pm.meta_key = '_thumbnail_id'";

		$query .= ' UNION ALL ';

			// Product Gallery for WooCommerce.

		$query .= " SELECT
				p.ID
			FROM
				{$wpdb->prefix}posts AS p			# attachment
			INNER JOIN
				{$wpdb->prefix}postmeta AS pm		# target CPT's postmeta
			ON
				FIND_IN_SET( p.ID, pm.meta_value )
			INNER JOIN
				{$wpdb->prefix}posts AS pp			# Target CPT
			ON
				pp.ID = pm.post_id
			WHERE
				pp.id IN ('" . implode( "','", $cpts_ids ) . "')
			AND
				p.post_type = 'attachment'
			AND
				pm.meta_key = '_product_image_gallery'";

		$attachments = $wpdb->get_col( $query );

		return $attachments;
	}

	/**
	 * Get Custom Post Types
	 *
	 * @return array array of cpt_slugs
	 */
	public static function get_cpts() {
		$pypass_cpts = array( Watermarks_Templates::$post_type_key, 'wp_template', 'wp_block', 'acf-field-group', 'acf-field', 'attachment', 'nav_menu_item', 'custom_css', 'product_variation', 'shop_order', 'shop_order_refund', 'shop_coupon' );
		return array_filter(
			get_post_types(
				array(
					'can_export' => true,
				)
			),
			function( $cpt_slug ) use ( $pypass_cpts ) {
				return ! in_array( $cpt_slug, $pypass_cpts );
			}
		);
	}

	/**
	 * Get CPT posts List
	 *
	 * @param string  $cpt
	 * @param integer $paged
	 * @return array
	 */
	public static function get_cpt_posts_for_bulk_apply( $cpt, $paged = 1 ) {
		$args = array(
			'post_type'                => $cpt,
			'posts_per_page'           => 20,
			'paged'                    => $paged,
			'no_found_rows'            => true,
			'update_post_meta_cache'   => false,
			'update_object_term_cache' => false,
			'order'                    => 'DESC',
		);

		$posts        = new \WP_Query( $args );
		$posts_result = array();

		if ( $posts->have_posts() ) {
			while ( $posts->have_posts() ) :
				$posts->the_post();
				$posts_result[ get_the_ID() ] = array(
					'id'        => get_the_ID(),
					'edit_link' => get_edit_post_link(),
					'title'     => _draft_or_post_title(),
					'date'      => get_the_date( 'Y/m/d' ),
					'isChecked' => false,
				);
			endwhile;
		}
		return $posts_result;
	}

	/**
	 * Get custom Post Types statuses
	 *
	 * @param string $cpt_slug
	 * @return array
	 */
	public static function get_cpt_statuses( $cpt_slug ) {
		global $wpdb;
		$statuses         = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT post_status FROM {$wpdb->prefix}posts WHERE post_type = '%s'", $cpt_slug ) );
		$auto_draft_index = array_search( 'auto-draft', $statuses, true );
		if ( false !== $auto_draft_index ) {
			unset( $statuses[ $auto_draft_index ] );
		}
		return $statuses;
	}

	/**
	 * Get Custom Post Types Posts'count.
	 *
	 * @param string $cpt_slug
	 * @return void
	 */
	public static function get_cpt_count( $cpt_slug ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}posts WHERE post_type = '%s'", $cpt_slug ) );
	}

		/**
		 * Date Start - End Options For CPT
		 *
		 * @param string $post_type
		 * @return void
		 */
	public static function select_date_options( $post_type = 'post' ) {
		global $wpdb, $wp_locale;
		$post_type  = sanitize_text_field( $post_type );
		$cpts_types = self::get_cpts();
		if ( empty( self::$cpts_date_options ) ) {
			$query = "SELECT DISTINCT
					YEAR( post_date ) AS year, MONTH( post_date ) AS month, post_type
				FROM
					$wpdb->posts
				WHERE
					post_type IN ('" . implode( "','", $cpts_types ) . "')
				AND
					post_status != 'auto-draft'
				ORDER BY
					post_date DESC";

			$months          = $wpdb->get_results( $query, \ARRAY_A );
			$filtered_months = array();
			foreach ( $months as $month_arr ) {
				$filtered_months[ $month_arr['post_type'] ][] = $month_arr;
			}
			self::$cpts_date_options = $filtered_months;
		}

		$months      = ! empty( self::$cpts_date_options[ $post_type ] ) ? self::$cpts_date_options[ $post_type ] : array();
		$month_count = ! empty( $months ) ? count( $months ) : 0;
		if ( ! $month_count || ( 1 == $month_count && 0 == $months[0]['month'] ) ) {
			return;
		}

		foreach ( $months as $date ) {
			if ( 0 == $date['year'] ) {
				continue;
			}
			$month = zeroise( $date['month'], 2 );
			echo wp_kses_post( '<option value="' . esc_html( $date['year'] ) . '-' . esc_html( $month ) . '">' . esc_html( $wp_locale->get_month( $month ) ) . ' ' . esc_html( $date['year'] ) . '</option>' );
		}
	}

		/**
		 * Authors Options For CPT.
		 *
		 * @param string $post_type
		 * @return void
		 */
	public static function select_authors_options( $post_type = 'post', $selected_authors = array(), $counter = 1 ) {
		global $wpdb;
		if ( empty( self::$cpts_author_options ) ) {
			$cpts_types = self::get_cpts();
			$query      =
				"SELECT
					u.ID, u.user_login, u.display_name, p.post_type
				FROM
					$wpdb->posts p
				INNER JOIN
					$wpdb->users u
				ON
					p.post_author = u.ID
				WHERE
					p.post_type IN ('" . implode( "','", $cpts_types ) . "')
				AND
					p.post_status != 'auto-draft'
				GROUP BY
					p.post_type, u.ID
				ORDER BY
					p.post_date DESC";

			$cpts_authors = $wpdb->get_results( $query, \ARRAY_A );
			foreach ( $cpts_authors as $author ) {
				self::$cpts_author_options[ $author['post_type'] ][] = $author;
			}
		}
		?>
		<select class="cpt-authors-select" data-cpt_type="<?php echo esc_attr( $post_type ); ?>" name="rule_group[<?php echo esc_attr( $post_type ); ?>][<?php echo esc_attr( $counter ); ?>][authors][]" multiple>
			<option value="">&mdash; <?php esc_html_e( 'Select' ); ?> &mdash;</option>
			<?php
			foreach ( (array) self::$cpts_author_options[ $post_type ] as $user ) :
				$display = sprintf( esc_html__( '%1$s', 'user dropdown' ), $user['display_name'] );
				?>
				<option <?php echo esc_attr( in_array( $user['ID'], $selected_authors ) ? 'selected="selected"' : '' ); ?> value="<?php echo esc_attr( $user['ID'] ); ?>"><?php echo esc_html( $display ); ?></option>
				<?php
			endforeach;
			?>
		</select>
		<?php
	}

	/**
	 * Filter Submitted Terms IDs.
	 *
	 * @param array $terms
	 * @return Array
	 */
	public static function filter_terms_ids( $terms, $cpt_names ) {
		$filtered_terms = array();
		foreach ( $terms as $cpt_name => $cpt_terms ) {
			if ( ! in_array( $cpt_name, $cpt_names ) ) {
				continue;
			}
			foreach ( $cpt_terms as $taxonomy_name => $taxonomy_terms ) {
				if ( empty( $taxonomy_terms ) ) {
					continue;
				}
				$taxonomy_terms = (array) $taxonomy_terms;
				$taxonomy_name  = sanitize_text_field( $taxonomy_name );
				$taxonomy_terms = array_unique( array_map( 'absint', array_map( 'sanitize_text_field', $taxonomy_terms ) ) );
				if ( empty( $taxonomy_terms ) ) {
					continue;
				}
				$filtered_terms[ $cpt_name ][ $taxonomy_name ] = $taxonomy_terms;
			}
		}

		return $filtered_terms;
	}

		/**
		 * Filtered Submitted CPT statuses
		 *
		 * @param array $cpt_names
		 * @param array $cpts_statuses
		 * @return array
		 */
	public static function filter_cpt_statuses( $cpt_names, $cpts_statuses ) {
		$new_cpt_names = array();
		foreach ( $cpt_names as $cpt_name ) :
			if ( ! empty( $cpts_statuses[ $cpt_name ] ) ) {
				$new_cpt_names[ $cpt_name ]['statuses'] = array_filter( array_map( 'sanitize_text_field', $cpts_statuses[ $cpt_name ] ) );
			} else {
				$new_cpt_names[ $cpt_name ]['statuses'] = array();
			}
		endforeach;

		return $new_cpt_names;
	}


	/**
	 * Filtered Submitted CPT Dates
	 *
	 * @param array $cpt_names
	 * @param array $cpts_start_dates
	 * @param array $cpts_startcpts_end_dates_dates
	 * @return array
	 */
	public static function filter_cpt_dates( $cpt_names, $cpts_start_dates, $cpts_end_dates ) {
		foreach ( $cpt_names as $cpt_name => $cpt_options_arr ) :
			if ( ! empty( $cpts_start_dates[ $cpt_name ] ) ) {
				$cpt_names[ $cpt_name ]['start_date'] = sanitize_text_field( $cpts_start_dates[ $cpt_name ] );
			} else {
				$cpt_names[ $cpt_name ]['start_date'] = '';
			}
		endforeach;

		foreach ( $cpt_names as $cpt_name => $cpt_options_arr ) :
			if ( ! empty( $cpts_end_dates[ $cpt_name ] ) ) {
				$cpt_names[ $cpt_name ]['end_date'] = sanitize_text_field( $cpts_end_dates[ $cpt_name ] );
			} else {
				$cpt_names[ $cpt_name ]['end_date'] = '';
			}
		endforeach;

		return $cpt_names;
	}


	/**
	 * Filtered Submitted CPT statuses
	 *
	 * @param array $cpt_names
	 * @param array $cpts_authors
	 * @return array
	 */
	public static function filter_cpt_authors( $cpt_names, $cpts_authors ) {
		foreach ( $cpt_names as $cpt_name => $cpt_options_arr ) :
			if ( ! empty( $cpts_authors[ $cpt_name ] ) ) {
				$cpt_names[ $cpt_name ]['authors'] = array_filter( array_map( 'absint', array_filter( $cpts_authors[ $cpt_name ] ) ) );
			} else {
				$cpt_names[ $cpt_name ]['authors'] = array();
			}
		endforeach;

		return $cpt_names;
	}
}
