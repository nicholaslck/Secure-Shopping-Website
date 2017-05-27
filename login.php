<?php
include_once('lib/csrf.php');
include_once('admin/lib/db_inc.php');

session_start();
// session_regenerate_id();
if(!empty($_SESSION['t4210'])){
	if(!empty($_COOKIE['t4210'])){
		if($t = json_decode(stripslashes($_COOKIE['t4210']),true)){
			if(time() > $t['exp']){
				unset($_COOKIE['t4210']);
				session_destroy();
				header('Location: login.php', true, 302);
				exit(); // to expire the user
			}
			global $db;
			$db = ierg4210_DB();
			$q= $db->prepare('SELECT * FROM user WHERE email=?');
			$q->execute(array($t['em']));
			if($r = $q->fetch()){
				$realk = hash_hmac('sha1', $t['exp'].$r['saltedPassword'], $r['salt']);
				if($realk == $t['k']){
					$_SESSION['t4210'] = $t;
					header('Location: admin/admin.php', true, 302);
					exit();
				}
			}
		}
	}
}else{}

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>AStore Login</title>
</head>
<body>
<h1>AStore Login</h1>
<fieldset>
	<legend>Login Form</legend>
	<form id="loginForm" method="POST" action="auth-process.php?action=<?php echo ($action = 'login'); ?>">
		<label for="email">Email:</label>
		<input type="text" name="email" required="true" pattern="^[\w=+\-\/][\w=\'+\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$" />
		<label for="pw">Password:</label>
		<input type="password" name="pw" required="true" pattern="^[\w@#$%\^\&\*\-]+$" />
		<input type="hidden" name="nonce" value="<?php echo csrf_getNonce($action); ?>"/>
		<input type="submit" value="Submit" />
	</form>
</fieldset>

<a href="index.php">Go back to Home page</a>
</body>
</html>