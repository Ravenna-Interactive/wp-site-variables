<?php
/*
Plugin Name: Multisite Variables
Plugin URI: http://ravennainteractive.com
Description: Allows a template of variables
Version: 0.1
Author: Beau Collins
Author URI: http://ravennainteracive.com
License: GPL2
*/

global $multivars_table_name, $multivars_variables, $wpdb;
$multivars_table_name = $wpdb->base_prefix . 'sitevariables';

$multivars_path_info = pathinfo(__FILE__);
define('MULTIVARS_PATH', $multivars_path_info['dirname']);

require(MULTIVARS_PATH . '/variables.php');

$multivars_variables = new MultiVarVariables($wpdb, $multivars_table_name);


function multivars_init() {
  add_action('admin_menu', 'multivars_template_menu');
  
  multivars_admin_save();
  
}

add_action('init', 'multivars_init');

function multivars_template_menu(){

    add_submenu_page('ms-admin.php', 'Site Variable Template', 'Site Variables', 'super_admin', 'multivars', 'multivars_template');
    add_options_page('Site Variables', 'Site Variables', 'manage_options', 'multivars-values', 'multivars_options');

}

function multivars_admin_save(){
  global $multivars_table_name, $multivars_variables, $wpdb;
  
  if ( ! current_user_can( 'manage_network_options' ) )
    wp_die( __( 'You do not have permission to access this page.' ) );
  
  
  if ( $_POST['action'] == 'multivars_save_variables' ){
    
    $errors = array();
    
    foreach($_POST['sitevariables'] as $id => $variable){
      // validate the values
      
      if($multivars_variables->valid($variable)){
        if ( preg_match('/new/', $id) ) {
          $wpdb->insert($multivars_table_name, $variable);
        }else{
          $wpdb->update($multivars_table_name, $variable, array('id' => $id));
        }
      }else{
        array_push($errors, $variable);
      }
      
    }
    
    wp_redirect( add_query_arg( array( 'updated' => 'true' ), wp_get_referer() ) );
    exit;
  }
}


