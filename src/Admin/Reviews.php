<?php 

namespace GMBConnect\Admin;

/**
 * The Admin Reviews class handles the page settings and display of the Reviews administration page.
 */
class Reviews {

  /**
   * The admin page slug.
   */
  const SLUG = 'gmbc-reviews';

  /**
   * The capabilities required to view the page.
   */
  const CAPABILITIES = 'manage_options';

  /**
   * The options group for the main settings page.
   */
  const OPTIONS_GROUP = 'gmbc_reviews';

  /**
  * Construct the Reviews Admin Area
  */
  function __construct() {
  
    $this->hooks();

  }

  /**
   * Register the required hooks for this page
   *
   * @return void
   */
  private function hooks() {

    \add_action( 'admin_menu', [ $this, 'add_gmbc_menu' ] );

  }

  /**
   * Add the menu for the reviews page.
   *
   * @return void
   */
  public function add_gmbc_menu() {

    $reviews_page_hook = \add_submenu_page( 
      AdminArea::SLUG, 
      \__( 'GMB Connect Reviews', 'gmbconnect' ), 
      \__( 'Reviews', 'gmbconnect' ), 
      Self::CAPABILITIES, 
      Self::SLUG, 
      [ $this, 'render_page' ], 
      5
    );

    \add_action( 'load-' . $reviews_page_hook, [ $this, 'load_reviews_list_table_screen_options'] );
  }

  /**
   * Load our custom screen options
   *
   * @return void
   */
  public function load_reviews_list_table_screen_options() {

    $args = [
      'label'   =>  \esc_html__( 'Reviews Per Page', 'gmbconnect' ),
      'default' => 10,
      'option'  => 'edit_post_per_page'
    ];

    \add_screen_option( 'per_page', $args );

    $this->reviews_list_table = new ReviewsListTable( 'gmbconnect' );
  }

  /**
   * Render the Reviews Admin page.
   *
   * @return void
   */
  public function render_page() {

    $loader = \GMBConnect\Loader::get_instance();

    $this->reviews_list_table->prepare_items();
    ?>

    <h2><?php echo \get_admin_page_title(); ?></h2>
    <div class="wrap" >

    <div class="sync-btn-wrapper">
      <a href="#" id="gmbc-sync-reviews" class="button button-primary" >Sync Reviews</a>
      <img id="gmbc-reviews-spinner" class="gmbc-admin-icon" style="display: none; width: 30px;" src="<?php echo $loader::$plugin_url . '/assets/ajax-spinner.gif'; ?>" />
    </div>
    <div id="gmbc-error-message">

    </div>
    <form id="gmbc-reviews-table">
    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
      <?php 
      $this->reviews_list_table->search_box( __( 'Find', 'gmbconnect' ), 'nds-user-find');
      $this->reviews_list_table->display(); 
      ?>
    </form>
    </div>

    <?php
  }
}