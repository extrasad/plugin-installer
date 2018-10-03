<?php
  /**
   * Plugin Installer
   *
   * Plugin Installer allows you to select a list of plugins to install.
   *
   * @package   Plugin_Installer
   * @author    CmantikWeb <servio@cmantikweb.com> , Dev Abdiangel Urdaneta <abdiangel.30@gmail.com>, Dev Carlos Rivas <carlosr1765@gmail.com>
   * @license   GPL-2.0+
   * @link      http://cmantikweb.com
   * @copyright 2018 CmantikWeb
   *
   * Plugin Name: Plugin Installer
   * @wordpress-plugin
   * Plugin Name: Plugin Installer
   * Plugin URI:  TODO
   * Description: Plugin Installer allows you to select a list of plugins to install.
   * Version:     1.0.0
   * Author:      CmantikWeb, Dev. Abdiangel Urdaneta, Dev. Carlos Rivas
   * Author URI:  http://www.cmantikweb.com/
   * License: GPLv2 or later
   * License URI: https://opensource.org/licenses/GPL-2.0
   */

    // If this file is called directly, abort.
    if ( ! defined( 'WPINC' ) ) {
        die;
    }

    // Sets debugger to false or true, to display errors during development

    $debug = true;

    if($debug){
        error_reporting(-1);
        ini_set('display_errors', 'On');
    }

  /**
   * Includes the core plugin class for executing the plugin.
   */
    require_once( plugin_dir_path( __FILE__ ) . 'admin/class-admin-plugin-installer.php' );

    if ( !defined('PLUGIN_INSTALLER_PLUGIN_DIR') ) {
        define ( 'PLUGIN_INSTALLER_PLUGIN_DIR', plugin_dir_path(__FILE__));
    }

    if ( !defined('PLUGIN_INSTALLER_PLUGIN_URI') ) {
        define ( 'PLUGIN_INSTALLER_PLUGIN_URI', plugin_dir_url(__FILE__));
    }
    // if ( !defined('CARACTERISTICAS_ICON_PLUGIN_FILES_URI') ) {
    //     define ( 'CARACTERISTICAS_ICON_PLUGIN_FILES_URI', get_site_url().'/wp-content/uploads/wp-caracteristicas-plugin/');
    //     }
    // if ( !defined('CARACTERISTICAS_ICON_PLUGIN_FILES_DIR') ) {
    //     define ( 'CARACTERISTICAS_ICON_PLUGIN_FILES_DIR', ABSPATH.'wp-content/uploads/wp-caracteristicas-plugin/');
    //     }
  /**
   * Begins execution of the plugin.
   *
   * Since everything within the plugin is registered via hooks,
   * then kicking off the plugin from this point in the file does
   * not affect the page life cycle.
   *
   * @since    0.1.0
    */
    function run_plugin_installer() {

        $plugin = new Plugin_Installer();
        $plugin->run();

    }

    run_plugin_installer();