<?php

$conn= mysql_connect("localhost","crowdple","VZGo8McLtPsw60j5") or die ("could not connect to mysql");
mysql_select_db("crowdple") or die ("no database"); 


$sqlCommand="select * from users";
$result=mysql_query($sqlCommand) or die(mysql_error());

while ($row = mysql_fetch_assoc($result)) {
    $newuser = strtolower($row['username']);
	$newuser = str_replace(' ', '', $newuser);
	$sql = "UPDATE users SET email = 'demo+".$newuser."@agriya.com' WHERE users.id=".$row['id'];  
	$retval = mysql_query( $sql);
  	if (!$retval){
   	 	echo "Update failed!!";
    }
    else
    {
    	echo "Update successfull;";
    } 
}

/*POST GRES
$db = pg_connect("host=localhost port=5432 dbname=bid user=postgres password=postgres");
$link = pg_Connect("host=localhost port=5432 dbname=bid user=postgres password=postgres");
$result = pg_exec($link, "select * from users");
$row = pg_fetch_all($result);
foreach($row as $row1 ){
   $result = pg_query($db,"UPDATE users SET email = 'demo+".$row1['username'].$row1['id']."@agriya.com' WHERE users.id=".$row1['id']); 
   if (!$result){
    echo "Update failed!!";
    }
    else
    {
    echo "Update successfull;";
    } 
}*/



?>
