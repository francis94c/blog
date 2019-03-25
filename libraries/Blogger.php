<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Blogger {

  private $ci;

  private $dbforge;

  private $table_name;

  const TABLE_PREFIX = "blogger_posts";

  const PACKAGE = "francis94c/blog";

  const CREATE = "create";

  const CREATE_AND_PUBLISH = "createAndPublish";

  const EDIT = "edit";

  const PUBLISH = "publish";

  function __construct($params=null) {
    $this->ci =& get_instance();
    $this->ci->load->database();
    $this->table_name = self::TABLE_PREFIX . (isset($params["name"]) ? "_" . $params["name"] : "");
    $this->ci->load->database();
  }
  /**
   * [install description]
   * @param  [type] $adminTableName          [description]
   * @param  [type] $adminIdColumnName       [description]
   * @param  [type] $adminIdColumnConstraint [description]
   * @return [type]                          [description]
   */
  function install($adminTableName = null, $adminIdColumnName = null, $adminIdColumnConstraint = null, $blogName=null) {
    $blogName = $blogName == null ? $this->table_name : self::TABLE_PREFIX . "_" . $blogName;
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
    if (!$this->ci->dbforge->create_table($blogName, true, $attributes)) return false;
    return true;
  }
  /**
   * [setBlog description]
   * @param [type] $name [description]
   */
  function setName($name) {
    $this->table_name = self::TABLE_PREFIX . "_" . $name;
  }
  /**
   * [getBlog description]
   * @return [type] [description]
   */
  function getName() {
    return $this->table_name;
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
  function loadEditor($callback, $postId=null) {
    $this->ci->load->helper("form");
    $data = array(
      "callback" => "Admin/token",
      "type"     => "create",
      "callback" => $callback
    );
    if ($postId != null) $data["id"] = $postId;
    $this->ci->load->splint("francis94c/blog", "-post_edit", $data);
  }

  function savePost($posterId=null) {
    $this->ci->load->splint(self::PACKAGE, "*BlogManager", "bmanager");
    if ($this->ci->input->post("action") == "save") {
      $id = $this->ci->security->xss_clean($this->ci->input->post("id"));
      if ($id != "") {
        $this->ci->bmanager->savePost($this->ci->input->post("id"), $this->ci->security->xss_clean($this->ci->input->post("title")), $this->ci->security->xss_clean($this->ci->input->post("editor")), $posterId);
      } else {
        $this->ci->bmanager->createPost($this->ci->security->xss_clean($this->ci->input->post("title")), $this->ci->security->xss_clean($this->ci->input->post("editor")), $posterId);
      }
    }
    if ($this->ci->input->post("action") == "publish" || $this->ci->input->post("action") == "createAndPublish") {
      $id = $this->ci->security->xss_clean($this->ci->input->post("id"));
      if ($id != "") {
        $this->ci->bmanager->publishPost($id, $posterId);
      } else {
        $this->ci->bmanager->createAndPublishPost($this->ci->security->xss_clean($this->ci->input->post("title")), $this->ci->security->xss_clean($this->ci->input->post("editor")), $posterId);
      }
    }
    $action = $this->ci->input->post("action");
    if ($action == "createAndPublish") {
      return self::CREATE_AND_PUBLISH;
    } elseif ($action == "save") {
      if ($id == "") return self::CREATE;
      return self::EDIT;
    } elseif ($action == "publish") {
      return self::PUBLISH;
    }
    return false;
  }
}
?>
