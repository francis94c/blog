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

You can manage multiple blogs with the library. To use another blog, you must have installed the blog (This simply means creating the table for the blog contents with the given name of a blog) with the ```install()``` function.

```php
/**
   * install [creates a table for a given blog name]
   * @param  [type] $blogName                The name of the blog
   * --------------------------------------------------------------------------------------------------------------------------------------------------------------------
   * @param  [type] $adminTableName          the name of the table containing admins (this is required if you have an admins section and you wan to keep track of who        *                                         creates or edits what). This is basically used to add a foreign key constraint on the blog table's column of admin if          *                                         provided.
   * --------------------------------------------------------------------------------------------------------------------------------------------------------------------
   * @param  [type] $adminIdColumnName       The name of the column in the given admin table that has the  id of each admin. this is usuall an AUTO_INCREMENT filed          *                                         called 'id'.
   * --------------------------------------------------------------------------------------------------------------------------------------------------------------------
   * @param  [type] $adminIdColumnConstraint [description]
   * @return [type]                          [description]
*/

$this->blogger->install("blog_name", "an_admin_table_name", "the_admin_column_name", "the_admin_column_constraint");

// All parameters are optional.

// If no blog name is given, it attempts to create a table called 'blogger_posts' by default.
```



### Wiki ###

Please head to the wiki at https://github.com/francis94c/blog/wiki for the full documentation.
