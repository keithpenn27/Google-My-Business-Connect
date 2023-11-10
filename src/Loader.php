<?php 

namespace GMBConnect;

/**
 * The loader class handles the initialization of the majority of the plugin. 
 */
class Loader {

  /**
   * Path to the plugin directory
   *
   * @var string Without trailing slash.
   */
  public static $plugin_path;

  /**
   * URL to the plugin directory
   *
   * @var string Without trailing slash.
   */
  public static $plugin_url;

  /**
   * URL to the assets directory
   *
   * @var string Without trailing slash.
   */
  public static $assets_url;

  /**
   * The Loader instance
   *
   * @var Loader
   */
  private static $instance = null;


  /**
   * Construct the Loader as a Singleton.
   */
  private function __construct() {

    /**
     * Include 3rd party libs
     */
     $this->_init_libs();

     /**
      * Instantiate the shortcodes.
      */
     $this->_init_shortcodes();
    
    /**
     * Instantiate necessary classes
     */
    $this->_init();

    /**
     * Setup required hooks for the plugin
     */
    $this->hooks();

  }

  /**
   * Get the Singleton instance
   *
   * @return Loader
   */
  public static function get_instance() {

    if( Self::$instance == null ) {
      Self::$instance = new Loader();
    }

    return Self::$instance;
  }

  /**
   * Sets up the Oauth Client. Optionally displays a button to connect if we haven't already. 
   *
   * @param boolean $display If true we display a connect button and return the client instance. If false, we just
   * return the client instance without displaying a button. Default is false.

   * @return Google_Client
   */
  public static function get_client() {

    $creds = \get_option( 'gmbc_credentials' );
    $client_id = ( isset( $creds['client_id'] ) && ! empty( $creds['client_id'] ) ) ? $creds['client_id'] : '';
    $client_secret = ( isset( $creds['client_secret'] ) && ! empty( $creds['client_secret'] ) ) ? $creds['client_secret'] : '';

    $encrypted_creds['client_id'] = $client_id;
    $encrypted_creds['client_secret'] = $client_secret;

    if ( $creds !== false ) {
      // We need to decrypt the client credentials before setting up the Google Client Object
      foreach( $encrypted_creds as $k => $v ) {
        switch ( $k ) {
          case 'client_id' :
            list( $enc, $ascii_key ) = explode( '::', $v );
            $key = \Defuse\Crypto\Key::loadFromAsciiSafeString( $ascii_key );
            $client_id = \Defuse\Crypto\Crypto::decrypt( $enc, $key );
  
            break;
  
          case 'client_secret' :
            list( $enc, $ascii_key ) = explode( '::', $v );
            $key = \Defuse\Crypto\Key::loadFromAsciiSafeString( $ascii_key );
            $client_secret = \Defuse\Crypto\Crypto::decrypt( $enc, $key );
  
            break;
        }
      }

        // Create the Oauth Client
        $client = new \Google\Client();
        $client->setAuthConfig(['client_id' => $client_id, 'client_secret' => $client_secret]);
        $client->setScopes(array(
          'https://www.googleapis.com/auth/business.manage',
          'https://www.googleapis.com/auth/plus.business.manage'
        ));

        // Set the Redirect URI with the value from the database
        if ( isset($creds['redirect_uri']) && ! empty($creds['redirect_uri'] ) ) {
            $client->setRedirectUri($creds['redirect_uri']);
        }
        $client->setAccessType( 'offline' );
        $client->setPrompt( 'select_account consent' );

        if ( isset( $creds['access_token'] ) && ! empty( $creds['access_token'] ) ) {

          // The token exists, so set access token.
          $access_token = $creds['access_token'];
          $client->setAccessToken( $access_token );

          if ( $client->isAccessTokenExpired() ) {

            // The token is expired, so refresh
            if ( $client->getRefreshToken() ) {
              $creds['access_token'] = $client->fetchAccessTokenWithRefreshToken( $client->getRefreshToken() );
              \update_option( 'gmbc_credentials', $creds );
            } else {

                // In case we need to reconnect
                $auth_url = $client->createAuthUrl();
            }
          }
        } else {

          // We aren't connected yet
          if ( ! isset( $_GET['code'] ) ) {
              $auth_url = $client->createAuthUrl();
              header( 'Location: ' . filter_var( $auth_url, FILTER_SANITIZE_URL ) );
          } else {

            // The auth code was sent from Google
            $auth_code = $_GET['code'];

            // Get the access token
            $access_token = $client->fetchAccessTokenWithAuthCode( $auth_code );

            // If there was an error with authorization throw the exception
            if ( array_key_exists('error', $access_token ) ) {
                throw new \Exception( join( ',', $access_token ) );
            }

            // Set the access token
            $client->setAccessToken( $access_token );

            // Store the access token in the file set in $token_path
            $creds['access_token'] = $client->getAccessToken();
            \update_option( 'gmbc_credentials', $creds );
          }
        }
  
      // Return the client, so we can use it later on.
      return $client;

    } else {
      // Return false if we couldn't set up the client
      return false;

    }

  }

