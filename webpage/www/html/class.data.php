<?php

require_once('dbconfig.php');

class DATA
{	

	private $conn;
	
	public function __construct()
	{
		$database = new Database();
		$db = $database->dbConnection();
		$this->conn = $db;
    }
	
	public function runQuery($sql)
	{
		$stmt = $this->conn->prepare($sql);
		return $stmt;
	}
	
	public function readdata()
	{

	$sql = "SELECT TEMPERATURE FROM data WHERE DEVICE_ID = 'szenzor1' ORDER BY LAST_LOGIN DESC LIMIT 1";
	$result = $conn->query($sql);
	$row = $result->fetch_assoc();
	echo $row['TEMPERATURE'];
	}
	
	
	public function doLogin($uname,$umail,$upass)
	{
		try
		{
			$stmt = $this->conn->prepare("SELECT USER_ID, USER_NAME, USER_EMAIL, USER_PASSWORD FROM users WHERE USER_NAME=:uname OR USER_EMAIL=:umail ");
			$stmt->execute(array(':uname'=>$uname, ':umail'=>$umail));
			$userRow=$stmt->fetch(PDO::FETCH_ASSOC);
			if($stmt->rowCount() == 1)
			{
				if(password_verify($upass, $userRow['USER_PASSWORD']))
				{
					$_SESSION['user_session'] = $userRow['USER_ID'];
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();
		}
	}
	
	public function is_loggedin()
	{
		if(isset($_SESSION['user_session']))
		{
			return true;
		}
	}
	
	public function redirect($url)
	{
		header("Location: $url");
	}
	
	public function doLogout()
	{
		session_destroy();
		unset($_SESSION['user_session']);
		return true;
	}
}
?>
