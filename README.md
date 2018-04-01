# plugin-installer

WP plugin to install plugins from a selected list.


## INSTRUCTIONS

To use this plugin, you have to do the following steps: 

1. Download or clone the repo, and install the WP plugin.
2. Add the plugins to the array called 'plugins' in the plugin-installer.php. USE THE PLUGIN'S SLUG(if
you don't use the plugin's slug, there will be a fatal error).
3. Uncomment the line indicated in the plugin-installer.php file.
4. After install all the plugins, proceed to comment the action line you uncommented before in the step 3, and delete
all the plugins from the array 'plugins'. :heavy_exclamation_mark:DON'T LOAD THE PAGE AGAIN,
OR NAVIGATE IN THE PAGE UNTIL COMMENT THE ACTION LINE:heavy_exclamation_mark:
5. Refresh the page, and enjoy!!!
