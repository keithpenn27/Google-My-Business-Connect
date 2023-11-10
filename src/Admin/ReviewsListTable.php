<?php 

namespace GMBConnect\Admin;
use GMBConnect\Libs\WP_List_Table;

/**
 * This class builds the reviews admin list table.
 */
class ReviewsListTable extends \GMBConnect\Libs\WP_List_Table {

  /**
   * Private static variable to hold the reviews table name.
   *
   * @var string
   */
  private static $reviews_table = 'gmbc_reviews';

  /**
   * Override the get_columns method and add our columns.
   *
   * @return string[]
   */
  public function get_columns() {

    $table_cols = [
      'cb'                      =>  '<input type="checkbox" />',
      'profile_photo_url'       =>  \esc_html__( 'Profile Photo', 'gmbconnect' ),
      'reviewer_display_name'   =>  \esc_html__( 'Reviewer Name', 'gmbconnect' ),
      'comment'                 =>  \esc_html__( 'Review Comment', 'gmbconnect' ),
      'star_rating'             =>  \esc_html__( 'Star Rating', 'gmbconnect' ),
      'update_time'             =>  \esc_html__( 'Updated On', 'gmbconnect' ),
      'ID'                      =>  \esc_html__( 'ID', 'gmbconnect' ),
      'is_hidden'               =>  ''
    ];

    return $table_cols;

  }

  /**
   * Override the no_items method to display our message when the reviews are empty.
   *
   * @return void
   */
  public function no_items() {
    if( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ) {
      \esc_html_e( 'No reviews match your search.', 'gmbconnect' );
    } else {
      \esc_html_e( 'There are no reviews to display. If the location you chose in the GMB Settings page has reviews on Google My Business, try syncing the reviews by clicking the button above.', 'gmbconnect' );
    }
  }

  /**
   * Override the prepare_items method to customize our output. Set up pagination, as well.
   *
   * @return void
   */
  public function prepare_items() {

    // Look for search query 
    $reviews_search_key = ( isset( $_REQUEST['s'] ) ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';

    // Get the columns
    $this->_column_headers = $this->get_column_info();

    // Handle single and bulk actions
    $this->handle_table_actions();

    // Get the table data
    $table_data = $this->get_table_data();

    if( $reviews_search_key ) {

      // There is a search query, so filter the results
      $table_data = $this->filter_table_data( $table_data, $reviews_search_key );
    }

    // Assign the items from the table data
    $this->items = $table_data;

    $reviews_per_page = $this->get_items_per_page( 'edit_post_per_page' );

    $table_page = $this->get_pagenum();

    // Slice array to display the correct page data
    $this->items = array_slice( $table_data, ( ( $table_page - 1 ) * $reviews_per_page ), $reviews_per_page );

    $total_reviews = count( $table_data );

    // Set the pagination args
    $this->set_pagination_args( array (
      'total_items' => $total_reviews,
      'per_page'    => $reviews_per_page,
      'total_pages' => ceil( $total_reviews/$reviews_per_page )
    ) );

  }

  /**
   * Filters the table data based on current search.
   *
   * @param array[] $table_data
   * @param string $search_key
   * @return array[]
   */
  public function filter_table_data( $table_data, $search_key ) {

    $filtered_table_data = array_values( array_filter( $table_data, function( $row ) use ( $search_key ) {
      foreach( $row as $row_val ) {
        if( stripos( $row_val, $search_key ) !== false ) {
          return true;
        }
      }
    } ) );

    return $filtered_table_data;
  }

  /**
   * Handle table actions. Bulk actions included.
   *
   * @return void
   */
  public function handle_table_actions() {
    global $wpdb;

    $reviews_table = $wpdb->prefix . Self::$reviews_table;

    $table_action = $this->current_action();

    // If "Hide Review" action link was clicked
    if( 'hide_review' === $table_action ) {
      $nonce = \wp_unslash( $_REQUEST['_wpnonce'] );

      if( ! \wp_verify_nonce( $nonce, 'hide_review_nonce' ) ) {
        $this->invalid_nonce_redirect();
      } else {

        $wpdb->update(
          $reviews_table,
          array(
            'is_hidden'         => true,
          ),
          [
            'ID'     =>  (int) $_REQUEST['review_id']
          ]
        );

      }

    }

    // If "Unhide Review" action link was clicked.
    if( 'unhide_review' === $table_action ) {
      $nonce = \wp_unslash( $_REQUEST['_wpnonce'] );

      if( ! \wp_verify_nonce( $nonce, 'unhide_review_nonce' ) ) {
        $this->invalid_nonce_redirect();
      } else {

        $wpdb->update(
          $reviews_table,
          array(
            'is_hidden'         => false,
          ),
          [
            'ID'     =>  (int) $_REQUEST['review_id']
          ]
        );

      }

    }

    // If the "Hide Reviews" bulk action was selected.
    if( ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'hide_reviews' ) || 
      ( isset( $_REQUEST['action2'] ) && $_REQUEST['action2'] === 'hide_reviews' ) ) {

        $nonce = \wp_unslash( $_REQUEST['_wpnonce'] );

        if( ! \wp_verify_nonce( $nonce, 'bulk-gmb-connect_page_gmbc-reviews' )) {
          $this->invalid_nonce_redirect();
        } else {
          $this->handle_bulk_actions();
          $this->graceful_exit();
        }
    }
  }

