<?php
/**
 * GC Sermons Series Shortcode - Run
 *
 * @version 0.1.3
 * @package GC Sermons
 */

class GCSS_Series_Run extends GCS_Shortcodes_Base {

	/**
	 * The Shortcode Tag
	 * @var string
	 * @since 0.1.0
	 */
	public $shortcode = 'gc_series';

	/**
	 * Default attributes applied to the shortcode.
	 * @var array
	 * @since 0.1.0
	 */
	public $atts_defaults = array(
		'series_per_page'           => 10, // Will use WP's per-page option.
		'series_remove_dates'       => false,
		'series_remove_thumbnail'   => false,
		'series_thumbnail_size'     => 'medium',
		'series_number_columns'     => 2,
		'series_list_offset'        => 0,
		'series_wrap_classes'       => '',
		'series_remove_pagination'  => false,

		// No admin
		'series_remove_description' => true,
	);

	/**
	 * Shortcode Output
	 */
	public function shortcode() {
		$allterms = gc_sermons()->taxonomies->series->get_many( array( 'orderby' => 'sermon_date' ) );

		if ( empty( $allterms ) ) {
			return '';
		}

		$per_page    = (int) $this->att( 'series_per_page', get_option( 'posts_per_page' ) );
		$total_pages = round( count( $allterms ) / $per_page, 0, PHP_ROUND_HALF_UP );
		$page        = (int) get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
		$offset      = ( ( $page - 1 ) * $per_page ) + $this->att( 'series_list_offset', 0 );
		$allterms    = array_splice( $allterms, $offset, $per_page );
		// $this->shortcode_object->set_att( 'series_number_columns', 2 );
		// $this->shortcode_object->set_att( 'series_remove_thumbnail', false );

		if ( empty( $allterms ) ) {
			return '';
		}


		$args = $this->get_pagination( $total_pages );

		$args['terms']        = $this->add_year_index_and_augment_terms( $allterms );
		$args['remove_dates'] = $this->bool_att( 'series_remove_dates' );
		$args['wrap_classes'] = $this->get_wrap_classes();

		$content = '';
		$content .= GCS_Style_Loader::get_template( 'list-item-style' );
		$content .= GCS_Template_Loader::get_template( 'series-list', $args );

		return $content;
	}

	public function get_pagination( $total_pages ) {
		$nav = array( 'prev_link' => '', 'next_link' => '' );

		if ( ! $this->bool_att( 'series_remove_pagination' ) ) {
			$nav['prev_link'] = get_previous_posts_link( __( '<span>&larr;</span> Newer', 'gc-sermons' ), $total_pages );
			$nav['next_link'] = get_next_posts_link( __( 'Older <span>&rarr;</span>', 'gc-sermons' ), $total_pages );
		}

		return $nav;
	}

	public function get_wrap_classes() {
		$columns   = absint( $this->att( 'series_number_columns' ) );
		$columns   = $columns < 1 ? 1 : $columns;

		return $this->att( 'series_wrap_classes' ) . ' gc-' . $columns . '-cols gc-series-wrap';
	}

	public function add_year_index_and_augment_terms( $allterms ) {
		$terms = array();

		$do_date  = ! $this->bool_att( 'series_remove_dates' );
		$do_thumb = ! $this->bool_att( 'series_remove_thumbnail' );
		$do_desc  = ! $this->bool_att( 'series_remove_description' );

		foreach ( $allterms as $key => $term ) {
			$term = $this->get_term_data( $term );

			$term->do_image       = $do_thumb && $term->image;
			$term->do_description = $do_desc && $term->description;
			$term->url            = $term->term_link;

			$terms[ $do_date ? $term->year : 0 ][] = $term;
		}

		return $terms;
	}

	public function get_term_data( $term ) {
		return gc_sermons()->taxonomies->series->get( $term, array( 'image_size' => $this->att( 'series_thumbnail_size' ) ) );
	}

}
