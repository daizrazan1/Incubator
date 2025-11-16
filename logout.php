<?php
require_once 'db_config.php';

logoutUser();
header('Location: index.php');
exit;
?>
