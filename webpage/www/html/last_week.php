<?php
require_once("session.php");
require_once("class.user.php");
echo "<table style='border: solid 1px black;'>";
echo "<tr><th>Id</th><th>Device name</th><th>Last login</th><th>Temperature</th><th>Humidity</th><th>Moisture</th><th>Pressure</th><th>L</th><th>L/s</th><th>Voltage</th><th>On/Off</th>
<th>Temp. openweather</th><th>Rain(mm)</th><th>RSSI</th><th>Awake(s)</th><th>Version</th><th>RST reason</th></tr>";

class TableRows extends RecursiveIteratorIterator {
   function __construct($it) {
       parent::__construct($it, self::LEAVES_ONLY);
   }

   function current() {
       return "<td style='width: 200px,150; border: 1px solid black; text-align:center;'>" . parent::current(). "</td>";
   }

   function beginChildren() {
       echo "<tr>";
   }

   function endChildren() {
       echo "</tr>" . "\n";
   }
}
	$auth_user = new USER();
	
	
	$user_id = $_SESSION['user_session'];
	
	$stmt = $auth_user->runQuery("SELECT * FROM users WHERE USER_ID=:user_id");
	$stmt->execute(array(":user_id"=>$user_id));
	
	$userRow=$stmt->fetch(PDO::FETCH_ASSOC);


    $result = $auth_user->runQuery("SELECT * FROM last_week order by LAST_LOGIN desc");
    $result->execute();

    $result->setFetchMode(PDO::FETCH_ASSOC);
    foreach(new TableRows(new RecursiveArrayIterator($result->fetchAll())) as $k=>$v) {
        echo $v;
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
<link href="bootstrap/css/bootstrap-theme.min.css" rel="stylesheet" media="screen">
<script type="text/javascript" src="jquery-1.11.3-jquery.min.js"></script>
<link rel="stylesheet" href="style.css" type="text/css"  />
<title>welcome - <?php print($userRow['USER_EMAIL']); ?></title>
</head>



</html>