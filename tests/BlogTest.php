<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BlogTest {

  function uiTest(&$ci) {
    $ci->load->splint("francis94c/blog", "+Blogger", null, "blogger");
    $ci->unit->run($ci->blogger->install("admins", "id", 1), true, "Install Blog Database.");
    $ci->unit->run($ci->blogger->install("release_notes", "admins", "id", 1), true, "Install Relese Notes Blog Database.");
    $ci->unit->run($ci->blogger->install("", "admins", "id", 1), true, "Install Blog Database.");
    $ci->unit->run($ci->blogger->install("test_name", "admins", "id", 1), true, "Install Test Blog Database.");
    $ci->blogger->loadEditor("callback");
    $oldVal = $_POST["action"];
    $_POST["action"] = "save";
    unset($_POST["id"]);
    $ci->blogger->renderPostItems(null, null, null, 1, 0);
    $ci->blogger->renderPostItems(null, null, "welcome_message", 1, 0);
    $ci->blogger->savePost(1);
    $ci->blogger->savePost(1);
    $ci->blogger->renderPostItems(null, null, null, 1, 0);
    $ci->blogger->renderPostItems("welcome_message", "welcome_message", null, 1, 0);
    $_POST["action"] = $oldVal;
    $this->cleanUp($ci);
  }
  function libTest(&$ci) {
    $ci->load->splint("francis94c/blog", "+Blogger", null, "blogger");
    $ci->blogger->setName("test_name");
    $ci->unit->run($ci->blogger->getName(), Blogger::TABLE_PREFIX . "_test_name", "Test Blogger setName()");
    $this->cleanUp($ci);
  }
  function createPostTest(&$ci) {
    $ci->load->splint("francis94c/blog", "+Blogger", null, "blogger");
    $ci->unit->run($ci->blogger->savePost(1), Blogger::CREATE, "Create Post Test.");
    $_POST["action"] = "createAndPublish";
    $ci->unit->run($ci->blogger->savePost(1), Blogger::CREATE_AND_PUBLISH, "Create and Publish Post Test.");
    $_POST["action"] = "save";
    $_POST["id"] = 1;
    $ci->unit->run($ci->blogger->savePost(1), Blogger::EDIT, "Edit Post.");
    $_POST["action"] = "publish";
    $ci->unit->run($ci->blogger->savePost(1), Blogger::PUBLISH, "Publish Post.");
    $this->cleanUp($ci);
  }
  private function cleanUp(&$ci) {
    $ci->db->empty_table("blogger_posts");
    $ci->db->empty_table("blogger_posts_test_name");
    $ci->db->empty_table("blogger_posts_release_notes");
  }
}
?>
