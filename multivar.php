<?php
/*
Plugin Name: Multisite Variables
Plugin URI: http://ravennainteractive.com
Description: Allows a template of variables
Version: 0.1
Author: Beau Collins
Author URI: http://beaucollins.com
License: GPL2
*/

global $multivars_table_name;
global $wpdb;
$multivars_table_name = $wpdb->base_prefix . 'sitevariables';

add_action('admin_menu', 'multivars_template_menu');


if(!function_exists('multivars_template_menu')){
  
  function multivars_template_menu(){

      add_submenu_page('ms-admin.php', 'Site Variable Template', 'Site Variables', 'super_admin', 'multivars', 'multivars_template');
      add_options_page('Site Variables', 'Site Variables', 'manage_options', 'multivars-values', 'multivars_options');

  }
  
}


function multivars_template(){
  ?>
  <div class="wrap">
    <h2>Site Variable Template</h2>
    <form method="post" action="options.php">
      <table class="form-table">
        <tr>
          <th scope="row'"><label>Variable Name<input type="text" name="sitevariables[key_name]" value="" /></label></th>
          <td><textarea></textarea></td>
        </tr>
      </table>
    </form>
  </div>
  <?php
}

function multivars_options(){
  
  global $wpdb, $multivars_table_name;
  
  ?>
  
  <div class="wrap">
    <h2>Site Variables</h2>
    <form method="post" action="options.php">
      <?php wp_nonce_field('update-options'); ?>
      <table class="form-table">
        <tr valign="top">
          <th scope="row"><?php echo $multivars_table_name; ?></th>
        </tr>
      </table>
    </form>
    
  </div>
  
  <?php
}

register_activation_hook(__FILE__, 'multivars_install');

function multivars_install(){
  
  global $wpdb, $multivars_table_name;
  
  if($wpdb->get_var("SHOW TABLES LIKE '$multivars_table_name'") != $multivars_table_name) {
    
    $sql = "CREATE TABLE " . $multivars_table_name . " (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        key_name varchar(255) NOT NULL,
        description text NOT NULL,
        default_value text NOT NULL,
        UNIQUE KEY key_name (key_name),
        PRIMARY KEY  id (id)
      );";
            
      
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);
      
      $rows_affected = $wpdb->insert( $multivars_table_name, array( 'key_name' => 'Awesome', 'description' => 'Is it awesome?', 'default_value' => 'Awesome' ) );
            
    
  } else {
    // we can't install
  }
}

register_deactivation_hook(__FILE__, 'multivars_uninstall');

function multivars_uninstall(){
  global $wpdb, $multivars_table_name;
  
  error_log("Deactivating plugin");
  
  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  $wpdb->query("DROP TABLE " . $multivars_table_name);
}