function multivars_template(){
  if ( ! current_user_can( 'manage_network_options' ) )
    wp_die( __( 'You do not have permission to access this page.' ) );
 
  global $multivars_variables;
  switch ($_POST['action']) {
    case 'multivars_save_variables':
      $variables = array();
      break;
    default:
      if(count($multivars_variables->all()) == 0){
        $variables = array(array('id' => 'new', 'name' => '', 'default_value' => '', 'description' => ''));
      }else{
        $variables = $multivars_variables->all();
      }
      break;
  }
  
  
  ?>
  <div class="wrap">
    <?php if ($_GET['updated']): ?>
    <div class="message updated">
      <p>Variables saved.</p>
    </div>
    <?php endif; ?>
    <h2>Site Variable Template</h2>
    <p>Provide the variables and default values that can be filled in by network sites.</p>
    <form method="post" action="ms-admin.php?page=multivars">
      <p class="submit">
        <input type="hidden" name="action" value="multivars_save_variables" />
        <a href="#" class="button add-new-variable">Add New</a>
        <input type="submit" value="Apply Changes" />
      </p>
      <table class="widefat" id="multivariables">
        <thead>
          <tr>
            <th>Name</th>
            <th>Default</th>
            <th>Description</th>
            <th></th>
          </tr>
        </thead>
        <?php
        foreach($variables as $variable):
          $class = ( 'alt' == $class ) ? '' : 'alt';
          
        ?>
        <tr id="multivariable_<?php echo $variable['id'] ;?>" valign="top" class="<?php echo $class; ?>">
          <td scope="row'"><input type="text" style="width:90%;" size="25" name="sitevariables[<?php echo $variable['id']; ?>][key_name]" value="<?php echo $variable['key_name'] ;?>" />
            <p><em>Required</em></p></td>
          <td><textarea cols="25" name="sitevariables[<?php echo $variable['id'] ;?>][default_value]" style="width:100%; max-width:100%;"><?php echo $variable['default_value'] ?></textarea></td>
          <td><textarea cols="25" name="sitevariables[<?php echo $variable['id'] ;?>][description]" style="width:100%; max-width:100%;"><?php echo $variable['description'] ?></textarea></td>
          <td style="vertical-align: middle;"><a href="#" class="button delete">Delete</a></td>
        </tr>
        <?php endforeach; ?>
      </table>
      <p class="submit">
        <a href="#" class="button add-new-variable">Add New</a>
        <input type="submit" value="Apply Changes" />
      </p>
      
    </form>
  </div>
  <script type="text/javascript" charset="utf-8">
    (function($){
      
      $.fn.removeMultiVarRow = function(){
        return this.each(function(index, element){
          var tr = $(element);
          var rows = tr.nextAll();
          var altrows = function(){
            rows.each(function(index, element){
              var next = $(element);
              next[(next.hasClass('alt') ? 'removeClass' : 'addClass')]('alt');              
            })
          }
          
          if(!confirm('Are you sure?')) return true;
          
          if(tr.hasClass('new')){
            tr.remove();
            altrows();
          }else{
            //ajax call
            tr.css({'opacity':'0.5'});
            $.ajax({
              url: ajaxurl,
              type: 'POST',
              data: {
                action: 'multivar_delete_variable',
                id: tr.attr('id').match(/[\d]+$/)[0]
              },
              success:function(){
                tr.remove();
                altrows();
              },
              failure:function(){
                tr.css({'opacity':1});
              }
            })
          }
        });
      }
      
      $(document).ready(function(){
        $('#multivariables').click(function(e){
          e.preventDefault();
          var $target = $(e.target);
          if($target.is('a.delete')){
            // if it's new just remove the row
            var tr = $target.parents('tr');
            tr.removeMultiVarRow();
            
          }
        });
        
        var index = 0;
        
        $('.add-new-variable').click(function(e){
          e.preventDefault()
          var tr_class = $('#multivariables tbody tr:last').hasClass('alt') ? '' : 'alt';
          $('<tr class="new '+tr_class+'">' +
          '<td><input type="text" name="sitevariables[new_'+index+'][key_name]" style="width:90%;"/><p><em>Required</em></p></td>' +
          '<td><textarea cols="25" name="sitevariables[new_'+index+'][default_value]" style="width:100%; max-width:100%;"></textarea></td>' +
          '<td><textarea cols="25" name="sitevariables[new_'+index+'][description]" style="width:100%; max-width:100%;"></textarea></td>' +
          '<td style="vertical-align: middle;"><a href="#" class="button delete">Delete</a></td>' +
          '</tr>').appendTo('#multivariables tbody');
          index ++;
          
        });
      });
    })(jQuery)
  </script>
  <?php
}

add_action('wp_ajax_multivar_delete_variable', 'multivar_delete_variable');

function multivar_delete_variable(){
  if ( ! current_user_can( 'manage_network_options' ) )
    wp_die( __( 'You do not have permission to access this page.' ) );
  
  global $wpdb, $multivars_table_name;
  $id = $_POST['id'];
  echo $wpdb->query($wpdb->prepare("DELETE FROM $multivars_table_name WHERE id = %s", $id));
  die();
}

function multivars_options(){
  
  global $multivars_table_name, $multivars_variables, $wpdb;
  
  ?>
  
  <div class="wrap">
    <h2>Site Variables</h2>
    <form method="post" action="">
      <?php wp_nonce_field('update-options'); ?>
      <p class="submit">
         <input type="submit" name="save_changes" value="<?php _e('Save Changes') ;?>" />
      </p>
      <table class="form-table">
        <?php foreach($multivars_variables->all() as $variable): ?>
        <tr valign="top">
          <th scope="row">
            <label for="multivar_<?php echo $variable['id'] ?>"><?php echo $variable['key_name'] ?></label>
          </th>
          <td>
            <?php if(!empty($variable['description'])): ?>
              <label for="multivar_<?php echo $variable['id'] ?>"><p><?php echo $variable['description'] ?></p></label>
            <?php endif; ?>
            <textarea id="multivar_<?php echo $variable['id'] ?>" cols="70" rows="3"></textarea>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
      <p class="submit">
         <input type="submit" name="save_changes" value="<?php _e('Save Changes') ;?>" />
      </p>
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

