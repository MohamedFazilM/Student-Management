<?php

include 'Sparrow.php';

$db=new Sparrow();

$db->setDb(array(

"type"=>"mysql",
"hostname"=>"localhost",
"database"=>"tests",
"username"=>"root",
"password"=>""

));

?>