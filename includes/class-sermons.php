<?php
/**
 * GC Sermons Sermons
 *
 * @version 0.1.0
 * @package GC Sermons
 */

class GCS_Sermons extends GCS_Post_Types_Base {

	/**
	 * Parent plugin class
	 *
	 * @var class
	 * @since  0.1.0
	 */
	protected $plugin = null;

	/**
	 * Bypass temp. cache
	 *
	 * @var boolean
	 * @since  0.1.0
	 */
	public $flush = false;

	/**
	 * Default WP_Query args
	 *
	 * @var   array
	 * @since 0.1.0
	 */
	protected $query_args = array(
		'post_type'      => 'THIS(REPLACE)',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'no_found_rows'  => true,
	);

	/**
	 * Constructor
	 * Register Custom Post Types. See documentation in CPT_Core, and in wp-includes/post.php
	 *
	 * @since  0.1.0
	 * @param  object $plugin Main plugin object.
	 * @return void
	 */
	public function __construct( $plugin ) {
		// Register this cpt
		// First parameter should be an array with Singular, Plural, and Registered name.
		parent::__construct( $plugin, array(
			'labels' => array( __( 'Sermon', 'gc-sermons' ), __( 'Sermons', 'gc-sermons' ), 'gc-sermons' ),
			'args' => array(
				'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
				'menu_icon' => 'dashicons-playlist-video',
			),
		) );
		$this->query_args['post_type'] = $this->post_type();
	}

	/**
	 * Initiate our hooks
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function hooks() {
		add_action( 'cmb2_admin_init', array( $this, 'fields' ) );
		add_action( 'dbx_post_advanced', array( $this, 'remove_excerpt_box' ) );
		add_filter( 'cmb2_override_excerpt_meta_value', array( $this, 'get_excerpt' ), 10, 2 );
		add_filter( 'cmb2_override_excerpt_meta_save', '__return_true' );
	}

	public function remove_excerpt_box() {
		remove_meta_box( 'postexcerpt', null, 'normal' );
	}

	/**
	 * Add custom fields to the CPT
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function fields() {
		$this->new_cmb2( array(
			'id'           => 'gc_sermon_metabox',
			'title'        => __( 'Sermon Details', 'gc-sermons' ),
			'object_types' => array( $this->post_type() ),
			'fields'       => array(
				'gc_sermon_video_url' => array(
					'id'   => 'gc_sermon_video_url',
					'name' => __( 'Video URL', 'gc-sermons' ),
					'desc' => __( 'Enter a youtube, or vimeo URL. Supports services listed at <a href="http://codex.wordpress.org/Embeds">http://codex.wordpress.org/Embeds</a>.', 'cmb2' ),
					'type' => 'oembed',
				),
				'gc_sermon_video_src' => array(
					'id'      => 'gc_sermon_video_src',
					'name'    => __( 'Video File', 'gc-sermons' ),
					'desc'    => __( 'Alternatively upload/select video from your media library.', 'gc-sermons' ),
					'type'    => 'file',
					'options' => array( 'url' => false ),
				),
				'gc_sermon_audio_url' => array(
					'id'   => 'gc_sermon_audio_url',
					'name' => __( 'Audio URL', 'gc-sermons' ),
					'desc' => __( 'Enter a soundcloud, spotify, or other oembed-supported web audio URL. Supports services listed at <a href="http://codex.wordpress.org/Embeds">http://codex.wordpress.org/Embeds</a>.', 'cmb2' ),
					'type' => 'oembed',
				),
				'gc_sermon_audio_src' => array(
					'id'      => 'gc_sermon_audio_src',
					'name'    => __( 'Audio File', 'gc-sermons' ),
					'desc'    => __( 'Alternatively upload/select audio from your media library.', 'gc-sermons' ),
					'type'    => 'file',
					'options' => array( 'url' => false ),
				),
				'excerpt' => array(
					'id'   => 'excerpt',
					'name' => __( 'Excerpt', 'gc-sermons' ),
					'desc' => __( 'Excerpts are optional hand-crafted summaries of your content that can be used in your theme. <a href="https://codex.wordpress.org/Excerpt" target="_blank">Learn more about manual excerpts.</a>' ),
					'type' => 'textarea',
					'escape_cb' => false,
				),
				'gc_sermon_notes' => array(
					'id'   => 'gc_sermon_notes',
					'name' => __( 'Sermon Questions', 'gc-sermons' ),
					'type' => 'wysiwyg',
				),
			),
		) );

	}

	public function get_excerpt( $data, $post_id ) {
		return get_post_field( 'post_excerpt', $post_id );
	}

	/**
	 * Registers admin columns to display. Hooked in via CPT_Core.
	 *
	 * @since  0.1.0
	 * @param  array $columns Array of registered column names/labels.
	 * @return array          Modified array
	 */
	public function columns( $columns ) {
		$new_column = array(
		);
		return array_merge( $new_column, $columns );
	}

