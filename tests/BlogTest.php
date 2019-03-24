<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BlogTest {

  function uiTest(&$ci) {
    $ci->load->splint("francis94c/blog", "+Blogger", null, "blogger");
    $ci->unit->run($ci->blogger->install("admins", "id", 1), true, "Install Blog Database.");
    $ci->blogger->loadScripts();
    $ci->blogger->loadEditor("callback");
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
  }
}
?>
