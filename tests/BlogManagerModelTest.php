<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BlogManagerModelTest {

  const PACKAGE = "francis94c/blog";

  function testCreatePost(&$ci) {
    $ci->load->splint(self::PACKAGE, "+Blogger", null, "blogger");
    $ci->unit->run($ci->blogger->install("admins", "id", 1), true, "Install Blog Database.");
    $ci->load->splint(self::PACKAGE, "*BlogManager", "bmanager");
    $ci->unit->run($ci->bmanager->createPost("Hello", "World", 1) !== false, true, "Create Post");
    $ci->unit->run(is_numeric($ci->bmanager->getPosts(0, 0)[0]["id"]), true, "ID Check");
    $ci->unit->run($ci->bmanager->getPosts(0, 0)[0]["title"], "Hello", "Title Check");
    $ci->unit->run($ci->bmanager->getPosts(0, 0)[0]["content"], "World", "Content Check");
    $this->cleanUp($ci);
  }
  private function cleanUp(&$ci) {
    $ci->db->empty_table("blogger_posts");
  }
}
