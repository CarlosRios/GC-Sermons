<?php
/**
 * GC Sermons Recent Series Shortcode
 *
 * @version 0.1.3
 * @package GC Sermons
 */

class GCSS_Recent_Series_Run extends GCS_Shortcodes_Base {

	/**
	 * The Shortcode Tag
	 * @var string
	 * @since 0.1.0
	 */
	public $shortcode = 'gc_recent_series';

	/**
	 * Default attributes applied to the shortcode.
	 * @var array
	 * @since 0.1.0
	 */
	public $atts_defaults = array(
		'sermon_id'                 => 0, // 'Blank, "recent", or "0" will play the most recent video.
		'sermon_recent'             => 'recent', // Options: 'recent', 'audio', 'video'
		'series_remove_thumbnail'   => true,
		'series_thumbnail_size'     => 'medium',

		// No admin
		'series_remove_description' => true,
		'series_wrap_classes'       => '',
	);

	/**
	 * Shortcode Output
	 */
	public function shortcode() {
		$args = array();
		foreach ( $this->atts_defaults as $key => $default_value ) {
			$args[ str_replace( 'series_', '', $key ) ] = is_bool( $this->atts_defaults[ $key ] )
				? $this->bool_att( $key, $default_value )
				: $this->att( $key, $default_value );
		}

		$args['wrap_classes'] .= ' gc-recent-series';

		return gc_get_sermon_series_info( $this->get_sermon(), $args );
	}

}