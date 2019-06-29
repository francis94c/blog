<?php
use PHPUnit\Framework\TestCase;

final class BlogTest extends TestCase {

  private $ci;

  public function setUp(): void {
    $this->ci =& get_instance();
    $this->ci->load->database();
    $this->ci->db->query("USE test_db;");
    $queries = [
      "CREATE TABLE IF NOT EXISTS admins (id INT(7) AUTO_INCREMENT PRIMARY KEY, name VARCHAR(20) NOT NULL, password TEXT NOT NULL) Engine=InnoDB;",
      "INSERT INTO admins (id, name, password) VALUES (1, \"Dev\", \"does_not_matter_for_this_test\");"
    ];
    foreach ($queries as $query) {
      $this->assertTrue($this->ci->db->query($query), "$query, Ran sucessfully.");
    }
  }

  public function testLoadBlog() {
    $this->ci->load->splint("francis94c/blog", "+Blogger", null, "blogger");
    $this->assertTrue($this->ci->blogger->install("test_blog"), "Blog Installed Successfuly without admin ID constraint.");
    $this->assertTrue($this->ci->blogger->install("test_blog"), "Verify CREATE IF NOT EXISTS clause");
    $this->assertTrue($this->ci->blogger->install("admin_test_blog", "admins", "id", 7), "Create Blog with existent admin constarint");
    $this->assertTrue($this->ci->blogger->loadEditor("callback"), "Load Editor");
    $this->ci->blogger->setBlog("test_blog");
    $this->assertTrue($this->ci->blogger->renderPostItems(null, null, null, 1, 0), "Test load empty posts set");
  }

  public function tearDown(): void {
    $this->ci->db->empty_table("admins");
  }
}
