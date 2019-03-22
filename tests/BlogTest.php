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
  }
}
?>
