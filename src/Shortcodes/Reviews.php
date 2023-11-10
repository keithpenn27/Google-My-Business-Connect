<?php 

namespace GMBConnect\Shortcodes;

/**
 * Extends the Shortcode class to build shortcodes to display reviews.
 *
 * 
 */
class Reviews extends Shortcode {

  /**
   * $tag_name Required. The shortcode tag name.
   *
   * @var string
   */
  protected $tag_name = 'gmbc_reviews';

  /**
   * $allowed_atts Required. An associative array of allowed shortcode attributes.
   *
   * @var string[]
   */
  protected $allowed_atts = [
    'max_reviews'           => null,
    'min_rating'            => null,
    'per_page'              => null,
    'id'                    => '',
    'class'                 => '',
    'reviews_link_class'    => '',
    'pagination_link_class' => '',
    'next_link_class'       => '',
    'prev_link_class'       => '',
    'next_link_text'        => '',
    'prev_link_text'        => '',
    'reviews_link_text'     => ''
  ];

  /**
   * $db_table The name of the database table to select data from.
   *
   * @var string
   */
  private static $db_table = 'gmbc_reviews';

  /**
   * Construct the Reviews shortcode class.
   */
  function __construct() {
    $options = \get_option( 'gmbc_reviews' );

    // Assign the default vals from the settings page
    $this->allowed_atts['max_reviews'] = ( isset( $options['reviews_max'] ) && ! empty( $options['reviews_max'] ) ) ? $options['reviews_max'] : '';
    $this->allowed_atts['min_rating']  = ( isset( $options['rating_min'] ) && ! empty( $options['rating_min'] ) ) ? $options['rating_min'] : '';
    $this->allowed_atts['per_page']    = ( isset( $options['reviews_per_page'] ) && ! empty( $options['reviews_per_page'] ) ) ? $options['reviews_per_page'] : '';

    // Finish constructing
    parent::__construct();
  }

  /**
   * Required. This method must be overridden from the abstract Shortcode class. This is the callback for add_shortcode.
   *
   * @param string[] $atts The user added attributes passed from add_shortcode.
   * @param string $content The content between the shortcode tags. Passed by add_shortcode.
   * @param [type] $shortcode_tag The tag name of the shortcode. Passed by add_shortcode.
   * @return string The output of the shortcode.
   */
  public function execute( $atts, $content, $shortcode_tag ) {

    // Combine the user atts with the allowed atts 
    $attributes = \shortcode_atts( $this->allowed_atts, $atts, $shortcode_tag );

    // Get the query results
    $results = $this->_get_reviews( $attributes );

    // Merge the query results with the shortcode args. We pass them all into gmbc_get_template_part for use in the template
    $args = array_merge( $results, [ 'shortcode_args' => $attributes ] );

    ob_start();

    // Load the reviews template file
    gmbc_get_template_part( 'reviews', '', true, true, $args );

    return ob_get_clean();
  }

  /**
   * Builds the reviews query to display the shortcode output.
   *
   * @param string[] $atts The combined user attributes and allowed attributes.
   * @return array The returned query results.
   */
  private function _get_reviews( $atts ) {
    global $wpdb;

    // Full table name
    $db_table = $wpdb->prefix . Self::$db_table;
    
    // Limit based on either reviews settings or user shortcode atts
    $limit = ( isset( $atts['max_reviews'] ) && trim( strtolower( $atts['max_reviews'] ) ) === 'all') ? '' : 'LIMIT ' . $atts['max_reviews'];

    // Get reviews by min_rating from reviews settings or user shortcode atts. Only reviews that are not hidden.
    $where = 'WHERE star_rating >= ' . $atts['min_rating'] . ' AND is_hidden = 0';
    
    // If user shortcode atts includes ids, modify the where clause.
    if( isset( $atts['id'] ) && ! empty( $atts['id'] ) ) {
      $ids = array_map( 'trim', explode(',', $atts['id'] ) );

      $where .= ' AND ID = ';

      for( $i = 0; $i < count( $ids ); $i++ ) {
        $where .= $ids[$i];

        if( $i !== count( $ids ) - 1 ) {
          $where .= ' OR ID = ';
        }
      }

    }

    $sql = sprintf( "SELECT * FROM %s %s %s", $db_table, $where, $limit );

    // Get the results from the database
    $reviews = $wpdb->get_results( $sql ); 
        
    $review_settings = \get_option( 'gmbc_reviews' );

    $location_settings = \get_option( 'gmbc_locations' );
    
    // Merge the GMB Conect reviews settings with the reviews results, so we have them in the template file.
    $results = array_merge( [ 'review_settings' => $review_settings, 'location_settings' => $location_settings, 'reviews' => $reviews ] );

    return $results;
  }
}