	/**
	 * Handles admin column display. Hooked in via CPT_Core.
	 *
	 * @since  0.1.0
	 * @param array $column  Column currently being rendered.
	 * @param int   $post_id ID of post to display column for.
	 */
	public function columns_display( $column, $post_id ) {
		switch ( $column ) {
		}
	}

	/**
	 * Retrieve the most recent sermon with video media.
	 *
	 * @since  0.1.0
	 *
	 * @return GCS_Sermon_Post|false  GC Sermon post object if successful.
	 */
	public function most_recent_with_video() {
		static $sermon = null;

		if ( null === $sermon || $this->flush ) {
			$sermon = $this->most_recent();

			if ( empty( $sermon->media['video'] ) ) {
				$sermon = $this->most_recent_with_media( 'video' );
			}
		}

		return $sermon;
	}

	/**
	 * Retrieve the most recent sermon with audio media.
	 *
	 * @since  0.1.0
	 *
	 * @return GCS_Sermon_Post|false  GC Sermon post object if successful.
	 */
	public function most_recent_with_audio() {
		static $sermon = null;

		if ( null === $sermon || $this->flush ) {
			$sermon = $this->most_recent();

			if ( empty( $sermon->media['video'] ) ) {
				$sermon = $this->most_recent_with_media( 'video' );
			}
		}

		return $sermon;
	}

	/**
	 * Retrieve the most recent sermon.
	 *
	 * @since  0.1.0
	 *
	 * @return GCS_Sermon_Post|false  GC Sermon post object if successful.
	 */
	public function most_recent() {
		static $sermon = null;

		if ( null === $sermon || $this->flush ) {
			$sermons = new WP_Query( apply_filters( 'gcs_recent_sermon_args', $this->query_args ) );
			$sermon = false;
			if ( $sermons->have_posts() ) {
				$sermon = new GCS_Sermon_Post( $sermons->posts[0] );
			}
		}

		return $sermon;
	}

	/**
	 * Retrieve the most recent sermon with audio media.
	 *
	 * @since  0.1.0
	 *
	 * @param  string  $type Media type (audio or video)
	 *
	 * @return GCS_Sermon_Post|false  GC Sermon post object if successful.
	 */
	protected function most_recent_with_media( $type = 'video' ) {
		$sermon = false;

		// Only audio/video allowed.
		$type = 'video' === $type ? $type : 'audio';

		$args = $this->query_args;
		$args['meta_query'] = array(
			'relation' => 'OR',
			array(
				'key' => "gc_sermon_{$type}_url",
			),
			array(
				'key' => "gc_sermon_{$type}_src",
			),
		);

		$sermons = new WP_Query( apply_filters( "gcs_recent_sermon_with_{$type}_args", $args ) );

		if ( $sermons->have_posts() ) {
			$sermon = new GCS_Sermon_Post( $sermons->posts[0] );
		}

		return $sermon;
	}

	/**
	 * Retrieve the most recent sermon which has terms in specified taxonomy.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $taxonomy Taxonomy slug
	 *
	 * @return GCS_Sermon_Post|false  GC Sermon post object if successful.
	 */
	public function most_recent_with_taxonomy( $taxonomy ) {
		$sermon = $this->plugin->sermons->most_recent();

		// No sermon post found at all.. oops
		if ( ! $sermon ) {
			return false;
		}

		try {
			$terms = $sermon->{$taxonomy};
		} catch ( Exception $e ) {
			return new WP_Error( __( '"%s" is not a valid taxonomy for %s.', 'gc-sermons' ), $taxonomy, $this->post_type( 'plural' ) );
		}

		if ( ! $terms || is_wp_error( $terms ) ) {
			$sermon = $this->find_sermon_with_taxonomy( $taxonomy, array( $sermon->ID ) );
		}

		return $sermon;
	}

	/**
	 * Searches for posts which have terms in a given taxonomy, while excluding previous tries.
	 *
	 * @since  0.1.0
	 *
	 * @param  string  $taxonomy Taxonomy slug
	 * @param  array   $exclude  Array of excluded post IDs
	 *
	 * @return GCS_Sermon_Post|false  GC Sermon post object if successful.
	 */
	protected function find_sermon_with_taxonomy( $taxonomy, $exclude ) {
		static $count = 0;

		$args = $this->query_args;
		$args['post__not_in'] = $exclude;
		$args = apply_filters( 'gcs_find_sermon_with_taxonomy_args', $args );

		$sermons = new WP_Query( $args );

		if ( ! $sermons->have_posts() ) {
			return false;
		}

		$sermon = new GCS_Sermon_Post( $sermons->posts[0] );

		$terms = $sermon ? $sermon->{$taxonomy} : false;

		if ( ! $terms || is_wp_error( $terms ) ) {
			// Only try this up to 5 times
			if ( ++$count > 5 ) {
				return false;
			}

			$exclude = array_merge( $exclude, array( $sermon->ID ) );
			$terms = $this->find_sermon_with_taxonomy( $taxonomy, $exclude );
		}

		return $terms;
	}


}
