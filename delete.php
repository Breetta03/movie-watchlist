<?php
include 'config.php';

$id = $_GET['id'];

$sql = "DELETE FROM movies WHERE id = $id";
mysqli_query($conn, $sql);

header("Location: list.php");
exit();
?>
