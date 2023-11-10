<?php 

namespace GMBConnect\Admin;

/**
 * The Admin Reviews class handles the page settings and display of the Reviews administration page.
 */
class Support {

  /**
   * The admin page slug.
   */
  const SLUG = 'gmbc-support';

  /**
   * The capabilities required to view the page.
   */
  const CAPABILITIES = 'manage_options';

  /**
   * The options group for the main settings page.
   */
  const OPTIONS_GROUP = 'gmbc_support';

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
      \__( 'GMB Connect Support', 'gmbconnect' ), 
      \__( 'Support', 'gmbconnect' ), 
      Self::CAPABILITIES, 
      Self::SLUG, 
      [ $this, 'render_page' ], 
      5
    );
  }

  /**
   * Render the Reviews Admin page.
   *
   * @return void
   */
  public function render_page() {
    ?>
      <div class="wrap gmbc-wrap">
      <h2><?php echo \get_admin_page_title(); ?></h2>
        <h3>Initial Set Up</h3>
        <ol class="gmbc-ordered-list">
          <li><h3>Go to <a href="<?php echo admin_url('/plugins.php') ?>" target="_blank">plugins</a> and activate the Google My Business Connect
 plugin.</h3></li>
          <li><h3>Next, go to the <a href="<?php echo admin_url('/admin.php?page=gmb-connect') ?>" target="_blank">GMB Connect Settings page</a> and set up the Client Credentials</h3></li>
            <ol class="gmbc-ordered-list">
              <li>Visit the <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a> and login with the <strong>info@lightsoncreative.com</strong> email address.</span></li>
              <li>In the top left corner, select the "GMB API - Reviews project</li>
              <li>Click the hamburger menu in the top left corner, hover over "API's and Services", then click "Credentials".</li>
              <li>In the next page, go the "OAuth 2.0 Client IDs" section and click the "LOC - GMBC Credentials" credential instance.</li>
              <li>Next, copy the "Client ID" in the top right corner and paste it into the "Client ID" field in the plugin settings page.</li>
              <li>Copy the "Client Secret" from the Google Cloud Console credentials and paste it into the "Client Secret" field in the plugin settings page.</li>
              <li>Lastly, Copy the "Redirect URI" value in the plugin settings page. Now go back to the Google Cloud Console and scroll to the bottom of the page.
                  In the "Authorized redirect URIs" section, click the "Add URI" button. Paste the redirect uri into the new field.</li>
              <li>Save the credentials in the Google Cloud Console</li>
              <li>Now save the credentials in the plugin settings page</li>
              <li>The Google Authentication page should display now. Choose the <strong>info@lightsoncreative.com</strong> email account. If you haven't logged into that email address yet, then do so now. 
                  <span>This email address has access to all of our client's GMB accounts, so we need to authenticate with this email address.</span></li>
              <li>If all goes well, you will be redirected back to the plugin settings page and you will see more settings for locations and reviews.</li>
            </ol>
          <li><h3>Locations & Reviews Settings</h3><span>These are default settings that determine what reviews are displayed when using the [gmbc_reviews /] shortcode.</span></li>
            <ol class="gmbc-ordered-list">
              <li>Choose the Location that you want to pull in reviews for</li>
              <li>Set the minimum review rating</li>
              <li>Set the maximum amount of reviews to display</li>
              <li>Set the maximum reviews per page to display</lI>
              <li>Set the update frequency. This controls how often we send requests to the Google API to refresh the locations and reviews</li>
            </ol>
          <li><h3>Now go to the <a href="<?php echo admin_url('/admin.php?page=gmbc-reviews') ?>" target="_blank">Reviews admin page</a> and sync the reviews</h3></li>
            <ol class="gmbc-ordered-list">
              <li>Click the "Sync Reviews" button to update the Locations and Reviews. Clicking this button causes a manual sync, but will not change the "Update Frequency" from the main settings page.</li>
            </ol>
        </ol>
      </div>
      <div class="wrap gmbc-wrap gmbc-shortcode-support">

      <h2>Displaying Reviews</h2>
      <p>To display reviews on a page or post you will need to use the [gmbc_reviews /] shortcode. There are a number of shortcode attributes
       that can be used to control what reviews are displayed. Below is a list of attributes and examples.</p>

      <table class="gmbc-table">
        <tr><th>Attribute</th><th>Description</th><th>Example</th></tr>
        <tr><td>max_reviews</td><td>A number between 1 and 999. This can also be set to max_reviews="all" to display all reviews.</td><td>[gmbc_reviews max_reviews="50" /]</td></tr>
        <tr><td>min_rating</td><td>A number between 1 and 5.</td><td>[gmbc_reviews min_rating="4" /]</td></tr>
        <tr><td>per_page</td><td>A number between 1 and 50.</td><td>[gmbc_reviews per_page="25" /]</td></tr>
        <tr><td>id</td><td>A comma separated list of review id's to display. The review id can be found in the ID column of the <a href="<?php echo admin_url('/admin.php?page=gmbc-reviews') ?>" target="_blank">Reviews table</a></td><td>[gmbc_reviews id="1, 20, 230" /]</td></tr>
        <tr><td>class</td><td>A string of CSS classes to apply to the reviews wrapper. Class names should be separated with a space, not a comma.</td><td>[gmbc_reviews class="reviews-wrapper my-class" /]</td></td></tr>
        <tr><td>reviews_link_class</td><td>A string of classes to apply to the "Write a Review" link. Class names should be separated with a space, not a comma.</td><td>[gmbc_reviews reviews_link_class="reviews write_reviews_link" /]</td></tr>
        <tr><td>pagination_link_class</td><td>A string of classes to apply to both of the pagination links. Class names should be separated with a space, not a comma.</td><td>[gmbc_reviews pagination_link_class="reviews pagination_links" /]</td></tr>
        <tr><td>next_link_class</td><td>A string of classes to apply to the "Next >>" pagination link. Class names should be separated with a space, not a comma.</td><td>[gmbc_reviews next_link_class="reviews next_link" /]</td></tr>
        <tr><td>prev_link_class</td><td>A string of classes to apply to the "<< Previous" pagination link. Class names should be separated with a space, not a comma.</td><td>[gmbc_reviews prev_link_class="reviews prev_link" /]</td></tr>
        <tr><td>next_link_text</td><td>Override the text that is displayed for the "Next >>" pagination link.</td><td>[gmbc_reviews next_link_text="Next Page" /]</td></tr>
        <tr><td>prev_link_text</td><td>Override the text that is displayed for the "<< Previous" pagination link.</td><td>[gmbc_reviews prev_link_text="Previous Page" /]</td></tr>
        <tr><td>reviews_link_text</td><td>Override the text that is displayed for the "Write a Review" link.</td><td>[gmbc_reviews reviews_link_text="Review Our Company" /]</td></tr>
      </table>
      </div>
    <?php
  }
}