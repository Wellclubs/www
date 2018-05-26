<?php
$file = str_replace('\\', '/', __FILE__);
define("PATH_BASE", substr($file, 0, strlen($file) - strlen('php/config/path.conf.php')));
?>