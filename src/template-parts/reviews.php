<?php 
  
  // Don't allow direct access
  if( ! isset( $args['reviews'] ) || empty( $args['reviews'] ) )
   return;

  $location_id = array_map( 'trim', explode( '/', $args['location_settings']['location_name'] ) );

  foreach( \GMBConnect\DBTables::get_locations() as $key => $location ) {
    if( $args['location_settings']['location_name'] === $location->name ) {
      $review_link = $location->new_review_uri;
      $heading     = $location->title;
      $map_uri     = $location->maps_uri;
      break;
    }
  }

?>

<section id="gmbc-reviews-section-<?php echo $location_id[1] ?>" class="gmbc gmbc-section gmbc-reviews-section">
  <div id="gmbc-reviews-wrapper" class="gmbc-reviews <?php echo trim( $args['shortcode_args']['class'] ) ?>">
  <div class="gmbc-reviews-header">
    <div class="reviews-brand">
      <img class="reviews-logo" width="75px" height="75px" loading="lazy" src="<?php echo \GMBConnect\Loader::$assets_url . '/google_logo.png' ?>" />
    </div>
    <div class="gmbc-heading-info">
      <h2 class="gmbc-reviews-heading"><?php echo $heading ?></h2>
        <span class="average-rating"><?php echo $args['review_settings']['average_rating'] ?></span>
        <span class="average-rating-stars"><?php echo gmbc_get_stars( $args ) ?></span>
      <a class="map-link" href="<?php echo $map_uri ?>" target="_blank">
        <span class="total-reviews"><?php echo $args['review_settings']['total_review_count'] ?> reviews</span>
      </a>
    </div>
  </div>
  <?php gmbc_get_current_reviews( $args ) ?>
  <div class="gmbc-reviews-footer">
    <a class="write-reviews-link <?php echo $args['shortcode_args']['reviews_link_class'] ?>" 
        href="<?php echo $review_link ?>" target="_blank">
          <?php echo ( ! empty( $args['shortcode_args']['reviews_link_text'] ) ) ? $args['shortcode_args']['reviews_link_text'] : 'Write a Review' ?>
        </a>
    </div>
    <div class="pagination-links">
      <?php gmbc_get_review_pagination( $args ) ?>
      </div>
  </div>
</section>