<?php

require_once('magic_quotes.php');
require_once('secure.conf.php');
require_once('path.conf.php');
require_once('mail.conf.php');
require_once('db.conf.php');

spl_autoload_register('loadClass');

function loadClass($class)
{
   if (class_exists($class, false))
      return;
   $path = PATH_BASE . 'php/classes/';
   $filename = $path . $class . '.php';
   if (file_exists($filename))
   {
      require_once($filename);
      return;
   }
   echo "<br/>\n<b>Loader error</b>: File '$class.php' not found in <b>$path</b><br>\n";
}

?>