<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ModelTest {

  const PACKAGE = "francis94c/blog";

  function testBlogManager(&$ci) {
    $ci->load->splint(self::PACKAGE, "+Blogger", null, "blogger");
    $ci->unit->run($ci->blogger->install("admins", "id", 1), true, "Install Blog Database.");
    $ci->load->splint(self::PACKAGE, "*BlogManager", "bmanager");
    $postId = $ci->bmanager->createPost("Hello456", "World", 1);
    $ci->unit->run($postId !== false, true, "Create Post");
    $ci->unit->run((int) $ci->bmanager->getPostsCount(), 1, "Posts count check");
    $ci->unit->run(is_numeric($ci->bmanager->getPost($postId)["id"]), true, "ID Check");
    $ci->unit->run($ci->bmanager->getPost($postId)["title"], "Hello456", "Title Check");
    $ci->unit->run((int) $ci->bmanager->getPost("Hello456")["id"], $postId, "Slug Check");
    $ci->unit->run((int) $ci->bmanager->getHits($postId), 2, "Hit count test.");
    $ci->unit->run($ci->bmanager->getPost($postId)["content"], "World", "Content Check");
    $ci->unit->run((int) $ci->bmanager->getHits($postId), 3, "Hit count test.");
    $ci->unit->run($ci->bmanager->savePost($postId, "Hello Title5", "Hello Content"), true, "Save Post.");
    $ci->unit->run($ci->bmanager->getPost($postId)["title"], "Hello Title5", "Title Check");
    $ci->unit->run((int) $ci->bmanager->getHits($postId), 4, "Hit count test.");
    $ci->unit->run($ci->bmanager->getPost($postId)["content"], "Hello Content", "Content Check");
    $ci->unit->run((int) $ci->bmanager->getHits($postId), 5, "Hit count test.");
    $ci->config->set_item("blogger_hits", false);
    $ci->bmanager->getPost($postId);
    $ci->unit->run((int) $ci->bmanager->getHits($postId), 5, "Hit count test.");
    $ci->config->set_item("blogger_hits", true);
    $ci->bmanager->getPost($postId);
    $ci->unit->run((int) $ci->bmanager->getHits($postId), 6, "Hit count test.");
    $ci->unit->run(count($ci->bmanager->getPosts(1, 5, true)), 0, "Published count.");
    $ci->unit->run(count($ci->bmanager->getPosts(1, 0, true)), 0, "Published count.");
    $ci->unit->run($ci->bmanager->publishPost($postId, true), true, "Publish Post.");
    $ci->unit->run(count($ci->bmanager->getPosts(1, 5, true)), 1, "Published count.");
    $ci->unit->run(count($ci->bmanager->getPosts(1, 0, true)), 1, "Published count.");
    $ci->unit->run((int) $ci->bmanager->getPost($postId)["published"], 1, "Publish Check");
    $ci->unit->run($ci->bmanager->publishPost($postId, false), true, "Unpublish Post");
    $ci->unit->run((int) $ci->bmanager->getPost($postId)["published"], 0, "Publish Check");
    $postId = $ci->bmanager->createAndPublishPost("Hello234", "World Peter", 1);
    $ci->unit->run((int) $ci->bmanager->getPostsCount(), 2, "Posts count check");
    $ci->unit->run((int) $ci->bmanager->getPost($postId)["published"], 1, "Publish Check");
    $ci->unit->run(count($ci->bmanager->searchPosts("Hello", 1, 5)), 2, "Search Count");
    $ci->unit->run(count($ci->bmanager->searchPosts("Peter", 1, 5)), 1, "Search Count");
    $ci->unit->run(isset($ci->bmanager->getPost($postId)["share_image"]), false, "Share Image Test");
    $postId = $ci->bmanager->createAndPublishPost("Hello23567",
    "World Peter <img class=\"w3-image\" src=\"http://a/link/to/image/1.png\" alt=\"Title\"/> <img class=\"w3-image\" src=\"http://a/link/to/image/2.png\" alt=\"Title\"/> <img class=\"w3-image\" src=\"http://a/link/to/image/3.png\" alt=\"Title\"/>", 1);
    $ci->unit->run(isset($ci->bmanager->getPost($postId)["share_image"]), true, "Share Image Test");
    $post = $ci->bmanager->getPost($postId);
    $ci->unit->run($post["share_image"], "http://a/link/to/image/1.png", "Share Image Link Test");
    $ci->bmanager->createPost(1, "Hello World Over Here", 1);
    $ci->unit->run($ci->bmanager->getPost("_1")["content"], "Hello World Over Here", "Numeric Slug Escape Check");
    $this->cleanUp($ci);
  }
  private function cleanUp(&$ci) {
    $ci->db->empty_table("blogger_posts");
    $ci->db->empty_table("blogger_posts_test_name");
    $ci->db->empty_table("blogger_posts_release_notes");
  }
}
