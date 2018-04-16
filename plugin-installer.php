<?php
 /** 
  *@package Plugin Installer
 */
/*
Plugin Name: Plugin Installer
Description: Plugin Installer allows you to select a list of plugins to install.
Author: CmantikWeb - Dev. Carlos Rivas,  Dev Abdiangel Urdaneta
Author URI: https://cmantikweb.com/
Version: 1.0.0
License: GPLv2 or later
License URI: https://opensource.org/licenses/GPL-2.0
Text Domain: plugin-installer

*/

// Security check
if ( ! function_exists( 'add_action' ) ) {
    echo 'You don\'t have permission to access this file.';
    die;
  }

class PluginInstaller{

  private $install;
  private $install_local;
  private $api;
  private $plugin_folder;
  private $plugin_folder_local;
  private $local_args;
  private $local_plugins;


  public function __construct(){
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    $this->local_args =  array(
      'path' => ABSPATH.'wp-content/plugins/',
      'preserve_zip' => true
    );
    
    // GO TO LINE 333 FOR WORDPRESS REPOSITORIES PLUGIN DOWNLOAD/INSTALL

    /* Use this array to determinate the local or private plugins that will
    be downloaded, installed and activated. Provide the array with the
    full path of the file, example: '/home/user/wordpress-seo.7.1.zip',
    and with the slug of the plugin, example : 'wordpress-seo'.
    Full example, insert this for each plugin to includ*/
    
    /*$this->local_plugins=array(
      array(
          'path' => '/home/abdiangel/track-message.zip',
          'slug' => 'track-message'
        )    
      );*/


		//Uncomment the line below if you want to use the plugin.
    add_action( 'admin_menu', array( $this, 'plginstMenu' ));
    add_action( 'admin_enqueue_scripts',array( $this, 'enqueue_scripts' ));
    add_action( 'wp_ajax_takePlugins', array( $this, 'takePlugins') );
    add_action( 'wp_ajax_extractLocalPlugins', array( $this, 'extractLocalPlugins') );
    //add_action( 'wp_ajax_extractLocalPlugins', array( $this, 'extractLocalPlugins')); AJAX FOR LOCAL PLUGIN INSTALLATION
  }
  
  //Main Menu

  public function plginstMenu(){
    add_options_page( 'Plugin Installer', 
    'Plugin Installer', 
    'manage_options', 
    'plugin-installer', 
    array($this, 'plginstOptionsPage'));
  }

  public function plginstOptionsPage() {
    if (!current_user_can('manage_options')) {
      return;
    }
    ?>
  <div class="wrap">
    <h1>
      <?= esc_html(get_admin_page_title()); ?>
    </h1>
    <h3>Plugins To Install</h3>
    <ul id="plugin-slugs">
    </ul>
    <button id="install-action" class="button button-primary">Install Plugins</button>
    <div id="load-spinner"></div>
    <h3>Local Plugins To Install</h3>
    <input type="file" id="localPluginsZip" name="plugins_zip[]" multiple></input>
    <br/>
    <button id="install-action2" class="button button-primary">Install Local Plugins</button>
    <div id="load-spinner2"></div>
    <h4>Plugins to be Unzip: </h4>
    <ul id="local-plugin-slugs">
    </ul>
  </div>
  <?php
    wp_die();
  }

  // Main plugin function.
  public function takePlugins(){
    
    include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
    $args = array(
      'path' => ABSPATH.'wp-content/plugins/',
      'preserve_zip' => false
    );

    if(isset($_POST['plugins'])){

    /*Checking if the list of plugins is empty, if isn't empty
    execute the request to the API of wordpress.org*/
    if(!empty($plugins)){
      foreach($plugins as $plugin){
          $this->api = plugins_api( 'plugin_information', array(
            'slug' => $plugin,
            'fields' => array(
              'downloadlink' => true,
              'slug' => true,
            ),
        ));
        // Try to download the plugin.
        $download= $this->PluginDownload($this->api->download_link, $args['path'].$this->api->slug.'.zip');
        /* Checking if the download process was successful or failed to
        continue the process, if the download failed, the process will stop*/
        if ($download === true){
          $unpack = $this->PluginUnpack($args, $args['path'].$this->api->slug.'.zip');          
        }
        /* Checking if the unzip process was successful or failed to
        continue the process*/
        if ($unpack === true){
          $this->plugin_folder = ("/".$this->api->slug);
          $var = get_plugins($this->plugin_folder);
		      foreach(array_keys($var) as $key){
            $this->install = $this->plugin_folder."/".$key;
		      }
          $install = $this->PluginActivate($this->install);
        /* Checking if the install process was successful or failed to
        finish the process*/
          if($install === true){
            $status = 'success';
            $msg = 'Successfully installed.';
          }else{
            $status = 'failed';
            $msg = 'There was an error installing';
          }
        }

          $json = array(
            'status' => $status,
            'msg' => $msg,
          );

          wp_send_json($json);
        }		
      }
    }
    /*Checking if the list of plugins is empty, if isn't empty
    execute unzip process.*/
    if(!empty($this->local_plugins)){
      foreach($this->local_plugins as $key => $plugins){
        $unpack_local= $this->PluginUnpack($this->local_args, $plugins['path']);
        /* Checking if the unzip process was successful or failed to
        continue the process*/
        if($unpack_local === true){
          $this->plugin_folder_local = ("/".$plugins['slug']);
          $var = get_plugins($this->plugin_folder_local);
		      foreach(array_keys($var) as $key){
            $this->install_local = $this->plugin_folder_local."/".$key;
          }
          $install_local = $this->PluginActivate($this->install_local);
          /* Checking if the install process was successful or failed to
          finish the process*/
          if($install === true){
            $status = 'success';
            $msg = 'Successfully installed.';
          }else{
            $status = 'failed';
            $msg = 'There was an error installing';
          }
        }
        $json = array(
          'status' => $status,
          'msg' => $msg,
        );

        wp_send_json($json);		
      }		
    }
    wp_die();        
  }

