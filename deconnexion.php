<?php
session_start();
session_unset();
session_destroy();
header('Location: ../Inscription/Inscription.php');
exit();
?>
