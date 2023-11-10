<?php 

namespace GMBConnect;

/**
 * The Reviews class handles the backend functionality for syncing and saving the reviews to the database.
 */
class Reviews {

  /**
   * Construct to object.
   */
  function __construct() {
    $this->hooks();
    $this->build_aggregate_reviews_schema();
  }

  /**
   * Set up the necessary hooks for the Reviews class.
   *
   * @return void
   */
  private function hooks() {

    // Action hooks
    \add_action( 'gmbc_reviews_sync_cron', [ $this, 'sync_reviews'] );
    \add_action( 'wp_ajax_sync_reviews', [ $this, 'sync_reviews' ] );
    \add_action( 'update_option_gmbc_reviews', [ $this, 'reviews_sync_cron_job' ], 10, 2 );
    \add_action( 'update_option_gmbc_locations', [ $this, 'gmbc_location_changed'], 10, 2 );

  }

  /**
   * Schedules the reviews sync cron job based on the Update Frequency setting.
   *
   * @param string[] $old_value
   * @param string[] $value
   * @return void
   */
  public function reviews_sync_cron_job( $old_value, $value ) {

    // If the update frequency setting has been changed, unschedule the cron event and reschedule.
    if ( isset( $old_value['update_frequency'] ) && $old_value['update_frequency'] !== $value['update_frequency'] ) {

      if( \wp_next_scheduled( 'gmbc_reviews_sync_cron' ) ) {

        $event = \wp_get_scheduled_event( 'gmbc_reviews_sync_cron' );
        \wp_unschedule_event( $event->timestamp, $event->hook );
        
      }

      \wp_schedule_event( \current_time( 'timestamp' ), $value['update_frequency'], 'gmbc_reviews_sync_cron' );
    }

  }

  /**
   * Hooked to the update_option action hook. Truncates the reviews db table if the location_name setting is changed.
   *
   * @param string[] $old_value
   * @param string[] $value
   * @return void
   */
  public function gmbc_location_changed( $old_value, $value ) {

    if( $old_value !== null && isset( $old_value['location_name'] ) && ! empty( $old_value['location_name'] ) && $old_value['location_name'] !== $value['location_name'] ) {
      $removed = DBTables::remove_reviews();
    }
  }

  public static function build_reviews_schema( $reviews ) {
    $business_info = \get_option( 'business_info' );

    $schema = [];

    foreach( $reviews as $key => $review ) {
      $schema[$key]['@context'] = 'https://schema.org';
      $schema[$key]['@type'] = 'Review';
      $schema[$key]['itemReviewed']['@type'] = 'LocalBusiness';
      $schema[$key]['itemReviewed']['address']['@type'] = 'PostalAddress';
      $schema[$key]['reviewRating']['@type'] = 'Rating';
      $schema[$key]['author']['@type'] = 'Person';
      $schema[$key]['itemReviewed']['image'] = $business_info['logo_url'];
      $schema[$key]['itemReviewed']['name']  = \get_bloginfo( 'name' );
      $schema[$key]['itemReviewed']['priceRange']  = $business_info['price_range'];
      $schema[$key]['itemReviewed']['telephone']   = $business_info['phone'];
      $schema[$key]['itemReviewed']['address']['streetAddress']  = $business_info['street_address'];
      $schema[$key]['itemReviewed']['address']['addressLocality'] = $business_info['city'];
      $schema[$key]['itemReviewed']['address']['addressRegion']  = $business_info['state'];
      $schema[$key]['itemReviewed']['address']['postalCode'] = $business_info['zip'];
      $schema[$key]['itemReviewed']['address']['addressCountry'] = $business_info['country'];
      $schema[$key]['author']['name'] = $review->reviewer_display_name;
      $schema[$key]['reviewRating']['ratingValue'] = (string) $review->star_rating;
      $schema[$key]['reviewRating']['bestRating'] = '5';
      $schema[$key]['reviewRating']['worstRating'] = '1';

      if( $review->comment != null ) {
        $schema[$key]['reviewBody'] = $review->comment;
      }
    }

    return $schema;

  }


