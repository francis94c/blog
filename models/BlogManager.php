<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BlogManager extends CI_Model {

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
      "content" => $content
    );
    if ($adminId != null) $data["poster_id"] = $adminId;
    if ($this->db->insert("blogger_posts", $data)) return $this->db->insert_id();
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
      "title"     => $title,
      "content"   => $content,
      "published" => 1
    );
    if ($adminId != null) $data["poster_id"] = $adminId;
    if ($this->db->insert("blogger_posts", $data)) return $this->db->insert_id();
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
  function getPosts($page, $limit, $filter=false) {
    if ($limit != 0) $this->db->limit($limit, ($page * $limit) - $limit);
    if ($filter) $this->db->where("published", 1);
    return $this->db->get("blogger_posts")->result_array();
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
    return $this->db->update("blogger_posts", $data);
  }
  /**
   * [getPost Gets a specific post by the given value of $postId. NB: This will
   * increment the hit count of the particularpost in the database.]
   * @param  int   $postId ID of the post to retrieve.
   * @return array An associative array for the Posts's data.
   */
  function getPost($postId) {
    $this->db->where("id", $postId);
    $query = $this->db->get("blogger_posts");
    if ($query->num_rows() > 0) {
      $this->db->where("id", $postId);
      $this->db->set("hits", "hits+1", FALSE);
      $this->db->update("blogger_posts");
      return $query->result_array()[0];
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
    $query = $this->db->get("blogger_posts");
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
    return $this->db->update("blogger_posts");
  }
  /**
   * [getPostsCount get the total number of posts in the database.]
   * @return [type] [description]
   */
  function getPostsCount() {
    $this->db->select("COUNT(title) as posts");
    return $this->db->get("blogger_posts")->result()[0]->posts;
  }
  /**
   * [deletePost description]
   * @param  [type] $postId [description]
   * @return [type]         [description]
   */
  function deletePost($postId) {
    $this->db->where("id", $postId);
    return $this->db->delete("blogger_posts");
  }
  function searchPosts($words) {
    $this->db->like("title", $words);
    $this->db->or_like("content", $words);
  }
}
?>
