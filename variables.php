<?php


class MultiVarVariables {
  
  var $wpdb = false;
  var $variables = null;
  var $table_name = null;
  var $keys = null;
  var $values = null;
  var $option_name = 'multivar_values';
  
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
  
  function values(){
    if($this->values == null){
      $this->values = get_option($this->option_name, array());
    }
    return $this->values;
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
  
  function idForName($name){
    foreach ($this->all() as $index => $variable) {
      if (strtoupper($variable['key_name']) == strtoupper($name)) {
        return intval($variable['id']);
      }
    }
    return false;
  }
  
  function valueFor($id_or_name){
    if(is_int($id_or_name)){
      //look up value by id
      $values = &$this->values();
      return $values[$id_or_name];
    }else{
      $id = $this->idForName($id_or_name);
      //look up id for given string
      return $this->valueFor($id);
    }
    
  }
  
  function setValueFor($id_or_name, $value){
    if(is_int($id_or_name)){
      //look up value by id
      $this->values();
      $this->values[$id_or_name] = $value;
    }else{
      $id = $this->idForName($id_or_name);
    }
  }
  
  function save(){
    add_option($this->option_name, $this->values);
  }
  
}


// some tests
if ( $argv[0] == __FILE__ ) {
  
  $var = new MultiVarVariables();
}