  /**
   * Gets the reviews from the Google My Business API and save them to the database. This function is used by the "Sync" button,
   * the gmbc_reviews_sync_cron, and the sync_reviews ajax action.
   *
   * @param string $rating_min
   * @param string $reviews_max
   * @param boolean $is_ajax
   * @return void
   */
  public function sync_reviews( $rating_min = null, $reviews_max = null, $is_ajax = false ) {

    if( isset( $_POST['is_ajax'] ) ) {
      // This is an ajax request
      $is_ajax = ( bool ) $_POST['is_ajax'];
    }

    // Get the default settings
    $default_settings = \get_option( 'gmbc_locations' );
    $location = $default_settings['location_name'];
    
    // TODO Check if we can remove these params and the condition.
    if( $rating_min == null || $reviews_max == null ) {

      $rating_min = ( $rating_min == null && isset( $default_settings['rating_min'] ) ) ? 
        $default_settings['rating_min'] : $rating_min;

      $reviews_max = ( $reviews_max == null && isset( $default_settings['reviews_max'] ) ) ? 
        $default_settings['reviews_max'] : $reviews_max;
    }
    

    // Get the client from the Loader instance.
    $client = Loader::get_client();

    // Try building our requests to get reviews for the selected location from the Google API.
    try {

      $account_management = new \Google_Service_MyBusinessAccountManagement($client);

      $gmb_accounts = $account_management->accounts->listAccounts()->getAccounts();
      $parent = $gmb_accounts[0]->name . '/' . $location;
  
      $gmb = new \Google_Service_MyBusiness($client);

      $reviews = [];

      $gmb_reviews = $gmb->accounts_locations_reviews->listAccountsLocationsReviews( $parent );

      $gmb_locations = Locations::sync_locations();
      

        
      // While we still have more pages of reviews, get them and build the array.
      while( count( $reviews ) < $gmb_reviews->totalReviewCount ) {

        $reviews = array_merge( $reviews, $gmb_reviews->reviews );

        $gmb_reviews = $gmb->accounts_locations_reviews->listAccountsLocationsReviews( $parent, [ 'pageToken' => $gmb_reviews->nextPageToken ] );

      }
      
      // Loop through the returned reviews.
      foreach( $reviews as $key => $review ) {

        $review_exists = DBTables::check_synced_reviews( $review->reviewId );

        $the_rating;
    
        switch ( $review->starRating ) {
          case 'ONE':
            $the_rating = 1;
            break;
    
          case 'TWO':
            $the_rating = 2;
            break;
    
          case 'THREE':
            $the_rating = 3;
            break;
    
          case 'FOUR':
            $the_rating = 4;
            break;
    
          case 'FIVE':
            $the_rating = 5;
            break;
        }

        // If the review already exists in our database, update it. If not, add it.
        if( $review_exists ) {

          // Review exists, so update it.
          DBTables::update_reviews_table( 
            $review->name, 
            $review->reviewId, 
            $review->comment, 
            $the_rating, 
            $review->updateTime, 
            $review->reviewer->displayName, 
            $review->reviewer->profilePhotoUrl 
          );
          
        } else {

          // Add the review to the database.
          DBTables::insert_reviews_table( 
            $review->name, 
            $review->reviewId, 
            $review->comment, 
            $the_rating, 
            $review->updateTime, 
            $review->reviewer->displayName, 
            $review->reviewer->profilePhotoUrl 
          );

        }

      }

      // The ajax response.
      $response['message'] = "GMB Connect reviews have been synced.";

    } catch ( \Google_Service_Exception $error ) {

      // Error while building our API requests.
      $response['message'] = "There was an error while syncing the reviews. Please check your Client Credentials and try again.";
      $response['errors']   = $error->getErrors();
    }

    // If we made it here, update settings in the gmbc_settings option.
    $settings = \get_option( 'gmbc_reviews' );
    $settings['last_synced_on'] = \current_time( 'Y-m-d H:i:s' );
    $settings['average_rating'] = round( $gmb_reviews->averageRating, 1 );
    $settings['total_review_count'] = $gmb_reviews->totalReviewCount;

    \update_option( 'gmbc_reviews', $settings );

    // If this was an ajax request, send the response back.
    if( $is_ajax ) {

      \wp_send_json( $response );

      \wp_die();

    }
    
  }

  private function build_aggregate_reviews_schema() {

    $business_info = \get_option( 'business_info' );

    $reviews_settings = \get_option( 'gmbc_reviews' );

    $aggregate_schema = [
      '@context' => 'https://schema.org/',
      '@type'    => 'AggregateRating',
      'itemReviewed' => [
        '@type'       => 'LocalBusiness',
        'image'       => $business_info['logo_url'],
        'name'        => get_bloginfo( 'name' ),
        'priceRange'  => $business_info['price_range'],
        'telephone'   => $business_info['phone'],
        'address'    => [
          '@type'     => 'PostalAddress',
          'streetAddress' => $business_info['street_address'],
          'addressLocality' => $business_info['city'],
          'addressRegion'   => $business_info['state'],
          'postalCode'      => $business_info['zip'],
          'addressCountry'  => $business_info['country']
        ]
      ],
      'ratingValue'   => (string) ( isset( $reviews_settings['average_rating'] ) && ! empty( $reviews_settings['average_rating'] ) ) ? $reviews_settings['average_rating'] : '',
      'bestRating'    => '5',
      'ratingCount'   => (string) ( isset( $reviews_settings['total_review_count'] ) && ! empty( $reviews_settings['total_review_count'] ) ) ? $reviews_settings['total_review_count'] : ''
    ];

    $aggregate_schema = json_encode( $aggregate_schema );

    $output = '<script type="application/ld+json">';
    $output .=  $aggregate_schema;
    $output .= '</script>';

    add_action( 'wp_footer', function() use ( $output ) {
      echo $output;
    });

  }

}
