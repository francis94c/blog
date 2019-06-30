<?php
use PHPUnit\Framework\TestCase;

final class BlogTest extends TestCase {

  /**
   * [private description]
   * @var [type]
   */
  private static $ci;

  /**
   * [setUp description]
   */
  public static function setUpBeforeClass(): void {
    self::$ci =& get_instance();
    self::$ci->load->database('mysqli://root@localhost/test_db');
    $queries = [
      "CREATE TABLE IF NOT EXISTS admins (id INT(7) AUTO_INCREMENT PRIMARY KEY, name VARCHAR(20) NOT NULL, password TEXT NOT NULL) Engine=InnoDB;",
      "INSERT INTO admins (id, name, password) VALUES (1, \"Dev\", \"does_not_matter_for_this_test\");"
    ];
    foreach ($queries as $query) {
      self::assertTrue(self::$ci->db->query($query), "$query, Ran sucessfully.");
    }
    self::$ci->load->splint("francis94c/blog", "+Blogger", null, "blogger");
  }
  /**
   * [testInstallBlog description]
   * @return [type] [description]
   */
  public function testInstallBlog() {
    $this->assertTrue(self::$ci->blogger->install("test_blog"), "Blog Installed Successfuly without admin ID constraint.");
    $this->assertTrue(self::$ci->db->table_exists("blogger_posts_test_blog"));
    $fields = self::$ci->db->list_fields("blogger_posts_test_blog");
    $this->assertContains("id", $fields);
    $this->assertContains("title", $fields);
    $this->assertContains("content", $fields);
    $this->assertContains("slug", $fields);
    $this->assertContains("date_created", $fields);
    $this->assertContains("date_published", $fields);
    $this->assertTrue(self::$ci->blogger->install("test_blog"), "Verify CREATE IF NOT EXISTS clause");
    $this->assertTrue(self::$ci->blogger->install("admin_test_blog", "admins", "id", 7), "Create Blog with existent admin constarint");
    $fields = self::$ci->db->list_fields("blogger_posts_admin_test_blog");
    $this->assertContains("id", $fields);
    $this->assertContains("title", $fields);
    $this->assertContains("content", $fields);
    $this->assertContains("slug", $fields);
    $this->assertContains("date_created", $fields);
    $this->assertContains("date_published", $fields);
  }
  /**
   * [testUI description]
   * @return [type] [description]
   * @depends testInstallBlog
   */
  public function testUI() {
    $this->assertTrue(self::$ci->blogger->loadEditor("callback"), "Load Editor");
    self::$ci->blogger->setBlog("test_blog");
    $this->assertTrue(self::$ci->blogger->renderPostItems(null, null, null, 1, 0), "Test load empty posts set");
  }
  /**
   * [testBlogSave description]
   * @return [type] [description]
   * @depends testInstallBlog
   */
  public function testBlogSave() {
    // No Admin.
    self::$ci->blogger->setBlog("test_blog");
    $_POST["action"] = "save";
    $_POST["title"] = "Hello Title";
    $_POST["editor"] = "The Quick Brown Fox Jumped over the Lazy Dog.";
    $this->assertEquals(self::$ci->blogger->savePost(), Blogger::CREATE);
    $_POST["editor"] = "The Quick Brown Fox Jumped over the Lazy Dog. Again.";
    $_POST["id"] = 1;
    $this->assertEquals(self::$ci->blogger->savePost(), Blogger::EDIT);
    $this->assertTrue(self::$ci->blogger->renderPost("Hello-Title", null));
  }
  /**
   * [tearDownAfterClass description]
   */
  public static function tearDownAfterClass(): void {
    self::$ci->db->empty_table("admins");
    self::$ci->db->empty_table("blogger_posts_test_blog");
    self::$ci->db->empty_table("blogger_posts_admin_test_blog");
    self::$ci->load->dbforge();
    self::$ci->dbforge->drop_table("admins");
    self::$ci->dbforge->drop_table("blogger_posts_test_blog");
    self::$ci->dbforge->drop_table("blogger_posts_admin_test_blog");

  }
}
