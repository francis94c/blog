[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/francis94c/blog/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/francis94c/blog/?branch=master) [![Build Status](https://travis-ci.org/francis94c/blog.svg?branch=master)](https://travis-ci.org/francis94c/blog) [![Coverage Status](https://coveralls.io/repos/github/francis94c/blog/badge.svg?branch=master)](https://coveralls.io/github/francis94c/blog?branch=master) [![Splint Identifier](https://splint.cynobit.com/shields/iconIdentifier/45FG415TGH)](https://splint.cynobit.com/packages/francis94c/blog) [![Splint Version](https://splint.cynobit.com/shields/iconVersion/45FG415TGH)](https://splint.cynobit.com/packages/francis94c/blog) ![Latest Release](https://img.shields.io/github/release/francis94c/blog.svg) ![Commits](https://img.shields.io/github/last-commit/francis94c/blog.svg)

# blog #
----

This library provides you the necessary back-end and some UI support required for you to manage blogged contents within your Code Igniter project such as actual blog posts, release notes, news, etc.

### Installation ###
Download and Install Splint from https://splint.cynobit.com/downloads/splint and run the below from the root of your Code Igniter project.
```bash
splint install francis94c/blog
```
### Usage ###
Top load the library, use
```php
$params = array("name" => "blog_name");
$this->load->splint("francis94c/blog", "+Blogger", $params, "blogger");
```

_You must load with a blog name._

You can manage multiple blogs with the library. To use another blog, you must have installed the blog (This simply means creating the table for the blog contents with the given name of a blog) with the ```install()``` function.

```php
/**
   * install [creates a table for a given blog name]
   * --------------------------------------------------------------------------------------------------------------------------------------------------------------------
   * @param  string $blogName                The name of the blog
   * --------------------------------------------------------------------------------------------------------------------------------------------------------------------
   * @param  string $adminTableName          the name of the table containing admins (this is required if you have an admins section and you wan to keep track of who
   *                                         creates/edits what). This is basically used to add a foreign key constraint on the blog table's column of admin if provided.
   * --------------------------------------------------------------------------------------------------------------------------------------------------------------------
   * @param  string $adminIdColumnName       The name of the column in the given admin table that has the  id of each admin. this is usuall an AUTO_INCREMENT field
   *                                         called 'id'.
   * --------------------------------------------------------------------------------------------------------------------------------------------------------------------
   * @param  int    $adminIdColumnConstraint The costrint of the id column in the admins table. e.g 7 for id INT(7), etc.
   * --------------------------------------------------------------------------------------------------------------------------------------------------------------------
   * @return bool                            true if successfull, false if not.
*/

$this->blogger->install([$blogName], [$adminTableName], [$adminColumnName], [$adminIdColumnConstraint]);

// All parameters are optional.

// If no blog name is given, it attempts to create a table called 'blogger_posts' by default.
```

To load the post editor, call the ```loadEditor()``` method.

```php
/**
   * loadEditor [loads a markdown editor.]
   * --------------------------------------------------------------------------------------------------------------------------------------------------------------------
   * @param  string  $callback the URI (controller/method combination) the editor form will be submitted to when any of the buttons 'Create, Save, Save and Publish' is
   *                           clicked. e.g 'MyBlog/savePost' for the controller 'MyBlog' with a 'savePost' function.
   * --------------------------------------------------------------------------------------------------------------------------------------------------------------------
   * @param  int     $postId   the id if a post to edit.
   * --------------------------------------------------------------------------------------------------------------------------------------------------------------------
   * @return null
*/
$this->blogger->loadEditor($callback, [$postId]); // $postId is optional, not pasing this argument means you want to create a new post.
```

The controller function specified by the ```$callback``` must call the library's ```savePost()``` function in order to commit every change to the database.

```php
$this->blogger->savePost([$adminId]); // $adminId is optional. However, you must supply this value if you installed the current blog with an admin table name and id.
```

### Example Application Flow ###

The blog package when loaded, makes available to you, class functions that help you carry out the basic function of a blog such as the below
* Create/Install a Blog (Internally created a table within your default database with '\_blog_posts' as suffix).
* Load Post Editor
* Perform persistent actions (Save, Publish & Delete);

The persistent action (Creation of a Blog Post) can be protected by setting a FOREIGN KEY constraint on the blog table with your admin table's ID column.

#### Install a Blog ####
This has been discusses in the [Usage](#usage) section.

This class method (`install`) is meant to be called in a `Controller` function on it's own as it doesn't need to be called often, just once to create your blog table. unless you have more advanced application where you dynamically create blog tables at will.

#### Load MarkDown Editor ####
```php
$this->blog->loadEditor($callback, $postId, $w3css);
```

### Wiki ###

Please head to the wiki at https://github.com/francis94c/blog/wiki for the full documentation.
