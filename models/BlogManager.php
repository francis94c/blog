<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BlogManager extends CI_Model {

  const TABLE_PREFIX = "blogger_posts";

  private $ci;

  private $table_name;

  function __construct() {
    parent::__construct();
    $this->ci =& get_instance();
    $this->ci->load->database();
  }
  /**
   * [setBlogName description]
   * @param [type] $name [description]
   */
  function setBlogName($name) {
    $this->table_name = self::TABLE_PREFIX . ($name != null ? "_" . $name : "");
  }
  /**
   * [getBlogName description]
   * @return [type] [description]
   */
  function getBlogName() {
    return $this->table_name;
  }
  /**
   * [createPost creates a post with the given $title and $content in the
   * database.]
   * @param  string $title   The title of the post.
   * @param  string $content The content of the post.
   * @param  int    $adminId Optional, the id of the poster. this is needed if
   *                         you provided an admins table name during blog
   *                         installation.
   * @return boolean         Returns the id of the newly created post in the
   *                         database. Returns false if the post couldn't be
   *                         created.
   */
  function createPost($title, $content, $adminId=null) {
    $data = array(
      "title"   => $title,
      "content" => $content,
      "slug"    => url_title($title)
    );
    if (is_numeric($title)) $data["slug"] = "_" . $data["slug"];
    if ($adminId != null) $data["poster_id"] = $adminId;
    if ($this->db->insert($this->table_name, $data)) return $this->db->insert_id();
    return false;
  }
  /**
   * [createAndPublish description]
   * @param  [type] $title   [description]
   * @param  [type] $content [description]
   * @param  [type] $adminId [description]
   * @return [type]          [description]
   */
  function createAndPublishPost($title, $content, $adminId=null) {
    $data = array(
      "title"          => $title,
      "content"        => $content,
      "slug"           => url_title($title),
      "published"      => 1,
      "date_published" => date("Y-m-d H:i:s")
    );
    if (is_numeric($title)) $data["slug"] = "_" . $data["slug"];
    if ($adminId != null) $data["poster_id"] = $adminId;
    if ($this->db->insert($this->table_name, $data)) return $this->db->insert_id();
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
  function getPosts($page=1, $limit=5, $filter=false, $hits=false) {
    if ($limit != 0) $this->db->limit($limit, ($page * $limit) - $limit);
    if ($filter) $this->db->where("published", 1);
    if ($hits) {
      $this->db->order_by("hits", "DESC");
    } else {
      $this->db->order_by("id", "DESC");
    }
    return $this->db->get($this->table_name)->result_array();
  }
  /**
   * [savePost saves or midfies the content of a post record given by $postId
   * in the database.]
   * @param  int    $postId  ID of the post to modify.
   * @param  string $title   New title of the post.
   * @param  string $content New content of the post.
   * @return bool            True on success, False if not.
   */
  function savePost($postId, $title, $content) {
    $data = array (
      "title"   => $title,
      "content" => $content
    );
    $this->db->where("id", $postId);
    return $this->db->update($this->table_name, $data);
  }
  /**
   * [getPost Gets a specific post by the given value of $postId. NB: This will
   * increment the hit count of the particularpost in the database.]
   * @param  int   $postId ID of the post to retrieve.
   * @return array An associative array for the Posts's data.
   */
  function getPost($postId, $hit=true) {
    if (is_numeric($postId)) {
      $this->db->where("id", $postId);
    } else {
      $this->db->where("slug", $postId);
    }
    $query = $this->db->get($this->table_name);
    if ($query->num_rows() > 0) {
      if ($hit) {
        if ($this->ci->config->item("blogger_hits") === null ||
        $this->ci->config->item("blogger_hits") === true) {
          $this->db->where("id", $postId);
          $this->db->set("hits", "hits+1", FALSE);
          $this->db->update($this->table_name);
        }
      }
      $post =  $query->result_array()[0];
      $images = array();
      // Fetch all images in post.
      preg_match("/<img\s[^>]*?src\s*=\s*['\"]([^'\"]*?)['\"][^>]*?>/", $post["content"], $images);
      $share_image = count($images) == 0 ? null : $images[0];
      unset($images);
      $src = array();
      // Get the contents of the src tag.
      preg_match("/(http|https):\/\/[a-zA-Z0-9-._\/]+/", $share_image, $src);
      if (count($src) == 0) return $post;
      $post["share_image"] = $src[0];
      return $post;
    }
    return false;
  }
  /**
   * [getHits description]
   * @param  [type] $postId [description]
   * @return [type]         [description]
   */
  function getHits($postId) {
    $this->db->where("id", $postId);
    $query = $this->db->get($this->table_name);
    if ($query->num_rows() > 0) return $query->result()[0]->hits;
    return 0;
  }
  /**
   * [publishPost description]
   * @param  [type] $postId  [description]
   * @param  [type] $publish [description]
   * @return [type]          [description]
   */
  function publishPost($postId, $publish) {
    $this->db->where("id", $postId);
    $this->db->set("published", $publish ? 1 : 0);
    $this->db->set("date_published", date("Y-m-d H:i:s"));
    return $this->db->update($this->table_name);
  }
  /**
   * [getPostsCount get the total number of posts in the database.]
   * @return [type] [description]
   */
  function getPostsCount() {
    $this->db->select("COUNT(title) as posts");
    return $this->db->get($this->table_name)->result()[0]->posts;
  }
  /**
   * [deletePost description]
   * @param  [type] $postId [description]
   * @return [type]         [description]
   */
  function deletePost($postId) {
    $this->db->where("id", $postId);
    return $this->db->delete($this->table_name);
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
    if ($limit != 0) $this->db->limit($limit, ($page * $limit) - $limit);
    if ($filter) $this->db->where("published", 1);
    $this->db->like("title", $words);
    $this->db->or_like("content", $words);
    return $this->db->get($this->table_name)->result_array();
  }
}
?>
