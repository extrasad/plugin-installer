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
	private $plugins_to_download;
	private $install;
	private $api;
	private $plugin_folder;

  public function __construct(){
		include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' ); //for plugins_api..
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		
		/* Use this array to determinate the plugins that will be downloaded,
		installed and activated. USE THE PLUGIN'S SLUG IN THE ARRAY. */
		$this->plugins = array(
			'jetpack', 
			'uk-cookie-consent', 
			'cookie-notice', 
			'ultimate-modal'
		);
		
		//Uncomment the line below if you want to test the plugin.
		//add_action('init', $this->takePlugins($this->plugins));		
	}

  public function takePlugins($plugins)
  {
      $args = array(
              'path' => ABSPATH.'wp-content/plugins/',
              'preserve_zip' => false
      );
  
			foreach($this->plugins as $plugin)
      {
				$this->api = plugins_api( 'plugin_information', array(
					'slug' => $plugin,
					'fields' => array(
							'downloadlink' => true,
							'slug' => true,
					),
				));
        $this->PluginDownload($this->api->download_link, $args['path'].$this->api->slug.'.zip');
				$this->PluginUnpack($args, $args['path'].$this->api->slug.'.zip');
				$this->plugin_folder = ("/".$this->api->slug);
				$var = get_plugins($this->plugin_folder);
				foreach(array_keys($var) as $key)
				{
						$this->install = $this->plugin_folder."/".$key;
				}
				$this->PluginActivate($this->install);						
			}			
  }
  public function PluginDownload($url, $path){
      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $data = curl_exec($ch);
      curl_close($ch);
      if(file_put_contents($path, $data)){
        return true;
			}else{
			 return false;
			}
  }
  public function PluginUnpack($args, $filename){
  
    if($zip = zip_open($filename)){
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
          if(zip_entry_name($entry))
          {
            mkdir($file_path);
            chmod($file_path, 0777);
          }
        }
      }
      zip_close($zip);
      }
    if($args['preserve_zip'] === false)
    {
      unlink($filename);
    }
  }
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
