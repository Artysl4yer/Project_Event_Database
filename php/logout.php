<?php
session_start();
session_unset();
session_destroy();
header("Location: ../pages/1_Login.php");
exit();
?> 