<?php
session_start();
session_unset();
session_destroy();
header("Location: ../pages/Login_v1.php");
exit;
?>