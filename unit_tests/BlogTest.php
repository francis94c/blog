<?php
use PHPUnit\Framework\TestCase;

final class BlogTest extends TestCase {

  public function testLoadBlog(): void {
    $ci =& get_instance();
    $ci->load->splint("francis94c/blog", "+Blogger", null, "blogger");
    $ci->load->database();
    $ci->load->dbforge();
    $this->assertTrue($ci->dbforge->create_database('blog_db'));
    $db['hostname'] = 'localhost';
    $db['username'] = 'root';
    $db['password'] = '';
    $db['database'] = 'blog_db';
    $db['dbdriver'] = 'mysqli';
    $db['dbprefix'] = '';
    $db['pconnect'] = FALSE;
    $db['db_debug'] = TRUE;
    $db['cache_on'] = FALSE;
    $db['cachedir'] = '';
    $db['char_set'] = 'utf8';
    $db['dbcollat'] = 'utf8_general_ci';
    $ci->load->database($db);
    $this->assertTrue($ci->blogger->install("test_blog"));
  }
}
?>
