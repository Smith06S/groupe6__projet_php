<?php
$hello = "World";
?>
<h1>Hello <?php echo $hello ?> !</h1>

<?php
$mysqli = new mysqli("localhost", "root", "", "php_exam_db");

$result = $mysqli->query("SELECT * FROM users");
?>
