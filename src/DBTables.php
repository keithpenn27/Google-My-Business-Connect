<?php 

namespace GMBConnect;

/**
 * The DBTables class handles all communication with the database tables.
 */
class DBTables {

  /**
   * Static string variable to hold the reviews table name.
   *
   * @var string
   */
  private static $reviews_table = 'gmbc_reviews';

  private static $locations_table = 'gmbc_locations';



  /**
   * Setup the necessary database tables.
   *
   * @return void
   */
  public static function setup_tables() {
    global $wpdb;

    $reviews_table = $wpdb->prefix . Self::$reviews_table;
    $locations_table = $wpdb->prefix . Self::$locations_table;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $reviews_table (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      endpoint_name TINYTEXT NOT NULL,
      review_id TINYTEXT NOT NULL,
      comment LONGTEXT NULL,
      star_rating VARCHAR(5) NOT NULL,
      update_time DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
      reviewer_display_name TEXT NOT NULL,
      profile_photo_url VARCHAR(255) NOT NULL,
      is_hidden BOOLEAN NOT NULL DEFAULT 0,
      PRIMARY KEY  (id)
    ) $charset_collate;
    CREATE TABLE $locations_table (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      name TINYTEXT NOT NULL,
      location_id TINYTEXT NOT NULL,
      title TINYTEXT NOT NULL,
      new_review_uri TINYTEXT NOT NULL,
      maps_uri TINYTEXT NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    \dbDelta( $sql );

  }

  /**
   * Insert synced reviews into the reviews db table.
   *
   * @param string $endpoint_name
   * @param string $review_id
   * @param string $comment
   * @param string $star_rating
   * @param string $update_time
   * @param string $display_name
   * @param string $profile_photo_url
   * @return void
   */
  public static function insert_reviews_table( $endpoint_name, $review_id, $comment, $star_rating, $update_time, $display_name, $profile_photo_url ) {
    global $wpdb;

    $reviews_table = $wpdb->prefix . Self::$reviews_table;

    $wpdb->insert(
      $reviews_table,
      array(
        'endpoint_name'         => $endpoint_name,
        'review_id'             => $review_id,
        'comment'               => $comment,
        'star_rating'           => $star_rating,
        'update_time'           => $update_time,
        'reviewer_display_name' => $display_name,
        'profile_photo_url'     => $profile_photo_url
      )
    );
  }

  /**
   * Update the reviews table when reviews are synced.
   *
   * @param string $endpoint_name
   * @param string $review_id
   * @param string $comment
   * @param string $star_rating
   * @param string $update_time
   * @param string $display_name
   * @param string $profile_photo_url
   * @return void
   */
  public static function update_reviews_table( $endpoint_name, $review_id, $comment, $star_rating, $update_time, $display_name, $profile_photo_url ) {
    global $wpdb;

    $reviews_table = $wpdb->prefix . Self::$reviews_table;

    $wpdb->update(
      $reviews_table,
      array(
        'endpoint_name'         => $endpoint_name,
        'review_id'             => $review_id,
        'comment'               => $comment,
        'star_rating'           => $star_rating,
        'update_time'           => $update_time,
        'reviewer_display_name' => $display_name,
        'profile_photo_url'     => $profile_photo_url
      ),
      [
        'review_id'     =>  $review_id
      ]
    );
  }

  /**
   * Check the reviews database table to see if a review already exists.
   *
   * @param string $review_id
   * @return void
   */
  public static function check_synced_reviews( $review_id ) {
    global $wpdb;

    $reviews_table = $wpdb->prefix . Self::$reviews_table;

    $sql = "SELECT review_id FROM $reviews_table WHERE review_id=%s";

    $result = $wpdb->get_results( $wpdb->prepare( $sql, $review_id ) );

    return ( ! empty( $result ) ) ? $result : false;
  }

  /**
   * Truncate the reviews table.
   *
   * @return boolean
   */
  public static function remove_reviews() {
    global $wpdb;

    $reviews_table = $wpdb->prefix . Self::$reviews_table;

    $sql = "TRUNCATE TABLE $reviews_table;";

    $removed = $wpdb->query( $sql );

    return $removed;
  }

  public static function insert_locations_table( $name, $title, $location_id, $new_review_uri, $maps_uri ) {
    global $wpdb;

    $locations_table = $wpdb->prefix . Self::$locations_table;

    $wpdb->insert(
      $locations_table,
      array(
        'name'                  => $name,
        'title'                 => $title,
        'location_id'           => $location_id,
        'new_review_uri'        => $new_review_uri,
        'maps_uri'              => $maps_uri
      )
    );
  }

  public static function update_locations_table( $name, $title, $location_id, $new_review_uri, $maps_uri ) {
    global $wpdb;

    $locations_table = $wpdb->prefix . Self::$locations_table;

    $wpdb->update(
      $locations_table,
      array(
        'name'                  => $name,
        'title'                 => $title,
        'location_id'           => $location_id,
        'new_review_uri'        => $new_review_uri,
        'maps_uri'              => $maps_uri
      ),
      [
        'location_id'     =>  $location_id
      ]
    );
  }

  public static function check_synced_locations( $location_id ) {
    global $wpdb;

    $locations_table = $wpdb->prefix . Self::$locations_table;

    $sql = "SELECT location_id FROM $locations_table WHERE location_id=%s";

    $result = $wpdb->get_results( $wpdb->prepare( $sql, $location_id ) );

    return ( ! empty( $result ) ) ? $result : false;
  }

  public static function remove_locations() {
    global $wpdb;

    $locations_table = $wpdb->prefix . Self::$locations_table;

    $sql = "TRUNCATE TABLE $locations_table;";

    $removed = $wpdb->query( $sql );

    return $removed;
  }

  public static function get_locations() {
    global $wpdb;

    $locations_table = $wpdb->prefix . Self::$locations_table;

    $sql = "SELECT * FROM $locations_table;";

    $result = $wpdb->get_results( $sql );

    if( empty( $result ) ) {
      $result = Locations::sync_locations();
    }

    return $result;
  }
}