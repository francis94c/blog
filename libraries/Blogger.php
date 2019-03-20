<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Blogger {

  private $db;

  private $ci;

  private $dbforge;

  const CREATE = "create";

  const CREATE_AND_PUBLISH = "create_and_publish";

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
  /**
   * [savePost description]
   * @return [type] [description]
   */
  function savePost() {
    $this->load->model("BlogManager", "blogger");
    if ($this->ci->input->post("action") == "save") {
      $id = $this->ci->security->xss_clean($this->ci->input->post("id"));
      if ($id != "") {
        $this->ci->blogger->savePost($this->ci->input->post("id"), $this->ci->security->xss_clean($this->ci->input->post("title")), $this->ci->security->xss_clean($this->ci->input->post("editor")));
      } else {
        $this->ci->blogger->createPost($this->ci->security->xss_clean($this->ci->input->post("title")), $this->ci->security->xss_clean($this->ci->input->post("editor")));
      }
    }
    if ($this->ci->input->post("action") == "publish" || $this->ci->input->post("action") == "createAndPublish") {
      $id = $this->ci->security->xss_clean($this->ci->input->post("id"));
      if ($id != "") {
        $this->ci->blogger->publishPost($id);
      } else {
        $this->ci->blogger->createAndPublishPost($this->ci->security->xss_clean($this->ci->input->post("title")), $this->ci->security->xss_clean($this->ci->input->post("editor")));
      }
    }
    $action = $this->ci->input->post("action");
    if ($action == "createAndPublish") {
    } elseif ($action == "create") {
      return self::CREATE;
    } 
  }
}
?>
