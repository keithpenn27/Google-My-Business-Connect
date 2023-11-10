<?php 

namespace GMBConnect\Admin;

/**
 * The main Admin Area settings page
 */
class AdminArea {

  /**
   * The admin page slug.
   */
  const SLUG = 'gmb-connect';

  /**
   * The capabilities required to view the page.
   */
  const CAPABILITIES = 'manage_options';

  /**
   * The options group for the main settings page.
   */
  const OPTIONS_GROUP = 'gmbc_settings';

  /**
   * An array to hold setting and field information for reviews settings.
   *
   * @var string[]
   */
  private $reviews = [
    'setting_name'  => 'gmbc_reviews',
    'setting_type'  => 'array',
    'setting_desc'  => 'Google My Business Reviews settings.',
    'section_title' => 'Reviews Settings',
    'fields'        =>  [
      'rating_min'    => [
        'title'         => 'Minimum Rating',
        'call_back'     => 'render_input_field',
        'args'          => [
          'class'         => 'gmbc-input gmbc-number-input',
          'label_for'     => 'rating_min',
          'option'        => 'gmbc_reviews',
          'option_key'    => 'rating_min',
          'type'          => 'number',
          'desc'          => 'Select the default minumum star rating to display. Only reviews with this rating or above will be displayed. This can be overridden in the shortcode. Ex: [gmbc_reviews rating_min="4" /].',
          'html_atts'     => 'min="1" max="5" required'
        ]
      ],
      'reviews_max' => [
        'title'       => 'Maximum Reviews',
        'call_back'   => 'render_input_field',
        'args'        => [
          'class'       => 'gmbc-input gmbc-number-input',
          'label_for'   => 'reviews_max',
          'option'      => 'gmbc_reviews',
          'option_key'  => 'reviews_max',
          'type'        => 'number',
          'desc'        => 'Select the max number of reviews to display. This can be overridden when using a shortcode to display reviews. Ex: [gmbc_reviews reviews_max="50" /]',
          'html_atts'   => 'min="1" max="999" required'
        ]
      ],
      'reviews_per_page' => [
        'title'       => 'Reviews Per Page',
        'call_back'   => 'render_input_field',
        'args'        => [
          'class'       => 'gmbc-input gmbc-number-input',
          'label_for'   => 'reviews_per_page',
          'option'      => 'gmbc_reviews',
          'option_key'  => 'reviews_per_page',
          'type'        => 'number',
          'desc'        => 'Select the number of reviews to display per page. This can be overridden when using a shortcode to display reviews. Ex: [gmbc_reviews reviews_per_page="10" /]',
          'html_atts'   => 'min="1" max="50" required'
        ]
      ],
      'update_frequency' => [
        'title'       => 'Update Frequency',
        'call_back'   => 'render_frequency_select_field',
        'args'        => [
          'class'       => 'gmbc-input gmbc-select-input',
          'label_for'   => 'update_frequency',
          'option'      => 'gmbc_reviews',
          'option_key'  => 'update_frequency',
          'type'        => 'select',
          'desc'        => 'Select the frequency that reviews are requested from the Google API.',
          'html_atts'   => 'required',
          'options'     => [
            'none'      => [
              'value'   => '',
              'label'   => 'Select Frequency',
              'html_atts' => 'selected disabled hidden'
            ],
            'hourly'    => [
              'value'     => 'hourly',
              'label'     => 'Hourly'
            ],
            'daily'     => [
              'value'     => 'daily',
              'label'     => 'Daily'
            ],
            'twicedaily' => [
              'value'     => 'twicedaily',
              'label'     => 'Twice Daily'
            ],
            'weekly'      => [
              'value'       => 'weekly',
              'label'       => 'Weekly'
            ],
            'every_two_weeks' => [
              'value'     => 'every_two_weeks',
              'label'     => 'Every Two Weeks'
            ],
            'monthly'   => [
              'value'     => 'monthly',
              'label'     => 'Monthly'
            ]
          ]
        ]
      ],
      'total_review_count' => [
        'title'         => '',
        'call_back'     => 'render_input_field',
        'args'          => [
          'class'       => 'gmbc-input gmbc-text-input',
          'label_for'   => 'total_review_count',
          'option'      => 'gmbc_reviews',
          'option_key'  => 'total_review_count',
          'type'        => 'text',
          'desc'        => '',
          'html_atts'   => 'hidden'
        ]
      ],
      'average_rating' => [
        'title'         => '',
        'call_back'     => 'render_input_field',
        'args'          => [
          'class'       => 'gmbc-input gmbc-text-input',
          'label_for'   => 'average_rating',
          'option'      => 'gmbc_reviews',
          'option_key'  => 'average_rating',
          'type'        => 'text',
          'desc'        => '',
          'html_atts'   => 'hidden'
        ]
      ]
    ]
  ];