  /**
   * Handle bulk actions. Since the GMB API determines what reviews we sync, 
   * this just handles hiding individual reviews on the site.
   *
   * @return void
   */
  public function handle_bulk_actions() {
    global $wpdb;

    $reviews_table = $wpdb->prefix . Self::$reviews_table;

    if( \current_user_can('manage_options' ) ) {
      if( isset( $_REQUEST['reviews'] ) && ! empty( $_REQUEST['reviews'] ) ) {
        foreach( $_REQUEST['reviews'] as $review_id ) {
          $wpdb->update(
            $reviews_table,
            array(
              'is_hidden'         => true,
            ),
            [
              'ID'     =>  (int) $review_id
            ]
          );
        }
      }
    } else {  
  ?>
      <p> <?php echo \__( 'You are not authorized to perform this operation.', 'gmbconnect' ) ?> </p>
  <?php   
    }

  }

  /**
   * Override the get_bulk_actions method and add Hide Reviews to our bulk acitons.
   *
   * @return string[]
   */
  public function get_bulk_actions() {

    $actions = [
      'hide_reviews'  => 'Hide Reviews'
    ];

    return $actions;

  }

  /**
   * Get the table data from the gmbc_reviews table.
   *
   * @return array[]
   */
  public function get_table_data() {
    global $wpdb;

    $reviews_table = $wpdb->prefix . Self::$reviews_table;
    $order_by = ( isset( $_GET['orderby'] ) ) ? \esc_sql( $_GET['orderby'] ) : 'update_time';
    $order = ( isset( $_GET['order'] ) ) ? \esc_sql( $_GET['order'] ) : 'DESC';

    $reviews_query = "SELECT 
        profile_photo_url, reviewer_display_name, SUBSTRING(comment, 1, 255) AS comment, star_rating, update_time, review_id, ID, is_hidden
        FROM $reviews_table
        ORDER BY $order_by $order";

    $results = $wpdb->get_results( $reviews_query, ARRAY_A );

    return $results;

  }

  /**
   * Override the column_default method and render our default output for most columns.
   *
   * @param array[] $item
   * @param string $column_name
   * @return string
   */
  public function column_default( $item, $column_name ) {

    switch ( $column_name ) {
      case 'profile_photo_url':
        return sprintf( '<img id="gmbc-photo-%s" class="gmbc-photo" referrerpolicy="no-referrer" src="%s" />', $item['ID'], $item['profile_photo_url'] );
      case 'reviewer_display_name':
      case 'comment':
      case 'star_rating':
      case 'update_time':
      case 'ID':
        return $item[$column_name];
      
      default:
       return $item[$column_name];
    }
  }

  /**
   * Custom callback for the is_hidden column. Renders string hidden if review is hidden and the action links.
   *
   * @param array[] $item
   * @return string
   */
  public function column_is_hidden( $item ) {

    $admin_page_url =  \admin_url( 'admin.php' );

    // row action to hide or show reviews.
    if( ! $item['is_hidden'] ) {
      $query_args_hide_review = array(
        'page'		=>  \wp_unslash( $_REQUEST['page'] ),
        'action'	=> 'hide_review',
        'review_id'	=> \absint( $item['ID']),
        '_wpnonce'	=> \wp_create_nonce( 'hide_review_nonce' ),
      );
      $hide_review_link = \esc_url( add_query_arg( $query_args_hide_review, $admin_page_url ) );		
      $actions['hide_review'] = '<a href="' . $hide_review_link . '" style="color: red;">' . __( 'Hide Review', 'gmbconnect' ) . '</a>';	
    }	else {
      $query_args_hide_review = array(
        'page'		=>  \wp_unslash( $_REQUEST['page'] ),
        'action'	=> 'unhide_review',
        'review_id'	=> \absint( $item['ID']),
        '_wpnonce'	=> \wp_create_nonce( 'unhide_review_nonce' ),
      );
      $hide_review_link = \esc_url( \add_query_arg( $query_args_hide_review, $admin_page_url ) );		
      $actions['hide_review'] = '<a href="' . $hide_review_link . '" style="color: green;">' . __( 'Unhide Review', 'gmbconnect' ) . '</a>';	
    }
  
    $output = ( $item['is_hidden'] ) ? 'Hidden' : '';
  
    $row_value = '<strong>' . $output . '</strong>';
    return $row_value . $this->row_actions( $actions );
  }

  public function column_star_rating( $item ) {

    $stars = '';

    for( $i = 1; $i <= $item['star_rating']; $i++ ) {
      $stars .= '<i class="gmbc-star solid-star">&starf;</i>';
    }

    if( $item['star_rating'] < 5 ) {
      $dif = 5 - $item['star_rating'];

      for( $i = 1; $i <= $dif; $i++ ) {
        $stars .= '<i class="gmbc-star empty-star">&starf;</i>';
      }
    }

    $stars .= '<br/><span class="gmbc-rating">(' . $item['star_rating'] . ')</span>';

    return $stars;
  }

  /**
   * Override the column_cb method and render our select all checkbox.
   *
   * @param array[] $item
   * @return void
   */
  protected function column_cb( $item ) {

    return sprintf(		
      '<label class="screen-reader-text" for="review_' . $item['ID'] . '">' . sprintf( __( 'Select %s' ), $item['review_id'] ) . '</label>'
      . "<input type='checkbox' name='reviews[]' id='review_{$item['ID']}' value='{$item['ID']}' />"					
      );
  }

  /**
   * Override the get_sortable_columns method and add our sortable columns to the list.
   *
   * @return void
   */
  protected function get_sortable_columns() {

    $sortable_cols = [
      'ID'                    =>  [ 'ID', true ],
      'star_rating'           => 'star_rating',
      'update_time'           => 'update_time',
      'reviewer_display_name' => 'reviewer_display_name'
    ];

    return $sortable_cols;
  }

}