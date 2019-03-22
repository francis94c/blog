<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BlogManager extends CI_Model {

  /**
   * [createPost description]
   * @param  [type] $title   [description]
   * @param  [type] $content [description]
   * @param  [type] $adminId [description]
   * @return [type]          [description]
   */
  function createPost($title, $content, $adminId=null) {
    $data = array(
      "title"   => $title,
      "content" => $content
    );
    if ($adminId != null) $data["poster_id"] = $adminId;
    return $this->db->insert("blogger_posts", $data);
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
}
?>
