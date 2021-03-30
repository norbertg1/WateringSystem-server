<?php
session_start();
require_once("class.user.php");
$login = new USER();

if ($login->is_loggedin() || $login->loginFromCookie()) {
	$login->redirect('home.php');
}

if (isset($_POST['btn-login'])) {
	$uname = strip_tags($_POST['txt_uname_email']);
	$umail = strip_tags($_POST['txt_uname_email']);
	$upass = strip_tags($_POST['txt_password']);
	$rememberMe = isset($_POST['remember_me']);

	if ($login->doLogin($uname, $umail, $upass, $rememberMe)) {
		$login->redirect('home.php');
	} else {
		$error = "Wrong Details !";
	}
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Coding Cage : Login</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <link href="bootstrap/css/bootstrap-theme.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="style.css" type="text/css"/>
    <!-- ****** faviconit.com favicons ****** -->
    <link rel="shortcut icon" href="faviconit/favicon.ico">
    <link rel="icon" sizes="16x16 32x32 64x64" href="faviconit/favicon.ico">
    <link rel="icon" type="image/png" sizes="196x196" href="faviconit/favicon-192.png">
    <link rel="icon" type="image/png" sizes="160x160" href="faviconit/favicon-160.png">
    <link rel="icon" type="image/png" sizes="96x96" href="faviconit/favicon-96.png">
    <link rel="icon" type="image/png" sizes="64x64" href="faviconit/favicon-64.png">
    <link rel="icon" type="image/png" sizes="32x32" href="faviconit/favicon-32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="faviconit/favicon-16.png">
    <link rel="apple-touch-icon" href="faviconit/favicon-57.png">
    <link rel="apple-touch-icon" sizes="114x114" href="faviconit/favicon-114.png">
    <link rel="apple-touch-icon" sizes="72x72" href="faviconit/favicon-72.png">
    <link rel="apple-touch-icon" sizes="144x144" href="faviconit/favicon-144.png">
    <link rel="apple-touch-icon" sizes="60x60" href="faviconit/favicon-60.png">
    <link rel="apple-touch-icon" sizes="120x120" href="faviconit/favicon-120.png">
    <link rel="apple-touch-icon" sizes="76x76" href="faviconit/favicon-76.png">
    <link rel="apple-touch-icon" sizes="152x152" href="faviconit/favicon-152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="faviconit/favicon-180.png">
    <meta name="msapplication-TileColor" content="#FFFFFF">
    <meta name="msapplication-TileImage" content="/favicon-144.png">
    <meta name="msapplication-config" content="/browserconfig.xml">
    <!-- ****** faviconit.com favicons ****** -->
</head>
<body>

<div class="signin-form">
    <div class="container">
        <form class="form-signin" method="post" id="login-form">
            <h2 class="form-signin-heading">Log In to WebApp.</h2>
            <hr/>
            <div id="error">
				<?php
				if (isset($error)) {
					?>
                    <div class="alert alert-danger">
                        <i class="glyphicon glyphicon-warning-sign"></i> &nbsp; <?php echo $error; ?> !
                    </div>
					<?php
				}
				?>
            </div>

            <div class="form-group">
                <input type="text" class="form-control" name="txt_uname_email" placeholder="Username or E mail ID"
                       required/>
                <span id="check-e"></span>
            </div>

            <div class="form-group">
                <input type="password" class="form-control" name="txt_password" placeholder="Your Password"/>
            </div>
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="remember_me"> Keep me logged in
                </label>
            </div>

            <hr/>

            <div class="form-group">
                <button type="submit" name="btn-login" class="btn btn-default">
                    <i class="glyphicon glyphicon-log-in"></i> &nbsp; SIGN IN
                </button>
            </div>
            <br/>
            <label>Don't have account yet ! <a href="sign-up.php">Sign Up</a></label>
        </form>
    </div>
</div>
</body>
</html>