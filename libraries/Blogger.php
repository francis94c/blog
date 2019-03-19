<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Blogger {

  private $db;

  private $ci;

  private $dbforge;

  function __construct() {
    if (func_num_args() > 0) $params = func_get_arg(0);
    $this->ci =& get_instance();
    $this->ci->load->database();
    //$this->ci->load->splint("francis94c/ci-preference", "+CIPreferences", array(
    //  "file_name" => "blogger_config.json"
    //), "prefs");
    $this->ci->load->database();
    $this->db =& $this->ci->db;
  }
  /**
   * [install description]
   * @param  [type] $adminTableName          [description]
   * @param  [type] $adminIdColumnName       [description]
   * @param  [type] $adminIdColumnConstraint [description]
   * @return [type]                          [description]
   */
  function install($adminTableName = null, $adminIdColumnName = null, $adminIdColumnConstraint = null) {
    $this->ci->load->dbforge();
    $this->ci->dbforge->add_field("id");
    $fields = array(
      "title" => array(
        "type"       => "VARCHAR",
        "constraint" => 70,
      ),
      "content" => array(
        "type" => "TEXT"
      ),
      "date_published" => array(
        "type" => "TIMESTAMP",
        "null" => true
      ),
      "published" => array(
        "type" => "TINYINT"
      ),
      "hits"      => array(
        "type"       => "INT",
        "constraint" => 7,
        "default"    => 0
      )
    );
    $constrain = $adminTableName !== null && $adminIdColumnName !== null &&
    $adminIdColumnConstraint !== null;
    if ($constrain) {
      $fields["poster_id"] = array(
        "type"       => "INT",
        "constraint" => $adminIdColumnConstraint
      );
      $this->ci->dbforge->add_field($fields);
      $this->ci->dbforge->add_field("date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
      $this->ci->dbforge->add_field(
        "FOREIGN KEY (poster_id) REFERENCES $adminTableName($adminIdColumnName)");
    }
    $attributes = array('ENGINE' => 'InnoDB');
    if (!$this->ci->dbforge->create_table("blogger_posts", true, $attributes)) return false;
    return true;
  }
  /**
   * [loadHeaderScripts description]
   * @param  boolean $w3css [description]
   * @return [type]         [description]
   */
  function loadScripts($w3css=true) {
    $this->ci->load->splint("francis94c/blog", "-header_scripts", array(
      "w3css" => $w3css
    ));
  }
  /**
   * [loadCreateView description]
   * @return [type] [description]
   */
  function loadCreateView($callback) {
    $this->ci->load->helper("form");
    $this->ci->load->splint("francis94c/blog", "-post_edit", array(
      "callback" => "Admin/token",
      "type"     => "create",
      "callback" => $callback
    ));
  }
  function savePost() {

  }
}
?>
