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
   * [getPosts description]
   * @param  [type] $page  [description]
   * @param  [type] $limit [description]
   * @return [type]        [description]
   */
  function getPosts($page, $limit) {
    if ($limit != 0) $this->db->limit($limit, ($page * $limit) - $limit);
    return $this->db->get("blogger_posts")->result_array();
  }
  /**
   * [savePost description]
   * @param  [type] $postId  [description]
   * @param  [type] $title   [description]
   * @param  [type] $content [description]
   * @return [type]          [description]
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
   * [getPost description]
   * @param  [type] $postId [description]
   * @return [type]         [description]
   */
  function getPost($postId) {
    $this->db->where("id", $postId);
    $query = $this->db->get("blogger_posts");
    if ($query->num_rows() > 0) return $query->result_array()[0];
    return false;
  }
}
?>
