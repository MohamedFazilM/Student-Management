<?php
include 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
  $db->from('student')->where('id', $id)->delete()->execute();
}

header("Location: index.php?deleted=1");
exit;
?>
