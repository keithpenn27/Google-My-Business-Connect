<?php 

namespace GMBConnect\Shortcodes;

/**
 * Abstract class for all shortcodes to extend.
 */
abstract class Shortcode {

  /**
   * Construct the shortcode object.
   *
   * @param string $tag_name
   * @param string[] $allowed_atts
   */
  function __construct() {

    if( ! isset( $this->tag_name ) || ! isset( $this->allowed_atts ) ) {

      // If $tag_name or $allowed_atts is not set, throw exceptions.
      if( ! isset( $this->tag_name ) ) {
        throw new \LogicException( get_class( $this ) . ' must have the $tag_name property and it must be accessible.');
      } else {
        throw new \LogicException( get_class( $this ) . ' must have the $allowed_atts property and it must be accessible.');
      }
    } 

    // Add our shortcodes. The abstract execute() method needs to be overridden in child classes
    add_shortcode( $this->tag_name, [ $this, 'execute' ] );
  }

  /**
   * The callback for add_shortcode. Needs to be implemented for each class that extends Shortcode.
   *
   * @param string[] $atts
   * @param string $content
   * @param string $shortcode_tag
   * @return string
   */
  abstract public function execute( $atts, $content, $shortcode_tag );

}