<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class BlogTest extends TestCase {

  /**
   * Code Igniter Instance.
   * @var object
   */
  private static $ci;

  /**
   * Prerquisites for the Unit Tests.
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
   * Test all functions relating to the installation of a blog. this is just the
   * creation of tables under the hood.
   */
  public function testInstallBlog(): void {
    $this->assertTrue(self::$ci->blogger->install("test_blog"), "Blog Installed Successfuly without admin ID constraint.");
    $this->assertTrue(self::$ci->db->table_exists(Blogger::TABLE_PREFIX . "_test_blog"));
    $fields = self::$ci->db->list_fields(Blogger::TABLE_PREFIX . "_test_blog");
    $this->assertContains("id", $fields);
    $this->assertContains("title", $fields);
    $this->assertContains("content", $fields);
    $this->assertContains("slug", $fields);
    $this->assertContains("date_created", $fields);
    $this->assertContains("date_published", $fields);
    $this->assertTrue(self::$ci->blogger->install("test_blog"), "Verify CREATE IF NOT EXISTS clause");
    $this->assertTrue(self::$ci->blogger->install("admin_test_blog", "admins", "id", 7), "Create Blog with existent admin constarint");
    $fields = self::$ci->db->list_fields(Blogger::TABLE_PREFIX . "_admin_test_blog");
    $this->assertContains("id", $fields);
    $this->assertContains("title", $fields);
    $this->assertContains("content", $fields);
    $this->assertContains("slug", $fields);
    $this->assertContains("date_created", $fields);
    $this->assertContains("date_published", $fields);
  }
  /**
   * Test UI functions. This just out pust  HTML for manual inspection. The optimal
   * inspection for this part is to use the Code Igniter Unit Testing system that
   * outputs to a browser. See https://splint.cynobit/wiki
   *
   * @depends testInstallBlog
   */
  public function testUI(): void {
    $this->assertTrue(self::$ci->blogger->loadEditor("callback"), "Load Editor");
    self::$ci->blogger->setBlog("test_blog");
    $this->assertTrue(self::$ci->blogger->renderPostItems(null, null, null, 1, 0), "Test load empty posts set");
  }
  /**
   * Test the blog post saving functionality of the library.
   * Create, Save, Publish, Create and Publish
   * @depends testInstallBlog
   */
  public function testBlogSave(): void {
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
    $post = self::$ci->blogger->getPost("Hello-Title", false);
    $this->assertTrue(is_array($post));
    $this->assertArrayHasKey("id", $post);
    $this->assertArrayHasKey("title", $post);
    $this->assertArrayHasKey("content", $post);
    $this->assertArrayHasKey("published", $post);
    $this->assertArrayHasKey("date_published", $post);
    $this->assertArrayHasKey("slug", $post);
    $this->assertEquals(1, $post["id"], "Assert Post ID");
    $this->assertEquals("Hello Title", $post["title"], "Assert Post Title");
    $this->assertEquals("The Quick Brown Fox Jumped over the Lazy Dog. Again.", $post["content"]);
    $this->assertEquals("Hello-Title", $post["slug"]);
    $this->assertEquals(0, $post["published"]);
    $this->assertEquals(null, $post["date_published"]);
    $_POST["action"] = "publish";
    $this->assertEquals(self::$ci->blogger->savePost(), Blogger::PUBLISH);
    $post = self::$ci->blogger->getPost("Hello-Title", false);
    $this->assertTrue(is_array($post));
    $this->assertEquals(1, $post["published"]);
    $this->assertNotEquals(null, $post["date_published"]);
    $_POST["action"] = "createAndPublish";
    $_POST["title"] = "Hello Title 2";
    $_POST["editor"] = "Create and Published Post.";
    unset($_POST["id"]);
    $this->assertEquals(Blogger::CREATE_AND_PUBLISH, self::$ci->blogger->savePost());
    $post = self::$ci->blogger->getPost("Hello-Title-2", false);
    $this->assertTrue(is_array($post));
    $this->assertArrayHasKey("id", $post);
    $this->assertArrayHasKey("title", $post);
    $this->assertArrayHasKey("content", $post);
    $this->assertArrayHasKey("published", $post);
    $this->assertArrayHasKey("date_published", $post);
    $this->assertArrayHasKey("slug", $post);
    $this->assertEquals(2, $post["id"], "Assert Post ID");
    $this->assertEquals("Hello Title 2", $post["title"], "Assert Post Title");
    $this->assertEquals("Create and Published Post.", $post["content"]);
    $this->assertEquals("Hello-Title-2", $post["slug"]);
    $this->assertEquals(1, $post["published"]);
    $this->assertNotEquals(null, $post["date_published"]);
    $this->assertEquals(Blogger::ABORT, self::$ci->blogger->savePost(), "No 2 blog posts can have the same title.");
    // TODO: With Admin.
  }
  /**
   * Test Setters and Getters.
   */
  public function testDynamicFunctions(): void {
    self::$ci->blogger->setBlog("rocket_blog");
    $this->assertEquals(Blogger::TABLE_PREFIX . "_rocket_blog", self::$ci->blogger->getName(), "Blogger setBlog works.");
  }
  /**
   * Clear and Free up persistent used resources for this test class.
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
