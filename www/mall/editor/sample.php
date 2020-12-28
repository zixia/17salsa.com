<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
 <head>
  <title> new document </title>
  <meta name="generator" content="editplus" />
  <meta name="author" content="" />
  <meta name="keywords" content="" />
  <meta name="description" content="" />
 </head>

 <body>
  <h1>RichEditor</h1>
    <?php
    include('editor.php');

    $editor = new RichEditor('editor');
    $editor->value = "<b>richeditor</b> is best, is stronger";
    $editor->border_color = "blue";
    $editor->show('full');
    ?>

 </body>
</html>
