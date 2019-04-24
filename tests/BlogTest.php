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
    $ci->blogger->renderPost("Hello", null);
    $ci->blogger->renderPost("Hello", null);
    $ci->blogger->renderPost("Hello", "welcome_message");
    echo $ci->blogger->metaOg($ci->blogger->getPost("Hello", false));
    $_POST["title"] .= "1E";
    $ci->blogger->savePost(1);
    $_POST["title"] .= "1ETY";
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
    $_POST["title"] .= "1E";
    $ci->unit->run($ci->blogger->savePost(1), Blogger::CREATE_AND_PUBLISH, "Create and Publish Post Test.");
    $_POST["action"] = "save";
    $_POST["id"] = 1;
    $_POST["title"] .= "1E";
    $ci->unit->run($ci->blogger->savePost(1), Blogger::EDIT, "Edit Post.");
    $_POST["action"] = "publish";
    $_POST["title"] .= "1E";
    $ci->unit->run($ci->blogger->savePost(1), Blogger::PUBLISH, "Publish Post.");
    $_POST["title"] = "Egress Traffic";
    unset($_POST["id"]);
    $_POST["content"] = "GAE";
    $_POST["action"] = "save";
    $ci->unit->run($ci->blogger->savePost(1), Blogger::CREATE, "Create Post Test.");
    $ci->unit->run($ci->blogger->getPost("Egress-Traffic", false)["title"], "Egress Traffic", "Post to be Deleted Test");
    $_POST["id"] = $ci->blogger->getPost("Egress-Traffic", false)["id"];
    $_POST["action"] = "delete";
    $ci->unit->run($ci->blogger->savePost(1), Blogger::DELETE, "Blogger Delete Test");
    $ci->unit->run($ci->blogger->getPost("Egress-Traffic", false), false, "Blogger Delete Post Test Result");
    $_POST["title"] .= "1E";
    $this->cleanUp($ci);
  }
  private function cleanUp(&$ci) {
    $ci->db->empty_table("blogger_posts");
    $ci->db->empty_table("blogger_posts_test_name");
    $ci->db->empty_table("blogger_posts_release_notes");
  }
}
?>
