<?php
include('db.php');
$conn->query("TRUNCATE TABLE teams");
header("Location: index.php");
exit;
?>
