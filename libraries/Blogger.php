<?php
declare(strict_types=1);
defined('BASEPATH') OR exit('No direct script access allowed');

class Blogger {

  /**
   * Code Igniter Instance
   * @var object
   */
  private $ci;
  /**
   * Code Igniter DB Forge instance reference for simplicity.
   * @var object
   */
  private $dbforge;
  /**
   * Current Blog Table Name.
   * @var string
   */
  private $table_name;
  /**
   * String prefixed with ever blog name.
   * @var string
   */
  const TABLE_PREFIX = "blogger_posts";
  /**
   * Name of this package for simplicity.
   * @var string
   */
  const PACKAGE = "francis94c/blog";
  /**
   * Name of the dependent package for markdown.
   * @var string
   */
  const MARKDOWN_PACKAGE = "francis94c/ci-parsedown";
  /**
   * Blog post create action.
   * @var string
   */
  const CREATE = "create";
  /**
   * Blog post create and publish action.
   * @var string
   */
  const CREATE_AND_PUBLISH = "createAndPublish";
  /**
   * Blog post edit action.
   * @var string
   */
  const EDIT = "edit";
  /**
   * Blog post publish action.
   * @var string
   */
  const PUBLISH = "publish";
  /**
   * Blog post delete action.
   * @var string
   */
  const DELETE = "delete";
  /**
   * Blog post abort acction. This is an action taken internally when other
   * actions fail.
   * @var string
   */
  const ABORT = "abortAction";
  /**
   * Blog post no action.
   * @var string
   */
  const NO_ACTION = "no_action";
  /**
   * Constructor
   * @param mixed $params associative array of parameters. See README.md
   */
  function __construct($params=null) {
    $this->ci =& /** @scrutinizer ignore-call */ get_instance();
    $this->ci->load->database();
    $this->table_name = self::TABLE_PREFIX . (isset($params["name"]) ? "_" . $params["name"] : "");
    $this->ci->load->database();
    $this->ci->load->splint(self::PACKAGE, "*BlogManager", "bmanager");
    $this->ci->load->splint(self::MARKDOWN_PACKAGE, "+Parsedown", null, "parsedown");
    $this->ci->bmanager->setBlogName(isset($params["name"]) ? $params["name"] : null);
    $this->ci->load->helper("url");
  }
  /**
   * Installs a blog with the given table name and paramters.
   *
   * @param  string $blogName                Name of blog tabke to install.
   *
   * @param  string $adminTableName          Name of admi table to restrict post to.
   *
   * @param  string $adminIdColumnName       Name of the column to add a foreign
   *                                         key constarint to the blog table with.
   *
   * @param  int    $adminIdColumnConstraint The column constarint or limit of
   *                                         $adminIdColumnName.
   *
   * @return bool                            True on Success, False if Not.
   */
  public function install(string $blogName=null, string $adminTableName=null, string $adminIdColumnName=null, int $adminIdColumnConstraint=null): bool {
    $blogName = $blogName === null ? $this->table_name : self::TABLE_PREFIX . "_" . $blogName;
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
        "type" => "TINYINT",
        "default" => 0
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
    $this->ci->dbforge->add_field($fields);
    $constrain = $adminTableName !== null && $adminIdColumnName !== null &&
    $adminIdColumnConstraint !== null;
    if ($constrain) {
      $this->ci->dbforge->add_field(
        "poster_id INT($adminIdColumnConstraint), FOREIGN KEY (poster_id) REFERENCES $adminTableName($adminIdColumnName)");
    }
    $this->ci->dbforge->add_field("date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    $attributes = array('ENGINE' => 'InnoDB');
    if (!$this->ci->dbforge->create_table($blogName, true, $attributes)) return false;
    return true;
  }
  /**
   * Sets the name of the current blog table.
   *
   * @param string $name name of a blog table.
   *
   * @deprecated
   */
  public function setName(string $name): void {
    $this->table_name = self::TABLE_PREFIX . "_" . $name;
    $this->ci->bmanager->setBlogName($name != "" ? $name : null);
  }
  /**
   * Same as the deprecated setName. Sets the name of the current blog table.
   *
   * @param string $blog [description]
   */
  public function setBlog(string $blog): void {
    $this->table_name = self::TABLE_PREFIX . "_" . $blog;
    $this->ci->bmanager->setBlogName($blog != "" ? $blog : null);
  }
  /**
   * Gets the name of the blog.
   * @return string The name of the blog.
   */
  public function getName(): string {
    return $this->table_name;
  }
  /**
   * Loads/echoes the client side scripts needed for the blog to render it's
   * post editor and other views.
   *
   * @param  bool $w3css If true, additionally loads the W3.CSS file for additional
   *                     styling. Defaults internally on Blogmanager to true
   */
  private function loadScripts(bool $w3css): void {
    $this->ci->load->splint(self::PACKAGE, "-header_scripts", array(
      "w3css" => $w3css
    ));
  }
  /**
   * Returns the W3.CSS client side script loading tag.
   * @return string W3.CSS link tag.
   */
  public function w3css(): string {
    return "<link rel=\"stylesheet\" href=\"https://www.w3schools.com/w3css/4/w3.css\">";
  }
  /**
   * Returns the Fonts Awesome CSS link loading tag.
   * @return string Fonts Awesome CSS link loading tag.
   */
  public function fontsAwesome(): string {
    return "<link rel=\"stylesheet\" href=\"https://use.fontawesome.com/releases/v5.3.1/css/all.css\"";
  }
  /**
   * Echoes to the browser a 'SimpleMDE' markdown editor for editing post
   * contents, as part of a form.
   *
   * @param  string  $callback The URI callback that will be passed to the Code
   *                           Igniter form_open method when outputing the form.
   *                           The call back is where you should read the contents
   *                           of the submited form.
   *                           The contents of the form should be read or handled
   *                           by a call to the 'savePost($posterId)' function.
   *                           You don't need to worry about reading it your self.
   *
   * @param  int     $postId   (Optional) The ID of the post whose content should
   *                           be present in the editor when loaded. provide this
   *                           parameter when you want to edit a post.
   *
   * @param  bool    $w3css    If true, echoes the W3.CSS link tag as well.
   *
   * @return bool              True  if sucessfull without errors, false if not.
   */
  public function loadEditor(string $callback, int $postId=null, bool $w3css=true): bool {
    $this->loadScripts($w3css);
    $this->ci->load->helper("form");
    $data = array(
      "callback" => "Admin/token",
      "type"     => $postId === null ? "create" : "edit",
      "callback" => $callback
    );
    if ($postId !== null) {
      $data["id"] = $postId;
      $post = $this->getPost($postId, false);
      $data["title"] = $post["title"];
      $data["content"] = $post["content"];
    }
    $this->ci->load->splint("francis94c/blog", "-post_edit", $data);
    return true;
  }
  /**
   * Handles form data from the Editor loaded by a call to 'loadEditor'.
   * Traditionally, this function is to be called ath the controller function
   * specified by the callback URI provided to the loadEditor method.
   *
   * @param  int $posterId ID of the poster. A valid admin ID from the table
   *                       specified as a foreign key constraint during the
   *                       installation of the selected blog.
   *
   * @return string        The final action reached in processing the form
   *                       inputs. These are public string constants declared in
   *                       this file.
   */
  public function savePost(int $posterId=null): string {
    $action = $this->ci->security->xss_clean($this->ci->input->post("action"));
    switch ($action) {
      case "save":
        return $this->handleSavePost($posterId);
      case "publish":
        return $this->handlePublishPost($posterId);
      case "createAndPublish":
        if ($this->ci->bmanager->createAndPublishPost($this->ci->security->xss_clean($this->ci->input->post("title")), $this->ci->security->xss_clean($this->ci->input->post("editor")), $posterId) !== false) return self::CREATE_AND_PUBLISH;
        return self::ABORT;
      case "delete":
        if ($this->ci->bmanager->deletePost($this->ci->security->xss_clean($this->ci->input->post("id")))) return self::DELETE;
        return self::ABORT;
      default:
        return self::NO_ACTION;
    }
  }
  /**
   * [handleSavePost handles save pot actions; edit & create]
   *
   * @param  int     poster ID or Admin ID.
   *
   * @return string  Action taken during the pocess; Blogger::CREATE Or Blogger::EDIT
   */
  private function handleSavePost(int $posterId=null): string {
    $id = $this->ci->security->xss_clean($this->ci->input->post("id"));
    if ($id != "") {
      if (!$this->ci->bmanager->savePost($this->ci->input->post("id"), $this->ci->security->xss_clean($this->ci->input->post("title")), $this->ci->security->xss_clean($this->ci->input->post("editor")), $posterId)) return self::ABORT;
      return self::EDIT;
    } else {
      if ($this->ci->bmanager->createPost($this->ci->security->xss_clean($this->ci->input->post("title")), $this->ci->security->xss_clean($this->ci->input->post("editor")), $posterId) == 0) return self::ABORT;
      return self::CREATE;
    }
  }
  /**
   * [handlePublishPost handles the publishing of a post using inputs from the
   *  submited form.]
   *
   * @param  int    $posterId ID of the publishing Admin.

   * @return string           Action reached while processing form inputs.
   */
  private function handlePublishPost(int $posterId=null): string {
    $id = $this->ci->security->xss_clean($this->ci->input->post("id"));
    if ($id == "") return self::ABORT;
    if (!$this->ci->bmanager->savePost($id, $this->ci->security->xss_clean($this->ci->input->post("title")), $this->ci->security->xss_clean($this->ci->input->post("editor")), $posterId)) return self::ABORT;
    if (!$this->ci->bmanager->publishPost($id, true)) return self::ABORT;
    return self::PUBLISH;
  }
  /**
   * getPosts get posts from the database by the given $page starting from the
   * value of 1 and returns $limit number of rows.
   *
   * @param  int     $page   Page number starting from 1.
   *
   * @param  int     $limit  Number of posts to return.
   *
   * @param  bool    $filter if true, returns only published posts, if false
   *                         return all posts. false by default.
   *
   * @param  bool    $hits   If truem orders the returned posts by number of hits.
   *
   * @return array           Array of posts for a given page.
   */
  public function getPosts(int $page, int $limit, bool $filter=false, bool $hits=false): array {
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
  public function renderPostItems($view=null, $callback=null, $empty_view=null, $page=1, $limit=5, $filter=false, $hits=false, $slug=true) {
    if ($view == null || $empty_view == null) $this->ci->load->bind("francis94c/blog", $blogger);
    $posts = $this->getPosts($page, $limit, $filter, $hits);
    if (count($posts) == 0) {
      if ($empty_view == null) { $blogger->load->view("empty"); } else {
        $this->ci->load->view($empty_view);
        return true;
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
    return true;
  }
  /**
   * [getRecentPosts description]
   * @param  integer $limit  [description]
   * @param  boolean $filter [description]
   * @return [type]          [description]
   */
  public function getRecentPosts($limit=5, $filter=false) {
    return $this->ci->bmanager->getRecentPosts($limit, $filter);
  }
  /**
   * [renderPost description]
   * @param  [type] $post [description]
   * @param  [type] $view [description]
   * @return [type]       [description]
   */
  public function renderPost($post, $view=null) {
    if (!is_array($post)) $post = $this->ci->bmanager->getPost($post);
    if (!$post) return false;
    $post["content"] = $this->ci->parsedown->text($post["content"]);
    if ($view == null) {
      $this->ci->load->splint("francis94c/blog", "-post_item", $post);
    } else {
      $this->ci->load->view($view, $post);
    }
    return true;
  }
  /**
   * [metaOg description]
   * @param  [type] $post [description]
   * @return [type]       [description]
   */
  public function metaOg($post) {
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
  public function getPostsCount() {
    return $this->ci->bmanager->getPostsCount();
  }
  /**
   * [getPost description]
   * @param  [type] $postId [description]
   * @return [type]         [description]
   */
  public function getPost($postId, $hit=true) {
    return $this->ci->bmanager->getPost($postId, $hit);
  }
  /**
   * [getHits description]
   * @param  [type] $postId [description]
   * @return [type]         [description]
   */
  public function getHits($postId) {
    return $this->ci->bmanager->getHits($postId);
  }
  /**
   * [publishPost description]
   * @param  [type] $postId  [description]
   * @param  [type] $publish [description]
   * @return [type]          [description]
   */
  public function publishPost($postId, $publish) {
    return $this->ci->bmanager->publishPost($postId, $publish);
  }
  /**
   * [deletePost description]
   * @param  [type] $postId [description]
   * @return [type]         [description]
   */
  public function deletePost($postId) {
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
  public function searchPosts($words, $page, $limit=0, $filter=false) {
    return $this->ci->bmanager->searchPosts($words, $page, $limit, $filter);
  }
}
