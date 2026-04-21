<?php
session_start();
session_unset();
session_destroy();
header("Location: /uniprojectHub-main/index.php");
exit();
?>
