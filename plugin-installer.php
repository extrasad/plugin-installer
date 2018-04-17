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
    $plugin = plugin_basename( __FILE__ );
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    $this->local_args =  array(
      'path' => ABSPATH.'wp-content/plugins/',
      'preserve_zip' => true
    );

    //-------------------------------------------------------------------
    // GO TO LINE 329 FOR WORDPRESS REPOSITORIES PLUGIN DOWNLOAD/INSTALL
    //-------------------------------------------------------------------


    //-------------------------------------------------------------------
    // THIS SECTION IS FOR LOCAL PLUGINS INSTALLATION LIKE ZIP FILES
    //-------------------------------------------------------------------
    /* Use this array to determinate the local or private plugins that will
    be downloaded, installed and activated. Provide the array with the
    full path of the file, example: '/home/user/wordpress-seo.7.1.zip',
    and with the slug of the plugin, example : 'wordpress-seo'.
    Full example, insert this for each plugin to include*/
    
    $this->local_plugins = array(
      array(
        'path' => '/home/user/track-message.zip', // REPLACE WITH YOUR PLUGIN PATH
        'slug' => 'track-message' // AND SLUG
      )
    );

    add_action( 'admin_menu', array( $this, 'plginstMenu' ));
    add_action( 'admin_enqueue_scripts',array( $this, 'enqueue_scripts' ));
    add_action( 'wp_ajax_takePlugins', array( $this, 'takePlugins') );
    add_action( 'wp_ajax_extractLocalPlugins', array( $this, 'extractLocalPlugins') );
    add_filter( "plugin_action_links_$plugin", array($this, 'customSettingsLink' ));
  }
  
    // Main menu link
  public function customSettingsLink($links) {
    $link = (admin_url('/options-general.php?page=plugin-installer')); 
    $settings_link = sprintf('<a href="%s">' .(esc_html( 'Settings')) . '</a>', esc_url($link));
    array_push($links, $settings_link);
      return $links;
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
    <h3>Plugins to Install</h3>
    <ul id="plugin-slugs">
    </ul>
    <h3>Local Plugins to Install</h3>
    <?php $this->viewLocalPlugins(); ?>
    <button id="install-action" class="button button-primary">Install Plugins</button>
    <div id="load-spinner"></div>
    <ul id="list">
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

    $json = array(
      'success' => array(),
      'failed' => array(),
      'msg' => array()
    );

    if(isset($_POST['plugins'])){

      $plugins = $_POST['plugins'];

      /*Checking if the list of plugins is empty, if isn't empty
      execute the request to the API of wordpress.org*/

      if(!empty($plugins)){
        foreach($plugins as $plugin){
            $this->api = plugins_api( 'plugin_information', array(
              'slug' => $plugin,
              'fields' => array(
                'downloadlink
                ' => true,
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

          $installed_plugin = null;

          if ($unpack === true){
            $this->plugin_folder = ("/".$this->api->slug);
            $var = get_plugins($this->plugin_folder);
            foreach(array_keys($var) as $key){
              $this->install = $this->plugin_folder."/".$key;
            }

            $plugin_name = basename($installed_plugin, '.php');

            $install = $this->PluginActivate($this->install);
          
            /* Checking if the install process was successful or failed to
          finish the process*/

            if($install === true) {
              $success = 'success';
              array_push($json['success'],$success);
              $msg = $plugin_name.' was successfully installed. bleble';
              array_push($json['msg'],$msg);

            } else{
              $failed = 'failed';
              array_push($json['failed'],$failed);
              $msg = 'bleble There was an error installing '.$plugin_name;
              array_push($json['msg'],$msg);
            }
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
          $installed_plugin = null;

          if($unpack_local === true){
            $this->plugin_folder_local = ("/".$plugins['slug']);
            $var = get_plugins($this->plugin_folder_local);
            foreach(array_keys($var) as $key){
              $installed_plugin = $key;
              $this->install_local = $this->plugin_folder_local."/".$key;
            }

            $plugin_name = basename($installed_plugin, '.php');

            $install_local = $this->PluginActivate($this->install_local);
            
            /* Checking if the install process was successful or failed to
            finish the process*/

            if($plugin_name == true){
              $success = 'success';
              array_push($json['success'],$success);
              $msg = $plugin_name.' '.'was successfully installed.';
              array_push($json['msg'],$msg);
            }else{
              $failed = 'failed';
              array_push($json['failed'],$failed);
              $msg = 'There was an error installing'.' '.$plugin_name .'.';
              array_push($json['msg'],$msg);
            }
          }	
        }		
      }
    }
    
    wp_send_json($json);
    
    wp_die();        
  }

  public function viewLocalPlugins(){
    $html = ('<ul>');
    foreach ($this->local_plugins as $slug){
      $html .= sprintf('<li>%s</li>',$slug['slug']);
    }
    $html .= ('</ul>');
    echo $html;
    return $this;
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
        If you don't want to install plugins from repositories leave in blank the array
        like this 
        ------
        'plugins' => array(
          ''
        )
        ------
        installed and activated. USE THE PLUGIN'S SLUG IN THE ARRAY. 
        Example : 'jetpack', 'uk-cookie-consent' */
        'plugins' => array(
         'wordpress-seo' // REPLACE WITH THE SLUG FOR THE PLUGIN YOU WANT TO INSTALL
        )
      ));

    wp_enqueue_style( 'plugin-installer', plugin_dir_url( __FILE__ ) . 'assets/installer.css');
  }
}

new PluginInstaller();