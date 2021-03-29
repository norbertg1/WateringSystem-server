<?php
ini_set('display_errors', 1);

	require_once("session.php");
	
	require_once("class.user.php");
	$auth_user = new USER();
	
	
	$user_id = $_SESSION['user_session'];
	
	$stmt = $auth_user->runQuery("SELECT * FROM users WHERE USER_ID=:user_id");
	$stmt->execute(array(":user_id"=>$user_id));
	
	$userRow=$stmt->fetch(PDO::FETCH_ASSOC);
	$random_number = rand (1,17);
	#<link rel="stylesheet" href="style.css" type="text/css"  />

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<link rel="stylesheet" href="/bootstrap-3.3.5-dist/css/bootstrap.css" type="text/css"  />
<script type="text/javascript" src="jquery-1.11.3-jquery.min.js"></script>
<title>Locsoló - <?php print($userRow['USER_EMAIL']); ?></title>
<style>	#szoveg {margin-left:1em;}
		img.hatter {width:100%;height:100%;position:fixed;top:0;left:0;z-index:-5;}</style>
		<!-- #chartdiv {width:60%;height:450px;margin-top:40px;margin-right:1%;background:#3f3f4f;color:#ffffff;float:right;padding:5px;opacity:0.9;border-radius:25px;} -->

</head>
<body>
	<img class="hatter" src="/wallpapers/<?php print($random_number); ?>.jpg">
	<style>	body{color:white}
			a{color: white;}
</style>
	
    <div id="szoveg">
    <h1 style="margin-left:30%">Locsolórendszer</h1>
	<div id="chartdiv">
	  

	<a href="/last_week.php"><span class="glyphicon glyphicon-sort-by-order"></span>&nbsp;Last week data</a></p>
	<a href="/scheduled_irrigation.php"><span class="glyphicon glyphicon-tint"></span>&nbsp;Scheduled irrigation</a></p>
	<a href="/scheduled_irrigation_result.php"><span class="glyphicon glyphicon-tint"></span>&nbsp;Scheduled irrigation result</a></p>
	<a href="/devices.php"><span class="glyphicon glyphicon-certificate"></span>&nbsp;Devices (settings)</a></p>

	<a href="/logs">Log files</a>
	</div>


	<br><br><font size="3">Locsoló1 <br>
	<form name="form" action="" method="get">
  <input type="text" color ="black" name="subject" id="subject" value="Car Loan">
<form>
	<a href="set"class="btn btn-success navbar-btn">SET</a>
	<br>
	<br>


	<br><br><br><br>
    <li><a href="logout.php?logout=true"><span class="glyphicon glyphicon-log-out"></span>&nbsp;Sign Out</a></li>
	  </body>
</html>
