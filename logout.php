<?php
require_once("database.php");
custom_session_start('admin_session');
session_destroy();
header("Location: index.php");
exit();
?>
