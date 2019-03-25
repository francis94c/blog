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
