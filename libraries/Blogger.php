<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Blogger {

  private $ci;

  private $dbforge;

  private $table_name;

  const TABLE_PREFIX = "blogger_posts";

  const PACKAGE = "francis94c/blog";

  const MARKDOWN_PACKAGE = "francis94c/ci-parsedown";

  const CREATE = "create";

  const CREATE_AND_PUBLISH = "createAndPublish";

  const EDIT = "edit";

  const PUBLISH = "publish";

  const DELETE = "delete";

  const ABORT = "abortAction";

  function __construct($params=null) {
    $this->ci =& get_instance();
    $this->ci->load->database();
    $this->table_name = self::TABLE_PREFIX . (isset($params["name"]) ? "_" . $params["name"] : "");
    $this->ci->load->database();
    $this->ci->load->splint(self::PACKAGE, "*BlogManager", "bmanager");
    $this->ci->load->splint(self::MARKDOWN_PACKAGE, "+Parsedown", null, "parsedown");
    $this->ci->bmanager->setBlogName(isset($params["name"]) ? $params["name"] : null);
    $this->ci->load->helper("url");
  }
  /**
   * [install description]
   * @param  [type] $adminTableName          [description]
   * @param  [type] $adminTableName          [description]
   * @param  [type] $adminIdColumnName       [description]
   * @param  [type] $adminIdColumnConstraint [description]
   * @return [type]                          [description]
   */
  function install($blogName=null, $adminTableName = null, $adminIdColumnName = null, $adminIdColumnConstraint = null) {
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
      ),
      "slug"      => array(
        "type"       => "VARCHAR",
        "constraint" => 80,
        "unique"     => true
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
    $this->ci->bmanager->setBlogName($name != "" ? $name : null);
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
  private function loadScripts($w3css) {
    $this->ci->load->splint(self::PACKAGE, "-header_scripts", array(
      "w3css" => $w3css
    ));
  }
  /**
   * [w3css description]
   * @return [type] [description]
   */
  function w3css() {
    return "<link rel=\"stylesheet\" href=\"https://www.w3schools.com/w3css/4/w3.css\">";
  }
  /**
   * [fontsAwesome description]
   * @return [type] [description]
   */
  function fontsAwesome() {
    return "<link rel=\"stylesheet\" href=\"https://use.fontawesome.com/releases/v5.3.1/css/all.css\"";
  }
  /**
   * [loadEditor description]
   * @param  [type]  $callback [description]
   * @param  [type]  $postId   [description]
   * @param  boolean $w3css    [description]
   * @return [type]            [description]
   */
  function loadEditor($callback, $postId=null, $w3css=true) {
    $this->loadScripts($w3css);
    $this->ci->load->helper("form");
    $data = array(
      "callback" => "Admin/token",
      "type"     => $postId == null ? "create" : "edit",
      "callback" => $callback
    );
    if ($postId != null) {
      $data["id"] = $postId;
      $post = $this->getPost($postId, false);
      $data["title"] = $post["title"];
      $data["content"] = $post["content"];
    }
    $this->ci->load->splint("francis94c/blog", "-post_edit", $data);
  }
  /**
   * [savePost description]
   * @param  [type] $posterId [description]
   * @return [type]           [description]
   */
  function savePost($posterId=null) {
    $action = $this->ci->security->xss_clean($this->ci->input->post("action"));
    if ($action == "save") {
      $id = $this->ci->security->xss_clean($this->ci->input->post("id"));
      if ($id != "") {
        $this->ci->bmanager->savePost($this->ci->input->post("id"), $this->ci->security->xss_clean($this->ci->input->post("title")), $this->ci->security->xss_clean($this->ci->input->post("editor")), $posterId);
        return self::EDIT;
      } else {
        $this->ci->bmanager->createPost($this->ci->security->xss_clean($this->ci->input->post("title")), $this->ci->security->xss_clean($this->ci->input->post("editor")), $posterId);
        return self::CREATE;
      }
    } elseif ($action == "publish" || $action == "createAndPublish") {
      if ($action == "publish") {
        $id = $this->ci->security->xss_clean($this->ci->input->post("id"));
        if ($id == "") return self::ABORT;
        $this->ci->bmanager->savePost($id, $this->ci->security->xss_clean($this->ci->input->post("title")), $this->ci->security->xss_clean($this->ci->input->post("editor")), $posterId);
        $this->ci->bmanager->publishPost($id, $posterId);
        return self::PUBLISH;
      } else {
        $this->ci->bmanager->createAndPublishPost($this->ci->security->xss_clean($this->ci->input->post("title")), $this->ci->security->xss_clean($this->ci->input->post("editor")), $posterId);
        return self::CREATE_AND_PUBLISH;
      }
    } elseif ($action == "delete") {
      if ($this->ci->bmanager->deletePost($this->ci->security->xss_clean($this->ci->input->post("id")))) return self::DELETE;
    }
    return false;
  }
  /**
   * [getPosts get posts from the database by the given $page starting from the
   * value of 1 and returns $limit number of rows.]
   * @param  int     $page   Page number starting from 1.
   * @param  int     $limit  Number of posts to return.
   * @param  boolean $filter if true, returns only published posts, if false
   *                         return all posts. false by default.
   * @return array Array of posts for a given page.
   */
  function getPosts($page, $limit, $filter=false, $hits=false) {
    return $this->ci->bmanager->getPosts($page, $limit, $filter, $hits);
  }
  /**
   * [renderPosts description]
   * @param  [type]  $view       [description]
   * @param  [type]  $empty_view [description]
   * @param  [type]  $page       [description]
   * @param  [type]  $limit      [description]
   * @param  boolean $filter     [description]
   * @param  boolean $hits       [description]
   * @return [type]              [description]
   */
  function renderPostItems($view=null, $callback=null, $empty_view=null, $page=1, $limit=5, $filter=false, $hits=false, $slug=true) {
    if ($view == null || $empty_view == null) $this->ci->load->bind("francis94c/blog", $blogger);
    $posts = $this->getPosts($page, $limit, $filter, $hits);
    if (count($posts) == 0) {
      if ($empty_view == null) { $blogger->load->view("empty"); } else {
        $this->ci->load->view($empty_view);
        return;
      }
    }
    $this->ci->load->helper("text");
    foreach ($posts as $post) {
      $post["callback"] = $callback != null ? trim($callback, "/") . "/" . ($slug ? $post["slug"] : $post["id"]) : "";
      $post["filter"] = $filter;
      $post["content"] = $this->ci->parsedown->text(ellipsize($post["content"], 300));
      if ($view == null) {$blogger->load->view("post_list_item", $post); } else {
        $this->ci->load->view($view, $post);
      }
    }
  }
  /**
   * [getRecentPosts description]
   * @param  integer $limit  [description]
   * @param  boolean $filter [description]
   * @return [type]          [description]
   */
  function getRecentPosts($limit=5, $filter=false) {
    return $this->ci->bmanager->getRecentPosts($limit, $filter);
  }
  /**
   * [renderPost description]
   * @param  [type] $post [description]
   * @param  [type] $view [description]
   * @return [type]       [description]
   */
  function renderPost($post, $view=null) {
    if (!is_array($post)) $post = $this->ci->bmanager->getPost($post);
    $post["content"] = $this->ci->parsedown->text($post["content"]);
    if ($view == null) {
      $this->ci->load->splint("francis94c/blog", "-post_item", $post);
    } else {
      $this->ci->load->view($view, $post);
    }
  }
  /**
   * [metaOg description]
   * @param  [type] $post [description]
   * @return [type]       [description]
   */
  function metaOg($post) {
    $data = array();
    $data["title"] = $post["title"];
    $data["description"] = substr($post["content"], 0, 154);
    if (isset($post["share_image"])) $data["image_link"] = $post["share_image"];
    $data["url"] = current_url();
    return $this->ci->load->splint(self::PACKAGE, "-meta_og", $data, true);
  }
  /**
   * [getPostsCount description]
   * @return [type] [description]
   */
  function getPostsCount() {
    return $this->ci->bmanager->getPostsCount();
  }
  /**
   * [getPost description]
   * @param  [type] $postId [description]
   * @return [type]         [description]
   */
  function getPost($postId, $hit=true) {
    return $this->ci->bmanager->getPost($postId, $hit);
  }
  /**
   * [getHits description]
   * @param  [type] $postId [description]
   * @return [type]         [description]
   */
  function getHits($postId) {
    return $this->ci->bmanager->getHits($postId);
  }
  /**
   * [publishPost description]
   * @param  [type] $postId  [description]
   * @param  [type] $publish [description]
   * @return [type]          [description]
   */
  function publishPost($postId, $publish) {
    return $this->ci->bmanager->publishPost($postId, $publish);
  }
  /**
   * [deletePost description]
   * @param  [type] $postId [description]
   * @return [type]         [description]
   */
  function deletePost($postId) {
    return $this->ci->bmanager->deletePost($postId);
  }
  /**
   * [searchPosts description]
   * @param  [type]  $words  [description]
   * @param  [type]  $page   [description]
   * @param  integer $limit  [description]
   * @param  boolean $filter [description]
   * @return [type]          [description]
   */
  function searchPosts($words, $page, $limit=0, $filter=false) {
    return $this->ci->bmanager->searchPosts($words, $page, $limit, $filter);
  }
}
?>