  public function extractLocalPlugins(){
    include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
     /*Checking if the list of plugins is empty, if isn't empty
    execute unzip process.*/
    if (isset($_POST['local_plugins'])){
      $file_post = $_FILES['plugins_zip'];
      $file_ary = array();
      $file_count = count($file_post['name']);
      $file_keys = array_keys($file_post);
  
      for ($i=0; $i<$file_count; $i++) {
          foreach ($file_keys as $key) {
              $file_ary[$i][$key] = $file_post[$key][$i];
          }
      }

      for ($x=0; $x < count($file_ary); $x++){
        $file_ary[$x]['name'] = basename($file_ary[$x]['name'], '.zip');
      }

      if(!empty($file_ary)){
        foreach($file_ary as $plugins){
          $path = $plugins['tmp_name'];

          $unpack_local= $this->PluginUnpack($this->local_args, $path);
          /* Checking if the unzip process was successful or failed to
          continue the process*/
          if($unpack_local === true){
            $this->plugin_folder_local = ("/".$plugins['name']);
            $var = get_plugins($this->plugin_folder_local);
            foreach(array_keys($var) as $key){
              $this->install_local = $this->plugin_folder_local."/".$key;
            }
            $install_local = $this->PluginActivate($this->install_local);
            /* Checking if the install process was successful or failed to
            finish the process*/
            if($install_local === true){
              $status = 'success';
              $msg = 'Successfully installed.';
            }else{
              $status = 'failed';
              $msg = 'There was an error installing';
              }
            }
            $json = array(
              'status' => $status,
              'msg' => $msg,
            );
            wp_send_json($json);
          }		
        }
      }
      wp_die();
  }


  // Function to download the plugin.
  public function PluginDownload($url, $path){
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $data = curl_exec($ch);
      curl_close($ch);
      if(file_put_contents($path, $data)){
        return true;
			}else{
        echo '<p> You have bad internet connection. Check your connection and try again. </p>';
			 return false;
			}
  }
  // Function to unzip the plugin.
  public function PluginUnpack($args, $filename){
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
              echo '<p> Plugin is already installed!!. </p>';
              break;
            }
          }
        }
      zip_close($zip);
      }else{
        echo '<p>You have provided a wrong file path. Check the filepath and try again.<p><br>';
      }
    if($args['preserve_zip'] === false){
      unlink($filename);
    }
    if(!$check_dir){
      return true;
    }else{
      return false;
    }
  }
  //Function to install the plugin.
  public function PluginActivate($install_path){
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    $get_current = get_option('active_plugins');
    $plugin = plugin_basename(trim($install_path));

    if(!in_array($plugin, $get_current)){
      $get_current[] = $plugin;
      sort($get_current);
      do_action('activate_plugin', trim($plugin));
      update_option('active_plugins', $get_current);
      do_action('activate_'.trim($plugin));
      do_action('activated_plugin', trim($plugin));
      return true;
    }
    else
    	return false;
  }
  
  public function enqueue_scripts() {
    wp_enqueue_script(
      'ajax-script',
      plugin_dir_url( __FILE__ ) . 'assets/installer.js',
      array( 'jquery' )
    );

    wp_localize_script(
      'ajax-script', 'ajax_object', array(
        /* Use this array to determinate the plugins that will be downloaded,
        installed and activated. USE THE PLUGIN'S SLUG IN THE ARRAY. 
        Example : 'jetpack', 'uk-cookie-consent' */
        // UNCOMMENT ARRAY BELOW IF YOU WANT TO INSTALL PLUGINS FROM PLUGINS REPOSITORIES
        /*'plugins' => array(
          'jetpack', 'wordpress-seo'
        )*/
      ));

    wp_enqueue_style( 'plugin-installer', plugin_dir_url( __FILE__ ) . 'assets/installer.css');
  }
}

new PluginInstaller();