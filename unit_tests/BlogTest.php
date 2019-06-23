<?php
use PHPUnit\Framework\TestCase;

final class BlogTest extends TestCase {

  public function testLoadBlog() {
    $ci =& get_instance();
    $ci->load->splint("francis94c/blog", "+Blogger", null, "blogger");
    $ci->load->database();
    $this->assertTrue($ci->blogger->install("test_blog"));
  }
}
?>