  /**
   * Required WP hooks
   *
   * @return void
   */
  private function hooks() {
    
    // Action hooks
    \add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
    \add_action( 'wp_enqueue_scripts', [ $this, 'wp_scripts' ] );
    
    // Filter hooks
    \add_filter( 'cron_schedules', [ $this, 'cron_schedules' ], 10 );

  }

  public function wp_scripts( $hook ) {
    global $post;

    // If the shortcode is on the current page, enqueue the css
    if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'gmbc_reviews' ) ) {
      \wp_enqueue_style( 'gmbc-style', Self::$plugin_url . '/src/css/style.css', '', true, 'all' );
    }
  }

  /**
   * Enqueue our admin scripts. Styles and js in the WP admin area
   *
   * @param string $hook The hook name. Passed by admin_enqueue_scripts.
   * @return void
   */
  public function admin_scripts( $hook ) {
    \wp_enqueue_script( 'gmbc-reviews', Self::$plugin_url . '/src/js/gmbc-reviews.js', array( 'jquery' ), true, true );
    \wp_enqueue_style( 'gmbc-admin', Self::$plugin_url . '/src/css/admin/style.css', '', true, 'all' );
  }

  /**
   * Add custom cron schedules
   *
   * @param array[] $schedules
   * @return array[] Returns the cron schedules.
   */
  public function cron_schedules( $schedules ) {
    $schedules['every_two_weeks'] = array(
      'interval' => 1209600,
      'display'  => \esc_html__( 'Every Two Weeks', 'gmbconnect' )
    );

    $schedules['monthly'] = array(
      'interval'  => 2419200,
      'display'   => \esc_html__( 'Monthly', 'gmbconnect' )
    );

    if( ! isset( $schedules['weekly'] ) ) {
      $schedules['weekly'] = array(
        'interval'  => 604800,
        'display'   => \esc_html__( 'Weekly', 'gmbconnect' )
      );
    }

    return $schedules;
  }

  /**
   * Instantiates most required classes for the plugin
   *
   * @return void
   */
  private function _init() {

    new Admin\AdminArea();
    new Admin\Reviews();
    new Admin\Support();
    new Reviews();
    new DBTables();

    require_once Self::$plugin_path . '/src/gmbc-functions.php';

  }

  /**
   * Include required 3rd party libraries
   *
   * @return void
   */
  private function _init_libs() {

    Self::$plugin_url  = rtrim( plugin_dir_url( __DIR__ ), '/\\' );
		Self::$assets_url  = Self::$plugin_url . '/assets';
		Self::$plugin_path = rtrim( plugin_dir_path( __DIR__ ), '/\\' );

    // Require the composer auto loader
    require_once Self::$plugin_path . '/vendor/autoload.php';

  }

  // Instantiate our shortcode classes
  private function _init_shortcodes() {
    new Shortcodes\Reviews();
  }


}