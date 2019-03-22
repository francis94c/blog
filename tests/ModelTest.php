<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ModelTest {

  const PACKAGE = "francis94c/blog";

  function testBlogManager(&$ci) {
    $ci->load->splint(self::PACKAGE, "+Blogger", null, "blogger");
    $ci->unit->run($ci->blogger->install("admins", "id", 1), true, "Install Blog Database.");
    $ci->load->splint(self::PACKAGE, "*BlogManager", "bmanager");
    $postId = $ci->bmanager->createPost("Hello", "World", 1);
    $ci->unit->run($postId !== false, true, "Create Post");
    $ci->unit->run(is_numeric($ci->bmanager->getPosts(0, 0)[0]["id"]), true, "ID Check");
    $ci->unit->run($ci->bmanager->getPost($postId)["title"], "Hello", "Title Check");
    $ci->unit->run((int) $ci->bmanager->getHits($postId), 1, "Hit count test.");
    $ci->unit->run($ci->bmanager->getPost($postId)["content"], "World", "Content Check");
    $ci->unit->run((int) $ci->bmanager->getHits($postId), 2, "Hit count test.");
    $ci->unit->run($ci->bmanager->savePost($postId, "Hello Title", "Hello Content"), true, "Save Post.");
    $ci->unit->run($ci->bmanager->getPost($postId)["title"], "Hello Title", "Title Check");
    $ci->unit->run((int) $ci->bmanager->getHits($postId), 3, "Hit count test.");
    $ci->unit->run($ci->bmanager->getPost($postId)["content"], "Hello Content", "Content Check");
    $ci->unit->run((int) $ci->bmanager->getHits($postId), 4, "Hit count test.");
    $ci->unit->run($ci->bmanager->publishPost($postId, true), true, "Publish Post.");
    $ci->unit->run((int) $ci->bmanager->getPost($postId)["published"], 1, "Publish Check");
    $ci->unit->run($ci->bmanager->publishPost($postId, false), true, "Unpublish Post");
      $ci->unit->run((int) $ci->bmanager->getPost($postId)["published"], 0, "Publish Check");
    $this->cleanUp($ci);
  }
  private function cleanUp(&$ci) {
    $ci->db->empty_table("blogger_posts");
  }
}