  /**
   * The client credentials. Redirect URI included so we know what to add to the Google Console. Access token
   * will be updated once we authenticate.
   *
   * @var array
   */
  private $creds = [
    'setting_name'  => 'gmbc_credentials',
    'setting_type'  => 'array',
    'setting_desc'  => 'The client credentials from the cloud console.',
    'section_title' => 'Client Credentials',
    'fields'        => [
      'client_id'     => [
        'title'         => 'Client ID',
        'call_back'     => 'render_input_field',
        'args'          => [
          'class'         => 'gmbc-input gmbc-text-input',
          'label_for'     => 'client_id',
          'option'        => 'gmbc_credentials',
          'option_key'    => 'client_id',
          'type'          => 'password',
          'desc'          => 'Enter the client credentials from the Google Cloud Console.',
          'html_atts'     => 'size="50" required'       
        ]
      ],
      'client_secret' => [
        'title'         => 'Client Secret',
        'call_back'     => 'render_input_field',
        'args'          => [
          'class'         => 'gmbc-input gmbc-text-input',
          'label_for'     => 'client_secret',
          'option'        => 'gmbc_credentials',
          'option_key'    => 'client_secret',
          'type'          => 'password',
          'desc'          => 'Enter the client secret from the Google Cloud Console.',
          'html_atts'     => 'size="50" required'
        ]
      ],
      'redirect_uri'  => [
        'title'         => 'Redirect URI',
        'call_back'     => 'render_input_field',
        'args'            => [
          'class'        => 'gmbc-input gmbc-text-input',
          'label_for'     => 'redirect_uri',
          'option'        => 'gmbc_credentials',
          'option_key'    => 'redirect_uri',
          'type'          => 'text',
          'desc'          => 'Enter this redirect URI into the credential settings in the Google Cloud Console.',
          'html_atts'     => 'readonly="readonly" size="50" required'
        ]
      ],
      'access_token' => [
        'title'       => '',
        'call_back'   => 'render_input_field',
        'args'        => [
          'class'       => 'gmbc-input gmbc-text-input',
          'label_for'   => 'access_token',
          'option'      => 'gmbc_credentials',
          'option_key' => 'access_token',
          'type'        => 'text',
          'desc'        => '',
          'html_atts'   => 'hidden'
        ]
      ]
    ]
  ];

   /**
   * An array to hold setting and field information for location settings.
   *
   * @var string[]
   */
  private $locations = [
    'setting_name'  => 'gmbc_locations',
    'setting_type'  => 'array',
    'setting_desc'  => 'Google My Business Location settings.',
    'section_title' => 'Location Settings',
    'fields'        =>  [
      'location_name'    => [
        'title'         => 'Location Name',
        'call_back'     => 'render_location_select_field',
        'args'          => [
          'class'         => 'gmbc-input gmbc-select-input',
          'label_for'     => 'location_name',
          'option'        => 'gmbc_locations',
          'option_key'    => 'location_name',
          'type'          => 'select',
          'desc'          => 'Select the location that you would like to pull in reviews for.',
          'html_atts'     => 'required'
        ]
      ]
    ]
  ];

   /**
    * Construct the Admin Area
    */
  function __construct() {

    $creds = \get_option( 'gmbc_credentials' );

    $this->client = \GMBConnect\Loader::get_client();
    
    $this->hooks();

  }

  /**
   * Register the required hooks for this page
   *
   * @return void
   */
  private function hooks() {

    \add_action( 'admin_menu', [ $this, 'main_menu' ] );
    \add_action( 'admin_init', [ $this, 'register_cred_settings' ] );
    \add_action( 'admin_init', [ $this, 'register_location_settings' ] );
    \add_action( 'admin_init', [ $this, 'register_reviews_settings' ] );

  }

  /**
   * Add the menu items for the main admin page.
   *
   * @return void
   */
  public function main_menu() {
    \add_menu_page( \__( 'Google My Business Connect' ),
     \__( 'GMB Connect', 'gmbconnect' ),
     Self::CAPABILITIES, 
     Self::SLUG, 
     [ $this, 'render_page' ], 
     'dashicons-google',
     80 
    );

    \add_submenu_page( 
      Self::SLUG, 
      'GMB Connect', 
      \__( 'Settings', 'gmbconnect' ), 
      Self::CAPABILITIES, 
      Self::SLUG,
      '',
      1
    );
  }

