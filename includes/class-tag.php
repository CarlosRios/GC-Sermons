<?php
/**
 * GC Sermons Tag
 *
 * @version 0.1.0
 * @package GC Sermons
 */

class GCS_Tag extends GCS_Taxonomies_Base {
	/**
	 * Parent plugin class
	 *
	 * @var class
	 * @since  0.1.0
	 */
	protected $plugin = null;

	/**
	 * Constructor
	 * Register Taxonomy. See documentation in Taxonomy_Core, and in wp-includes/taxonomy.php
	 *
	 * @since 0.1.0
	 * @param  object $plugin Main plugin object.
	 * @return void
	 */
	public function __construct( $plugin ) {
		parent::__construct( $plugin, array(
			'labels'     => array( __( 'Tag', 'gc-sermons' ), __( 'Tags', 'gc-sermons' ), 'gcs-tag' ),
			'args'       => array(),
			'post_types' => array( $plugin->sermons->post_type() )
		) );
	}

	/**
	 * Initiate our hooks
	 *
	 * @since 0.1.0
	 * @return void
	 */
	public function hooks() {
	}
}
