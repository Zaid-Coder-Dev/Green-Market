<?php
session_start();
session_unset();
session_destroy();
session_gc();
header('Location: authentification/authentification.php');
exit();
?>
