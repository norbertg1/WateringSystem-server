<?php
echo "<table style='border: solid 1px black;'>";
echo "<tr><th>USER_ID</th><th>Device name</th><th>Device ID</th><th>Last on</th><th>on command</th><th>repeat rate</th><th>Irigation time</th><th>legth (s)</th><th>legth (L)</th><th>legth (mm)</th>
<th>moisture (%)</th><th>irrigation temp.</th><th>temp. points</th><th>m2</th><th>delay(s)</th><th>sleep(s)</th><th>rem. upd.</th><th>rem. log</th><th>Latid.</th><th>Longtd.</th></tr>";

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


    $result = $auth_user->runQuery("SELECT * FROM devices");
    $result->execute();

    $result->setFetchMode(PDO::FETCH_ASSOC);
    foreach(new TableRows(new RecursiveArrayIterator($result->fetchAll())) as $k=>$v) {
        echo $v;
    }
?>