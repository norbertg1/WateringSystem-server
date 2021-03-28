<?php
echo "<table style='border: solid 1px black;'>";
echo "<tr><th>Irrigation ID</th><th>Device ID</th><th>start date</th><th>end date</th><th>on time</th><th>length(s)</th><th>length(L)</th><th>length(mm)</th><th>Done</th><th>Today</th><th>Com. ID</th></tr>";

class TableRows extends RecursiveIteratorIterator {
   function __construct($it) {
       parent::__construct($it, self::LEAVES_ONLY);
   }

   function current() {
       return "<td style='width: 110px,200px; border: 1px solid black;'>" . parent::current(). "</td>";
   }

   function beginChildren() {
       echo "<tr>";
   }

   function endChildren() {
       echo "</tr>" . "\n";
   }
}
	require_once("session.php");
	
	require_once("class.user.php");
	$auth_user = new USER();
	
	
	$user_id = $_SESSION['user_session'];
	
	$stmt = $auth_user->runQuery("SELECT * FROM users WHERE USER_ID=:user_id");
	$stmt->execute(array(":user_id"=>$user_id));
	
	$userRow=$stmt->fetch(PDO::FETCH_ASSOC);


    $result = $auth_user->runQuery("SELECT * FROM scheduled_irrigation order by ON_DATE desc");
    $result->execute();

    $result->setFetchMode(PDO::FETCH_ASSOC);
    foreach(new TableRows(new RecursiveArrayIterator($result->fetchAll())) as $k=>$v) {
        echo $v;
    }
?>