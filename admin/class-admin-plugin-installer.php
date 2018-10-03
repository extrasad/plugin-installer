<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://www.cmantikweb.com/
 * @since      1.0.0
 *
 * @package    Plugin_Installer
 * @subpackage Plugin_Installer/admin
*/

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, the post fetch functionality
 * and the JavaScript for loading the Media Uploader.
 *
 * @package    Plugin_Installer
 * @subpackage Plugin_Installer/admin
 * @author     CmantikWeb <servio@cmantikweb.com>
*/

  class Plugin_Installer {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $name    The ID of this plugin.
    */

    private $name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The version of the plugin
    */

    private $version;

    /**
     * Initializes the plugin by defining the properties.
     *
     * @since  1.0.0
    */
    
    private $controllers;
    private $views;
    private $results;
    private $post_id;
    private $local_args;

    public function __construct() {

      require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

      $this->initControllers();

      $this->local_args =  [
        'path' => ABSPATH.'wp-content/plugins/',
        'preserve_zip' => true
      ];
      
      $this->name = 'plugin-installer';
      $this->version = '1.0.0';
    }

    /**
     * Defines the hooks that will register and enqueue the JavaScriot
     * and the meta box that will render the option.
     *
     * @since 1.0.0
    */

    public function run() {
      $plugin = plugin_basename( __FILE__ );

    
      add_action( 'admin_menu', array( $this, 'pinst_options_page' ));
      // add_action( 'admin_enqueue_scripts',array( $this, 'enqueue_scripts' ));
      add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
      add_filter( "plugin_action_links_$plugin", array( $this, 'pinst_settings_link' ));

    }

    /**
     * When triggered it appends a link to the settings plugin page
     *
     * @since 1.0.0
    */


    public function pinst_settings_link() {

      $link = (admin_url('/options-general.php?page=plugin-installer')); 
      $settings_link = sprintf('<a href="%s">' .(esc_html( 'Settings')) . '</a>', esc_url($link));
      array_push($links, $settings_link);
      
      return $links;
    }


    /**
     * Hook the Plugins Options Page View to be called
     *
     * @since 1.0.0
    */

    public function pinst_options_page() {

      add_options_page( 'Plugin Installer', 
      'Plugin Installer', 
      'manage_options', 
      'plugin-installer', 
      array($this, 'pinst_options_page_content'));

    }

    /**
     * Renders the view in the Plugin Installer Admin Page
     *
     * @since 1.0.0
    */

    public function pinst_options_page_content() {

      if ( !current_user_can( 'manage_options' ) )  {
          wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
      }

      // Call the admin panel view
      include_once(PLUGIN_INSTALLER_PLUGIN_DIR.'admin/views/admin-view.php');

    }


    /**
     * Registers the JavaScript for handling the media uploader.
     *
     * @since 1.0.0
    */

    public function enqueue_scripts() {
      
      wp_enqueue_script(
          $this->name,
          plugin_dir_url( __FILE__ ) . 'js/admin.js',
          array( 'jquery' ),
          $this->version,
          'all'
      );

    }

    /**
     * Registers the stylesheets for handling the meta box
     *
     * @since 1.0.0
    */

    public function enqueue_styles() {
      
      wp_enqueue_style(
          $this->name,
          plugin_dir_url( __FILE__ ) . 'css/admin.css',
          array()
      );

    }

    /**
     * Init Controllers Classes
     *
     * @since 1.0.0
    */

    private function initControllers() {

      require_once( PLUGIN_INSTALLER_PLUGIN_DIR . 'admin/controllers/class-controller-plugin-processor.php' );

    }
  }