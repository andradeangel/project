<?php
require_once("database.php");
custom_session_start('admin_session');

// Destruir la sesión
session_unset();
session_destroy();
session_write_close();
setcookie(session_name(),'',0,'/');
session_regenerate_id(true);

// Headers para prevenir el cacheo
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

// Redirección con JavaScript para prevenir el comportamiento del botón "atrás"
echo "<script>
    window.location.replace('/');
    history.pushState(null, '', '/');
    window.onpopstate = function () {
        history.go(1);
    };
</script>";
exit();
?>
