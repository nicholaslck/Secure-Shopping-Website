
<?php
// init $_SESSION
include_once('admin/lib/db_inc.php');
include_once('lib/csrf.php');
session_start();

function ierg4210_login(){
	if (empty($_POST['email']) || empty($_POST['pw']) || !preg_match("/^[\w=+\-\/][\w='+\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$/", $_POST['email']) || !preg_match("/^[\w@#$%\^\&\*\-]+$/", $_POST['pw']))
		throw new Exception('Wrong Credentials');
	
	// Implement the login logic here
	$email = $_POST['email'];
	$pwd = $_POST['pw'];
	$login_success = false;

	global $db;
	$db = ierg4210_DB();
	$q=$db->prepare("SELECT * FROM user WHERE email=?");
	$q->execute(array($email));

	if($r = $q->fetch()){
		$saltedPwd = hash_hmac('sha1', $pwd, $r['salt']);
		if($saltedPwd == $r['saltedPassword']){
			$exp = time() + 3600 * 24 * 3;
			$token = array(
				'em'=>$r['email'],
				'exp'=>$exp,
				'k'=>hash_hmac('sha1', $exp.$r['saltedPassword'], $r['salt'])
				);

			setcookie('t4210', json_encode($token), $exp,'','',true,true);

			$_SESSION['t4210'] = $token;
			$login_success = true;
		}
	}
	
	
	if ($login_success){
		// redirect to admin page
		header('Location: admin/admin.php', true, 302);
		exit();
	} else
		throw new Exception('Wrong Credentials');
}

function ierg4210_logout(){
	// clear the cookies and session
	setcookie('t4210', '', time()-3600);
	// unset($_COOKIE['t4210']);

	session_destroy();

	// redirect to login page after logout
	header('Location: login.php',true,302);
	exit();
}



header("Content-type: text/html; charset=utf-8");

try {
	// input validation
	if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action']))
		throw new Exception('Undefined Action');
	
	// check if the form request can present a valid nonce
	include_once('lib/csrf.php');
	csrf_verifyNonce($_REQUEST['action'], $_POST['nonce']);
	
	// run the corresponding function according to action
	if (($returnVal = call_user_func('ierg4210_' . $_REQUEST['action'])) === false) {
		if ($db && $db->errorCode()) 
			error_log(print_r($db->errorInfo(), true));
		throw new Exception('Failed');
	} else {
		// no functions are supposed to return anything
		// echo $returnVal;
	}

} catch(PDOException $e) {
	error_log($e->getMessage());
	header('Refresh: 10; url=login.php?error=db');
	echo '<strong>Error Occurred:</strong> DB <br/>Redirecting to login page in 10 seconds...';
} catch(Exception $e) {
	header('Refresh: 10; url=login.php?error=' . $e->getMessage());
	echo '<strong>Error Occurred:</strong> ' . $e->getMessage() . '<br/>Redirecting to login page in 10 seconds...';
}
?>