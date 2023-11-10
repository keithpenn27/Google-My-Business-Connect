<?php 

// Don't allow direct access
if( ! isset( $args ) || empty( $args ) )
  return;

  $stars = '';

  for( $i = 1; $i <= $args->star_rating; $i++ ) {
    $stars .= '<i class="gmbc-star solid-star">&starf;</i>';
  }

  if( $args->star_rating < 5 ) {
    $dif = 5 - $args->star_rating;

    for( $i = 1; $i <= $dif; $i++ ) {
      $stars .= '<i class="gmbc-star empty-star">&starf;</i>';
    }
  }

?>

<div id="gmbc-review-<?php echo $args->id ?>" 
  class="gmbc-review <?php echo apply_filters( 'gmbc_review_classes', '', $args ) ?>">
    <div id="review-<?php echo $args->id ?>-reviewer" class="reviewer-photo">
      <img class="review-img" src="<?php echo $args->profile_photo_url ?>" loading="lazy" width="60px" height="60px" referrerpolicy="no-referrer" />
    </div>
    <div id="review-<?php echo $args->id ?>" class="review">
    <p id="review-<?php echo $args->id ?>-user-display-name" class="display-name">
        <?php echo $args->reviewer_display_name ?>
      </p>
      <span class="gmbc-star-rating"><?php echo $stars; echo ' ' . '<span class="review-time">' . gmbc_time_elapsed_string( $args->update_time ) . '</span>' ?></span>
      <br/>
      <span class="gmbc-rating">(<?php echo $args->star_rating ?>)</span>
      <p class="gmbc-review-comment">
        <?php echo $args->comment ?>
      </p>
    </div>
</div>
<?php

?>