    /**
   * Register the gmbc_option in the database. Add Location settings section and fields.
   *
   * @return void
   */
   public function register_reviews_settings() {
    
    \register_setting( 
      Self::OPTIONS_GROUP, 
      $this->reviews['setting_name'], 
      [ 
        'type' => $this->locations['setting_type'], 
        'description' => $this->locations['setting_desc']
      ] 
    );

    \add_settings_section( 
      $this->reviews['setting_name'], 
      \__( $this->reviews['section_title'], 'gmbconnect' ), 
      '', 
      Self::SLUG 
    );

    foreach ( $this->reviews['fields'] as $key => $field ) {

      \add_settings_field( 
        $key, 
        \__( $field['title'], 'gmbconnect' ), 
        [ $this, $field['call_back'] ], 
        Self::SLUG, 
        $this->reviews['setting_name'],
        $field['args']
      );

    }

  }

  public function register_cred_settings() {
    
    \register_setting( 
      Self::OPTIONS_GROUP, 
      $this->creds['setting_name'], 
      [ 
        'type' => $this->creds['setting_type'], 
        'description' => $this->creds['setting_desc'],
        'sanitize_callback' => [ $this, 'sanitize_client_creds' ]  
      ] 
    );

    \add_settings_section( 
      $this->creds['setting_name'], 
      \__( $this->creds['section_title'], 'gmbconnect' ), 
      '', 
      Self::SLUG 
    );

    foreach ($this->creds['fields'] as $key => $field ) {

      \add_settings_field( 
        $key, 
        \__( $field['title'], 'gmbconnect' ), 
        [ $this, $field['call_back'] ], 
        Self::SLUG, 
        $this->creds['setting_name'],
        $field['args']
      );

    }

  }

  /**
   * Register the gmbc_option in the database. Add Location settings section and fields.
   *
   * @return void
   */
  public function register_location_settings() {
    
    \register_setting( 
      Self::OPTIONS_GROUP, 
      $this->locations['setting_name'], 
      [ 
        'type' => $this->locations['setting_type'], 
        'description' => $this->locations['setting_desc']
      ] 
    );

    \add_settings_section( 
      $this->locations['setting_name'], 
      \__( $this->locations['section_title'], 'gmbconnect' ), 
      '', 
      Self::SLUG 
    );

    foreach ($this->locations['fields'] as $key => $field ) {

      \add_settings_field( 
        $key, 
        \__( $field['title'], 'gmbconnect' ), 
        [ $this, $field['call_back'] ], 
        Self::SLUG, 
        $this->locations['setting_name'],
        $field['args']
      );

    }

  }

