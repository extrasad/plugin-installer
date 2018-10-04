<?php
 
/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://www.cmantikweb.com/
 * @since      1.0.0
 *
 * @package    Plugin_Processor
 * @subpackage Plugin_Processor/admin/controllers
 */
 
/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, the post fetch functionality
 * and the JavaScript for loading the Media Uploader.
 *
 * @package    Plugin_Processor
 * @subpackage Plugin_Processor/admin/controllers
 * @author     CmantikWeb <servio@cmantikweb.com>
 */

  class Plugin_Processor {

    /**
     * The slugs to be used to retrieve information from Wordpress.org API
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugins_slugs    The slugs used to retrieve info about plugins
     */
    private $plugins_slugs;

    /**
     * Initializes the Plugin Processor Controller 
     * by defining hooks, properties and dependencies.
     * 
     * @since  1.0.0
     */

    public function __construct() {

      // This is necessary to bring the plugins_api() function
      require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
      require_once( ABSPATH . 'wp-admin/includes/plugin.php' );


      /* Use this array to determinate the plugins that will be downloaded,
       * uncomment the plugins array and, innsert the plugin's slug in the array to 
       * determine which plugins will be installed. 
      */

      $this->plugins_slugs = [
        'wordpress-seo',
        'jetpack',
        'uk-cookie-consent'
      ];

      /** Plugins admitted for submission
       * 
       */

      $this->plugins_allowed = [
        'uk-cookie-consent',
        'jetpack',
        'akismet'
      ];

      // Hooks
      add_action( 'admin_enqueue_scripts',array( $this, 'pinst_processor_js' ));
      add_action( 'wp_ajax_pinst_get_plugin_info', array( $this, 'pinst_get_plugin_info' ));
      add_action( 'wp_ajax_pinst_process_plugin', array( $this, 'pinst_process_plugin' ));
      add_action( 'wp_ajax_pinst_download_from_url', array( $this, 'pinst_download_from_url' ));

    }

    /**
     * This function do the download and install process 
     *
     * @since 1.0.0
     */

    public function pinst_process_plugin() {

      $json = [];
      $unpack;

      // Args to be used in the download and Unpack functionalities 
      $args = array(
        'path' => ABSPATH.'wp-content/plugins/',
        'preserve_zip' => false
      );

      $plugin_unpacked = false;

      if (isset($_POST['download_link']) && isset($_POST['slug']) && isset($_POST['name'])) {

        $slug = $_POST['slug'];
        $download_link = $_POST['download_link'];
        $name = $_POST['name'];

        $download = $this->pinst_download_plugin( $download_link, $args['path'] . $slug . '.zip');

        /* Checking if the download process was successful or failed to
        continue the process, if the download failed, the process will stop*/

        if (!$download){

          $status = [
            'status' => 'failed',
            'msg' => 'You have bad internet connection. Check your connection and try again.',
          ];
          array_push($json, $status);

        } else {
          
          $unpack = $this->pinst_unpack_plugin($args, $args['path'] . $slug . '.zip');
          $plugin_unpacked = $unpack[0] === true ? true : false;

        }
      }

      /* Checking if plugins coming from repositories and local were successfully unzipped to
      proceed Installation and Activation.*/

      if ($plugin_unpacked === true) {

        $status = [
          'status' => 'success',
          'msg' => $name.' '.'was installed successfully.'
        ];

        array_push($json, $status);

        //This is for the same plan of activation !!

        // $plugin_file = $this->get_plugin_file($name);
        // $plugin_file_path = $args['path'] . $plugin_file;
        // $data = get_plugin_data($plugin_file_path);



        /* Checking if the install process was successful or failed to
        finish the process*/

        /**ATTENTION IF YOU WANT TO ADD ACTIVATION FUNCIONALITY HERE YOU CAN DO IT */
        
        //$activate = $this->pinst_activate_plugin($plugin_file_path);

        //array_push($json, $activate);

      } else {


        $status = array(
          'status' => 'failed',
          'msg' => $name . $unpack[0]
        );

        array_push($json, $status);
        
      }

      wp_send_json($json);

      wp_die();

    }

    /**
     * It retrieves the plugin main file
     *
     * @since 1.0.0
     */

    public function get_plugin_file( $plugin_name ) {
      require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

      $plugins = get_plugins();

      foreach( $plugins as $plugin_file => $plugin_info ) {
          if ( $plugin_info['Name'] == $plugin_name ) {
            return $plugin_file;
          }
      }
      return null;
    }

    /**
     * It downloads the plugin package and store it in the plugins folder
     *
     * @since 1.0.0
     */

    public function pinst_download_plugin($url, $path){
      $ch = curl_init($url);
      $status;

      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $data = curl_exec($ch);
      curl_close($ch);
      if (file_put_contents($path, $data)){
        $status = true;
        return $status;
			} else {
        $status = false;
			  return $status;
			}
    }

    /**
     * It Unpack the zip file in the plugins folder
     *
     * @since 1.0.0
      */

    public function pinst_unpack_plugin($args, $filename){
      $status = [];
      $zip = zip_open($filename); 
      if(is_resource($zip)){
        while($entry = zip_read($zip))
        {
          $file_check = substr(zip_entry_name($entry), -1) == '/' ? false : true;
          $file_path = $args['path'].zip_entry_name($entry);
          if($file_check){
            if(zip_entry_open($zip,$entry,"r")){
              $fstream = zip_entry_read($entry, zip_entry_filesize($entry));
              file_put_contents($file_path, $fstream );
              chmod($file_path, 0777);
            }
            zip_entry_close($entry);
          }
          else{
            $dir = $args['path'].zip_entry_name($entry);
            $check_dir = file_exists($dir) && is_dir($dir);
            if(!$check_dir){
                mkdir($file_path);
                chmod($file_path, 0777);
              }else{
                $msg = 'Plugin is already installed';
                array_push($status, $msg);
                break;
              }
            }
          }
        zip_close($zip);
        } else{
          echo '<p> You have provided a wrong file path. Check the filepath and try again. <p><br>';
        }
      if  ($args['preserve_zip'] === false) {
        unlink($filename);
      }

      if  (!$check_dir) {
        $check = true;
        array_push($status,$check);
        return $status;
      } else {
        $check = false;
        array_push($status,$check);
        return $status;
      }
    }


    /**
     *  This activates the plugin.
     *
     * @since 1.0.0
    */

    public function pinst_activate_plugin($plugin_file_path){
      $activate = activate_plugin($plugin_file_path);
      $data = get_plugin_data($plugin_file_path);

      if (!$activate) {

        $msg = $data['Name'].' '.'was successfully activated.';
        return $msg;

      } else {

        return $activate;

      }

    }



    /**
     * This is the javascript needed for the controllers
     * to render data and operate in WordPressOrg API
     *
     * @since 1.0.0
     */

    public function pinst_processor_js() {

      wp_enqueue_script(
        'ajax-script',
        PLUGIN_INSTALLER_PLUGIN_URI.'admin/js/admin.js',
        array( 'jquery' )
      );
  
      wp_localize_script(
        'ajax-script', 'pinst_processor', array(
          'plugins' => $this->plugins_slugs,
          'plugins_allowed' => $this->plugins_allowed
        )
      );

      wp_localize_script(
        'ajax-script', 'pinst_plugin', array(
          'url' => PLUGIN_INSTALLER_PLUGIN_URI
        )
      );

    }

}

new Plugin_Processor();