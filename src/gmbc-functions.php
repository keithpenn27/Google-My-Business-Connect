<?php 

if( ! function_exists( 'gmbc_get_template_part' ) ) {

  /**
   * Retrieves a GMB Connect template file.
   *
   * @param string $slug
   * @param string $name Optional. Default null.
   * @param boolean $load
   * @param boolean $require_once Whether to require_once or require. Default is true.
   * @param array $args An array of arguments to pass to the template file. Make the values available inside the template file.
   * @return string The template file name if it's found.
   * 
   * @uses gmbc_locate_template
   * @uses load_template
   * @uses get_template_part
   */
  function gmbc_get_template_part( $slug, $name = null, $load = true, $require_once = true, $args = array() ) {

    // Execute code for this template part
    do_action( 'get_template_part_' . $slug, $slug, $name );

    // Get possible template parts
    $templates = array();
    if (isset($name)) {
      $templates[] = $slug . '-' . $name . '.php';
    }

    $templates[] = $slug . '.php';

    // Allow template parts to be filtered
    $templates = apply_filters( 'gmbc_get_template_part', $templates, $slug, $name );

    return gmbc_locate_template( $templates, $load, $require_once, $args );

  }
}

if( ! function_exists( 'gmbc_locate_template' ) ) {

  /**
   * Find the name of the highest priority template file that exists.
   *
   * Searches in the Child theme, then Parent theme, finally the GMB Connect plugin template dir.
   *
   * @param string[] $template_names Template file(s) to search for.
   * @param boolean $load If true, the template file will be loaded if it's found.
   * @param boolean $require_once Whether to require_once or require. Default is true
   * @return string The template file name if it's found.
   */
  function gmbc_locate_template( $template_names, $load = false, $require_once = true, $args ) {

    $located = false;

    // Loop through template names
    foreach( (array) $template_names as $template_name ) {

      if( empty( $template_name ) ) 
        continue;

      // Trim off slashes from template name
      $template_name = ltrim( $template_name, '/' );

      // Look in the child theme first
      if( file_exists( trailingslashit( get_stylesheet_directory() ) . 'gmbc/' . $template_name ) ) {
        $located = trailingslashit( get_stylesheet_directory() ) . 'gmbc/' . $template_name;
        break;

      // Look in the parent theme next
      } elseif ( file_exists( trailingslashit( get_template_directory() ) . 'gmbc/' . $template_name ) ) {
        $located = trailingslashit( get_template_directory() ) . 'gmbc/' . $template_name;
        break;

      // Finally, look in the plugin templates dir
      } elseif ( file_exists( trailingslashit( gmbc_get_template_dir() ) . $template_name ) ) {
        $located = trailingslashit( gmbc_get_template_dir() ) . $template_name;
        break;
      }
    }

    if( $load === true && ! empty( $located ) ) {
      load_template( $located, $require_once, $args );
    }

    return $located;

  }
}

if( ! function_exists( 'gmbc_get_template_dir' ) ) {

  /**
   * Returns the template directory for the GMB Connect plugin.
   *
   * @return string
   */
  function gmbc_get_template_dir() {
    $loader = \GMBConnect\Loader::get_instance();

    return trailingslashit( $loader::$plugin_path ) . 'src/template-parts';
  }

}

if( ! function_exists( 'gmbc_time_elapsed_string' ) ) {

  /**
   * Converts a UNIX timestamp into a "Time Ago" format
   *
   * @param string $datetime
   * @param boolean $full
   * @return string
   */
  function gmbc_time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
  }

}

if( ! function_exists( 'gmbc_get_review_pagination') ) {

  /**
   * Echos out the pagination links for the GMBC reviews.
   * Links are only echoed out if needed based on shortcode atts supplied.
   *
   * @param array $args
   * @return void
   */
  function gmbc_get_review_pagination( $args ) {

    $per_page = (int) $args['shortcode_args']['per_page'];

    $pages = (int) ceil( count( $args['reviews'] ) / $per_page );

    $current_page = ( isset( $_GET['reviews_page'] ) && ! empty( $_GET['reviews_page'] ) ) ? (int) $_GET['reviews_page'] : 1 ;
    $next_page = $current_page + 1;
    $previous_page = $current_page - 1;

    $prev_text = ( ! empty( $args['shortcode_args']['prev_link_text'] ) ) ? $args['shortcode_args']['prev_link_text'] : '<< Previous';
    $next_text = ( ! empty( $args['shortcode_args']['next_link_text'] ) ) ? $args['shortcode_args']['next_link_text'] : 'Next >>';

    $classes = 'gmbc reviews-pagination ' . $args['shortcode_args']['pagination_link_class'];
    $prev_classes = $classes . ' prev-link ' . $args['shortcode_args']['prev_link_class'];
    $next_classes = $classes . ' next-link ' . $args['shortcode_args']['next_link_class'];

    $links = '';

    if( $previous_page !== 0 ) {
      $links .= sprintf( '<a class="%s" href="%s">%s</a>', $prev_classes, esc_url( add_query_arg( 'reviews_page', $previous_page ) . '#gmbc-reviews-wrapper' ), $prev_text );
    }

    if( $current_page !== $pages ) {
      $links .= sprintf( '<a class="%s" href="%s">%s</a>', $next_classes, esc_url( add_query_arg( 'reviews_page', $next_page ) . '#gmbc-reviews-wrapper' ), $next_text );
    }

    echo $links;
  }
}

if( ! function_exists( 'gmbc_get_current_reviews') ) {

  /**
   * Get the current reviews based on shortcode atts and pagination render the reviews.php template.
   *
   * @param array $args
   * @return void
   */
  function gmbc_get_current_reviews( $args ) {

    $per_page = (int) $args['shortcode_args']['per_page'];

    $pages = (int) ceil( count( $args['reviews'] ) / $per_page );

    $current_page = ( isset( $_GET['reviews_page'] ) && ! empty( $_GET['reviews_page'] ) ) ? (int) $_GET['reviews_page'] : 1 ;

    $start_ind = ( $current_page * $per_page ) - $per_page;

    $reviews = [];

      for( $i = $start_ind; $i < ( $current_page * $per_page ); $i++ ) {        
        if( isset( $args['reviews'][$i] ) ) {
          gmbc_get_template_part( 'review', '', true, false, $args['reviews'][$i] );
          $reviews[] = $args['reviews'][$i];
        }
      
    }


    // Get the reviews schema
    $schema = GMBConnect\Reviews::build_reviews_schema( $reviews );
    $schema = json_encode( $schema );
    $output = '<script type="application/ld+json">';
    $output .=  $schema;
    $output .= '</script>';

    add_action( 'wp_footer', function() use ( $output ) {
      echo $output;
    });

  }
}

if( ! function_exists( 'gmbc_get_stars' ) ) {

  /**
   * Get the stars output for the GMBC reviews template heading.
   *
   * @param array $args
   * @return string
   */
  function gmbc_get_stars( $args ) {

    $stars = '';

    $rounded = round( $args['review_settings']['average_rating'] );
 
    for( $i = 1; $i <= $rounded; $i++ ) {
      $stars .= '<i class="gmbc-star solid-star">&starf;</i>';
    }
  
    if( $rounded < 5 ) {
      $dif = 5 - $rounded;
  
      for( $i = 1; $i <= $dif; $i++ ) {
        $stars .= '<i class="gmbc-star empty-star">&starf;</i>';
      }
    }

    return $stars;

  }

}

