<?php


class MultiVarVariables {
  
  var $wpdb = false;
  var $variables = null;
  var $table_name = null;
  var $keys = null;
  
  function __construct($wpdb = null, $table_name = null){
    $this->wpdb = $wpdb;
    $this->table_name = $table_name;
  }
  
  function all(){
    if($this->variables == null){
      $this->variables = $this->queryAll();
    }
    return $this->variables;
  }
  
  function hasDb(){
    return !is_null($this->wpdb) && $this->wpdb != false;
  }
  
  function queryAll(){
    if($this->hasDb()){
      return $this->wpdb->get_results("SELECT * FROM {$this->table_name}", ARRAY_A);
    }else{
      return array();
    }
  }
  
  function allKeys(){
    if($this->keys == null){
      $keys = array();
      foreach($this->all() as $variable){
        array_push($keys, strtoupper($variable['key_name']));
      }
      $this->keys = $keys;
    }
    return $this->keys;
  }
  
  function valid($variable){
    if (empty($variable['key_name'])){
      return false;
    }
    if (in_array(strtoupper($variable['key_name']), $this->allKeys())){
      return false;
    }
    return true;
  }
  
}


// some tests
if ( $argv[0] == __FILE__ ) {
  
  $var = new MultiVarVariables();
}