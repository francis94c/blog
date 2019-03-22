<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ModelTest {

  const PACKAGE = "francis94c/blog";

  function testCreatePost(&$ci) {
    $ci->load->splint(self::PACKAGE, "+Blogger", null, "blogger");
    $ci->unit->run($ci->blogger->install("admins", "id", 1), true, "Install Blog Database.");
    $ci->load->splint(self::PACKAGE, "*BlogManager", "bmanager");
    $postId = $ci->bmanager->createPost("Hello", "World", 1);
    $ci->unit->run($postId !== false, true, "Create Post");
    $ci->unit->run(is_numeric($ci->bmanager->getPosts(0, 0)[0]["id"]), true, "ID Check");
    $ci->unit->run($ci->bmanager->getPost($postId)["title"], "Hello", "Title Check");
    $ci->unit->run($ci->bmanager->getPost($postId)["content"], "World", "Content Check");
    $ci->unit->run($ci->bmanager->savePost($postId, "Hello Title", "Hello Content"), true, "Save Post.");
    $ci->unit->run($ci->bmanager->getPost($postId)["title"], "Hello Title", "Title Check");
    $ci->unit->run($ci->bmanager->getPost($postId)["content"], "Hello Content", "Content Check");
    $this->cleanUp($ci);
  }
  private function cleanUp(&$ci) {
    $ci->db->empty_table("blogger_posts");
  }
}