  /**
   * Render the normal input fields.
   *
   * 
   *
   * @param string[] $args (required) - An array of string attributes to echo in the input field atts. 
   * 
   * @return void
   */
  public function render_input_field( $args ) {

    $val = \get_option( $args['option'] );
    $val = ( isset( $val[$args['option_key'] ] ) ) ? $val[ $args['option_key'] ] : '';
    
    if( ( $args['option_key'] === 'client_id' || $args['option_key'] === 'client_secret' ) && $val ) {
      list( $encrypted, $ascii_key ) = explode( '::', $val );
      $key = \Defuse\Crypto\Key::loadFromAsciiSafeString( $ascii_key );
      $val = \Defuse\Crypto\Crypto::decrypt( $encrypted, $key );
    }

  if( $args['option_key'] === 'redirect_uri' ) {
      $val = ( isset( $_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http" ) . "://$_SERVER[HTTP_HOST]" . '/wp-admin/admin.php?page=gmb-connect';
    }

    $name = $args['option'] . '[' . $args['option_key'] . ']';

    $html_atts = ( isset( $args['html_atts'] ) ) ? $args['html_atts'] : '';

    if( $args['option_key'] === 'access_token' && ! empty( $val ) ) {
      foreach( $val as $key => $v ) {
        if ( isset($val[$key] ) ) {
            ?>
        <input class="<?php echo $args['class'] ?>" type="<?php echo $args['type'] ?>" id="<?php echo $key; ?>" 
        name="<?php echo $name . '[' . $key . ']' ?>" value="<?php echo \esc_attr__( $v, 'gmbconnect' ); ?>" <?php echo \esc_attr( $html_atts ) ?> />
      <p class="description" id="tagline-description"><?php echo \__( $args['desc'], 'gmbconnect' ); ?></p>
        <?php
        }
      }
    } else {
        ?>
      <input class="<?php echo $args['class'] ?>" type="<?php echo $args['type'] ?>" id="<?php echo $args['option_key']; ?>" 
        name="<?php echo $name ?>" value="<?php echo \esc_attr__( $val, 'gmbconnect' ); ?>" <?php echo $html_atts ?> />
      <p class="description" id="tagline-description"><?php echo \__( $args['desc'], 'gmbconnect' ); ?></p>
    <?php
    }
  }


  /**
   * Render the location select field.
   *
   * @param string[] $args
   * @return void
   */
  public function render_location_select_field( $args ) {

    $name = $args['option'] . '[' . $args['option_key'] . ']';

    $html_atts = ( isset( $args['html_atts'] ) ) ? $args['html_atts'] : '';

    if( $current_val = \get_option( $args['option'] ) ) {
      $current_val = $current_val[$args['option_key']];
    }

    // Get the locations, either from the database or the API
    $locations = \GMBConnect\DBTables::get_locations();

    ?>
      <select class="<?php echo $args['class'] ?>" id="<?php echo $args['option_key']; ?>" name="<?php echo $name ?>" <?php echo \__( $html_atts, 'gmbconnect' ) ?> >
      <option selected disabled hidden value="" ><?php echo \esc_attr__( 'Select Location', 'gmbconnect' ) ?></option>
      <?php foreach( $locations as $location ) { ?>
        <option value="<?php echo $location->name ?>" <?php \selected( $current_val, $location->name ) ?> ><?php echo \__( $location->title, 'gmbconnect' ) ?></option>
      <?php } ?>
      </select>
      <p class="description" id="tagline-description"><?php echo \__( $args['desc'], 'gmbconnect' ) ?></p>
    <?php 
    
  }

  /**
   * Render the Update Frequency select field.
   *
   * @param string[] $args
   * @return void
   */
  public function render_frequency_select_field( $args ) {

    $name = $args['option'] . '[' . $args['option_key'] . ']';

    $html_atts = ( isset( $args['html_atts'] ) ) ? $args['html_atts'] : '';

    if(  ( $current_val = \get_option( $args["option"] ) ) && isset( $current_val[ $args['option_key'] ] ) ) {
      $current_val = $current_val[$args['option_key']];
    }

    ?>
      <select class="<?php echo $args['class'] ?>" id="<?php echo $args['option_key']; ?>" name="<?php echo $name ?>" <?php echo \esc_attr( $html_atts ) ?> >
      <?php foreach( $args['options'] as $option ) { 
          $opt_html_atts = ( isset( $option['html_atts'] ) ) ? $option['html_atts'] : '';

        ?>
        <option <?php echo \esc_attr( $opt_html_atts ) ?> value="<?php echo \esc_attr__( $option['value'], 'gmbconnect' ) ?>" <?php \selected($current_val, $option['value']); ?> ><?php echo \__( $option['label'], 'gmbconnect' ) ?></option>
        <?php } ?>
      </select>
      <p class="description" id="tagline-description"><?php echo \__( $args['desc'], 'gmbconnect' ) ?></p>
    <?php
  }

  /**
   * Render the main Admin Area page.
   *
   * @return void
   */
  public function render_page() {

    global $gmbc_loader;

    $loader = \GMBConnect\Loader::get_instance();

    $creds = \get_option( 'gmbc_credentials' );
    
    ?>

    <div class="wrap">
    <h1><?php echo \get_admin_page_title(); ?></h1> 
    <form action="options.php" method="post">
    <?php 

    // Show the client credential fields first and hide the rest
    echo '<table class="form-table">';
    \settings_fields(Self::OPTIONS_GROUP);
    echo "<h2>{$this->creds['section_title']}</h2>";
    \do_settings_fields( Self::SLUG, 'gmbc_credentials' );
    echo '</table>';

    if( isset( $creds['access_token'] ) && ! empty( $creds['access_token'] ) ) {
      // We have the credentials, so show the reste of the settings
      echo '<table class="form-table">';
      echo "<h2>{$this->locations['section_title']}</h2>";
      \do_settings_fields(Self::SLUG, 'gmbc_locations' );
      echo '</table>';
      echo '<table class="form-table">';
      echo "<h2>{$this->reviews['section_title']}</h2>";
      \do_settings_fields(Self::SLUG, 'gmbc_reviews' );
      echo '</table>';

    }
    // Show the submit button, always.
    \submit_button(); 

    ?>
     </form>
    </div>
    <?php
  }

  /**
   * Sanitize and encrypt the Client ID and Client Secret
   *
   * @param array $value
   * @return array $value
   */
  public function sanitize_client_creds( $value ) {
    
    // Create the encryption key
    $key = \Defuse\Crypto\Key::createNewRandomKey();

    foreach( $value as $k => $v ) {
      // Don't encrypt values that we don't want to.
      if( $k === 'redirect_uri' || $k === 'access_token' ) {
        continue;
      } else {
        // If we already encrypted, don't do it again
        if( strpos( $v, '::' ) !== false ) {
          continue;
        } else {
          // Encrypt the fields
          $encrypted = \Defuse\Crypto\Crypto::encrypt( $v, $key );
          $ascii_key = $key->saveToAsciiSafeString( $key );
          $value[$k] = $encrypted . '::' . $ascii_key;
        }
      }
    }
    return $value;
  }

}