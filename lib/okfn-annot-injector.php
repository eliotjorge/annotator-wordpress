<?php

/*
 *
 * Appends the Annotator JavaScript and CSS files (with their respective dependencies)
 * to the document leveraging the appropriate Wordpress hooks.
 *
 *
 */

class OkfnAnnotInjector extends OkfnBase {
  public $conf = array(
    'assets_basepath' => '/vendor/',

    // In the sake of semplicity for now we only support the full minified version
    // of the annotator (A more fine grained control through the settings can be implemented later).

    'javascripts' => array(
      array(
        'file' => 'javascripts/jquery.min.js'
      ),
      array(
        'file' => 'javascripts/annotator/annotator-full.min.js',
        'dependencies' => array('json2','jquery'),
      ),
    ),

    'stylesheets' => array(
      array(
        'file' => 'stylesheets/annotator.min.css'
      )
    )
  );

  function __construct(){
    parent::__construct();
  }

  /*
   * Enqueues the Annotator's dependencies (i.e. JSON2, and jQuery).
   *
   * returns nothing
   *
   */
  function load_javascript_dependencies(){
    wp_enqueue_script('json2');
    //deregister the javascript version used by wordpress
    wp_deregister_script('jquery');
  }

  /*
   * Public:
   *
   * Enqueues the Annotator javascript files
   *
   * returns nothing
   *
   */
  public function load_javascripts() {
    $this->load_javascript_dependencies();
    $this->load_assets('javascripts');
  }

  /*
   * Public:
   *
   * Enqueues the Annotator stylesheet/s
   *
   * returns nothing
   *
   */
  function load_stylesheets() {
    $this->load_assets('stylesheets');
  }


  /*
   * Ensures that libraries are registered with the '.{js|css}' and not the '.min.{js|css}' prefix
   * prefix
   *
   * path - a relative path to an asset
   *
   */
  function asset_id($path) {
    return preg_replace('/(\.min)\.(js|css)$/','.$2', basename($path) );
  }


  /*
   * Wrapper for for wp_enqueue_{style|script}
   *
   * This has been implemented only for mocking/testing purposes.
   *
   * asset_id                      - The asset filename (without the path and the .min prefix).
   * asset_src                     - The asset url (relative to the plugin directory).
   * dependencies                  - Array of asset filenames specifying other assets that should be loaded first.
   * version_number                - A version number to be passed to the library; defaults to Wordpress version number, (optional).
   * in_footer                     - Wether or not to load the library in the document footer rather than the header.
   * javascripts_or_stylesheets    - Whether to load a javascript or a stylesheet asset
   *
   * returns nothing
   */
  function wp_enqueue_asset($asset_id, $asset_src, $dependencies, $version_number, $in_footer, $javascripts_or_stylesheets) {

      $loader_function = ($javascripts_or_stylesheets === 'javascripts') ?
        'wp_enqueue_script' :
        'wp_enqueue_style';

      call_user_func_array($loader_function, array($asset_id,
        plugins_url($asset_src , dirname(__FILE__)),
        $dependencies, $version_number, $in_footer
      ));
  }

  /*
   * Registers the annotator assets (as specified in $conf) and enqueues them using
   * the wordpress loading functions
   *
   * javascripts_or_stylesheets - Whether it should load javascripts or stylesheets.
   *
   * returns nothing
   */

  private function load_assets($javascripts_or_stylesheets) {
    foreach($this->conf->$javascripts_or_stylesheets as $asset) {

      $assid= $this->asset_id($asset->file);

      $in_footer = isset($asset->in_footer) && $asset->in_footer;
      $this->wp_enqueue_asset(
        $this->asset_id($asset->file),
        "{$this->conf->assets_basepath}/{$asset->file}",
        isset($asset->dependencies) ? $asset->dependencies : array(),
        false,
        $in_footer,
        $javascripts_or_stylesheets
      );

    }
  }

  function inject() {
    //TODO: implement the annotator inclusion logic here ; design a simple mechanism whereby the
    //      user can specify in what URL patterns or content types the annotator should be included.

    add_action('wp_print_styles', array($this,'load_stylesheets'));
    add_action('wp_print_scripts', array($this,'load_javascripts'));
  }
}
?>