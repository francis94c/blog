<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class BlogTest {

  function uiTest(&$ci) {
    $ci->load->splint("francis94c/blog", "+Blogger", null, "blogger");
    $ci->unit->run($ci->blogger->install("admins", "id", 1), true, "Install Blog Database.");
    $ci->blogger->loadScripts();
    $ci->blogger->loadCreateView("callback");
  }
}
?>
