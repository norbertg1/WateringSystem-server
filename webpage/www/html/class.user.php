<?php

require_once('dbconfig.php');

class USER
{
	const REMEMBER_COOKIE = 'LOGIN_TOKEN';
	const REMEMBER_EXPIRATION_SECONDS = 7 * 24 * 3600;
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

	public function register($uname, $umail, $upass)
	{
		try {
			$new_password = password_hash($upass, PASSWORD_DEFAULT);

			$stmt = $this->conn->prepare("INSERT INTO users(USER_NAME,USER_EMAIL,USER_PASSWORD) 
		                                               VALUES(:uname, :umail, :upass)");

			$stmt->bindparam(":uname", $uname);
			$stmt->bindparam(":umail", $umail);
			$stmt->bindparam(":upass", $new_password);

			$stmt->execute();

			return $stmt;
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}


	public function doLogin($uname, $umail, $upass, $remember)
	{
		try {
			$stmt = $this->conn->prepare("SELECT USER_ID, USER_NAME, USER_EMAIL, USER_PASSWORD FROM users WHERE USER_NAME=:uname OR USER_EMAIL=:umail ");
			$stmt->execute(array(':uname' => $uname, ':umail' => $umail));
			$userRow = $stmt->fetch(PDO::FETCH_ASSOC);
			if ($stmt->rowCount() == 1) {
				if (password_verify($upass, $userRow['USER_PASSWORD'])) {
					$_SESSION['user_session'] = $userRow['USER_ID'];

					if ($remember) {
						$token = bin2hex(openssl_random_pseudo_bytes(16));
						$userId = $userRow['USER_ID'];
						$stmt = $this->conn->prepare("UPDATE users SET LOGIN_TOKEN=:token, LOGIN_TOKEN_CREATED_AT=CURRENT_TIMESTAMP where USER_ID = :userId");
						$stmt->bindparam(":token", $token);
						$stmt->bindparam(":userId", $userId);
						$stmt->execute();
						$cookieValue = "$token:$userId";
						setcookie(self::REMEMBER_COOKIE, $cookieValue, time() + self::REMEMBER_EXPIRATION_SECONDS);
					}

					return true;
				} else {
					return false;
				}
			}
		} catch (PDOException $e) {
			echo $e->getMessage();
		}
	}

	public function loginFromCookie()
	{
		if (isset($_COOKIE[self::REMEMBER_COOKIE])) {
			$parts = explode(":", $_COOKIE[self::REMEMBER_COOKIE]);
			if (sizeof($parts) == 2) {
				$token = $parts[0];
				$userId = $parts[1];

				$stmt = $this->conn->prepare("SELECT USER_ID FROM users WHERE USER_ID=:userId and LOGIN_TOKEN=:token and CURRENT_TIMESTAMP < LOGIN_TOKEN_CREATED_AT + :expiryPeriod");
				$stmt->execute(array(
					':userId' => $userId,
					':token' => $token,
					':expiryPeriod' => self::REMEMBER_EXPIRATION_SECONDS));
				$userRow = $stmt->fetch(PDO::FETCH_ASSOC);
				if ($stmt->rowCount() == 1) {
					$_SESSION['user_session'] = $userRow['USER_ID'];
					return true;
				}
			}
		}
	}

	public function is_loggedin()
	{
		if (isset($_SESSION['user_session'])) {
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
		setcookie(self::REMEMBER_COOKIE, null, -1);
		return true;
	}
}

?>