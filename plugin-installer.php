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

	private $plugins;
  private $install;
  private $install_local;
	private $api;
  private $plugin_folder;
  private $local_plugins;
  private $plugin_folder_local;
  private $local_args;


  public function __construct(){
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    $this->local_args =  array(
      'path' => ABSPATH.'wp-content/plugins/',
      'preserve_zip' => true
    );
		/* Use this array to determinate the plugins that will be downloaded,
    installed and activated. USE THE PLUGIN'S SLUG IN THE ARRAY. 
    Example : 'jetpack', 'wordpress-seo' */
		$this->plugins = array(
      //'jetpack'
    );
    /* Use this array to determinate the local or private plugins that will
    be downloaded, installed and activated. Provide the array with the
    full path of the file, example: '/home/user/wordpress-seo.7.1.zip',
    and with the slug of the plugin, example : 'wordpress-seo'.
    Full example, insert this for each plugin to include: 
    
      array(
      'path' => '/home/user/track-message.zip',
      'slug' => 'track-message'
      ),

    By default is an empty array.
    */
    $this->local_plugins=array(
    /*  array(
        'path' => '/home/user/wordpress-seo.7.1.zip',
        'slug' => 'wordpress-seo'
        ) */    
    );

		//Uncomment the line below if you want to use the plugin.
		add_action('init', $this->takePlugins($this->plugins, $this->local_plugins));		
	}

  // Main plugin function.
  public function takePlugins($plugins, $local_plugins){
    include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
    $success = '<p> plugin installed and activated </p>';
    $failure ='<p> cannot install plugin </p>';
    $args = array(
      'path' => ABSPATH.'wp-content/plugins/',
      'preserve_zip' => false
    );
    /*Checking if the list of plugins is empty, if isn't empty
    execute the request to the API of wordpress.org*/
    if(!empty($this->plugins)){
		  foreach($this->plugins as $plugin){
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
          echo $success;		
          }else{
            echo $failure;
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
        if($unpack_local === true){
          $this->plugin_folder_local = ("/".$plugins['slug']);
          $var = get_plugins($this->plugin_folder_local);
		      foreach(array_keys($var) as $key){
            $this->install_local = $this->plugin_folder_local."/".$key;
          }
          $install_local = $this->PluginActivate($this->install_local);
          /* Checking if the install process was successful or failed to
          finish the process*/
          if($install_local === true){
          echo $success;		
          }else{
            echo $failure;
          }
          }
        }						
      }		
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
}

new PluginInstaller();
