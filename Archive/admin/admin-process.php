<?php
session_start();
include_once('lib/db_inc.php');
include_once('../lib/csrf.php');

include_once('../auth.php');

// // session_regenerate_id();
// if(!empty($_SESSION['t4210'])){
// 	if(!empty($_COOKIE['t4210'])){
// 		if($t = json_decode(stripslashes($_COOKIE['t4210']),true)){
// 			if(time() > $t['exp']){
// 				setcookie('t4210', '', time()-3600);
// 				session_destroy();
// 				header('Location: ../login.php', true, 302);
// 				exit();
// 			}
// 			global $db;
// 			$db = ierg4210_DB();
// 			$q= $db->prepare("SELECT * FROM user WHERE email=?");
// 			$q->execute(array($t['em']));
// 			if($r = $q->fetch()){
// 				$realk = hash_hmac('sha1', $t['exp'].$r['saltedPassword'], $r['salt']);
// 				if($realk == $t['k']){
// 					$_SESSION['t4210'] = $t;
// 				}
// 			}
// 			// $db = NULL;
// 		}
// 	}
// }else{
	
// 	unset($_COOKIE['t4210']);
// 	session_destroy();
// 	header('Location: ../login.php', true, 302);
// 	exit();
// }
auth();


function ierg4210_cat_fetchall() {
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT * FROM categories LIMIT 100;");
	if ($q->execute())
		return $q->fetchAll();
}

function ierg4210_cat_insert() {
	// input validation or sanitization
	//check if csrf is encountered
	// error_log(print_r($_SESSION['csrf_nonce'], TRUE));
	// error_log(print_r($_POST['nonce'], TRUE));
	error_log(print_r(csrf_verifyNonce($_REQUEST['action'], $_POST['nonce']), TRUE));

	if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
		throw new Exception("invalid-name");

	

	// DB manipulation


	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("INSERT INTO categories (name) VALUES (?)");
	return $q->execute(array($_POST['name']));
}

function ierg4210_cat_edit() {
	//input validation
	//check if csrf is encountered
	csrf_verifyNonce($_REQUEST['action'], $_POST['nonce']);
	if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
		throw new Exception("invalid-name");	



	//DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("UPDATE categories SET name = ? WHERE catid = ?");
	return $q->execute(array($_POST['name'],$_POST['catid']));
}

function ierg4210_cat_delete() {

	// input validation or sanitization
	$_POST['catid'] = (int) $_POST['catid'];

	// DB manipulation
	global $db;
	$db = ierg4210_DB();

	$q = $db->prepare("DELETE FROM products WHERE catid = ?");
	$q->execute(array($_POST['catid']));

	$q = $db->prepare("DELETE FROM categories WHERE catid = ?");
	return $q->execute(array($_POST['catid']));
}

// Since this form will take file upload, we use the tranditional (simpler) rather than AJAX form submission.
// Therefore, after handling the request (DB insert and file copy), this function then redirects back to admin.php

function ierg4210_prod_fetchFromCatid() {
	//input validation
	if (!preg_match('/^[\d]+$/', $_POST['catid']))
		throw new Exception("invalid-catid");
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT * FROM products WHERE catid = ?");
	if ($q->execute(array($_POST['catid'])))
		return $q->fetchAll();
}

function ierg4210_prod_insert() {
	//check if csrf is encountered
	//csrf_verifyNonce($_REQUEST['action'], $_POST['nonce']);
	// input validation or sanitization
	if (!preg_match('/^[\d]+$/', $_POST['catid']))
		throw new Exception("invalid-catid");
	if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
		throw new Exception("invalid-name");
	if (!preg_match('/^[\d]+[\.]{0,1}[\d]*$/', $_POST['price']))
		throw new Exception("invalid-price");
	if (!preg_match('/^[\w\-,\?\!\.\'\" ]*$/', $_POST['description']))
		throw new Exception("invalid-description");



	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("INSERT INTO products (catid, name, price, description) VALUES (?, ?, ?, ?)");
	$q->execute(array($_POST['catid'],$_POST['name'], $_POST['price'], $_POST['description']));
	
	
	// The lastInsertId() function returns the pid (primary key) resulted by the last INSERT command
	$lastId = $db->lastInsertId();

	// Copy the uploaded file to a folder which can be publicly accessible at incl/img/[pid].jpg 
	if ($_FILES["file"]["error"] == 0
		&& ($_FILES["file"]["type"] == "image/jpeg" or $_FILES["file"]["type"] == "image/gif" or $_FILES["file"]["type"] == "image/png")
		&& $_FILES["file"]["size"] < 5000000) {

		$extension; //Determine the image type for saving
		switch($_FILES["file"]["type"]){
			case "image/jpeg":
				$extension = ".jpg";
				break;
			case "image/gif":
				$extension = ".gif";
				break;
			case "image/png":
				$extension = ".png";
				break;
		}
		// Note: Take care of the permission of destination folder (hints: current user is apache)
		if (move_uploaded_file($_FILES["file"]["tmp_name"], "/var/www/html/img/" . $lastId . $extension)) {
			// redirect back to original page; you may comment it during debug
			header('Location: admin.php');
			exit();
		}

	}
	// Only an invalid file will result in the execution below
	// Remove the SQL record that was just inserted
	$q = $db->prepare("DELETE FROM products WHERE pid = ?");
	$q->execute(array($lastId));
	
	// To replace the content-type header which was json and output an error message
	header('Content-Type: text/html; charset=utf-8');
	echo 'Invalid file detected. Please check whether you have upload an image file as ".jpeg" or ".gif" or ".png" format.<br/><a href="javascript:history.back();">Back to admin panel.</a>';
	exit();
}

// TODO: add other functions here to make the whole application complete
function ierg4210_prod_delete(){
	//Doing sanitization
	$_POST['pid'] = (int) $_POST['pid'];

	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("DELETE FROM products WHERE pid = ?");
	return $q->execute(array($_POST['pid']));
}

function ierg4210_prod_fetchProdDetail(){
	$_POST['pid'] = (int) $_POST['pid'];

	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT * FROM products WHERE pid = ?");
	if ($q->execute(array($_POST['pid'])))
		return $q->fetchAll();

}

function ierg4210_prod_edit() {
	//check if csrf is encountered
	csrf_verifyNonce($_REQUEST['action'], $_POST['nonce']);
	$_POST['pid'] = (int) $_POST['pid'];
	if (!preg_match('/^[\d]+$/', $_POST['catid']))
		throw new Exception("invalid-catid");
	if (!preg_match('/^[\w\- ]+$/', $_POST['name']))
		throw new Exception("invalid-name");
	if (!preg_match('/^[\d]+[\.]{0,1}[\d]*$/', $_POST['price']))
		throw new Exception("invalid-price");
	if (!preg_match('/^[\w\d\-,\?\!\.\'\" ]*$/', $_POST['description']))
		throw new Exception("invalid-description");


	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("UPDATE products SET catid = ? , name = ? , price = ? , description = ? WHERE pid = ?");
	$q->execute(array($_POST['catid'], $_POST['name'], $_POST['price'], $_POST['description'], $_POST['pid']));
	

	if ($_FILES["file"]["error"] == 0
		&& ($_FILES["file"]["type"] == "image/jpeg" or $_FILES["file"]["type"] == "image/gif" or $_FILES["file"]["type"] == "image/png")
		&& $_FILES["file"]["size"] < 5000000) {

		$extension; //Determine the image type for saving
		switch($_FILES["file"]["type"]){
			case "image/jpeg":
				$extension = ".jpg";
				break;
			case "image/gif":
				$extension = ".gif";
				break;
			case "image/png":
				$extension = ".png";
				break;
		}
		// Note: Take care of the permission of destination folder (hints: current user is apache)
		if (move_uploaded_file($_FILES["file"]["tmp_name"], "../img/" . $_POST['pid'] . $extension)) {
			// redirect back to original page; you may comment it during debug
			header('Location: admin.php');
			exit();
		}

	}
	header('Location: admin.php');
	exit();
}

function ierg4210_user_create(){
	//check if csrf is encountered
	csrf_verifyNonce($_REQUEST['action'], $_POST['nonce']);
	if (!preg_match("/^[\w=+\-\/][\w='+\-\/\.]*@[\w\-]+(\.[\w\-]+)*(\.[\w]{2,6})$/", $_POST['email']))
		throw new Exception("invalid-email");
	if (!preg_match("/^[\w\d\-,\?\!\.\'\" ]{6,}$/", $_POST['password']))
		throw new Exception("invalid-password: password must be at least 6 characters");


	$email = $_POST['email'];
	$pwd = $_POST['password'];

	$salt = mt_rand();
	$saltedPwd = hash_hmac('sha1', $_POST['password'], $salt);

	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("INSERT INTO user (email, salt, saltedPassword) VALUES (? , ? , ?)");
	$q->execute(array($email, $salt, $saltedPwd));
	
	header('Location: admin.php');
	exit();
}


//TODO: Enable the header function to using .json
header('Content-Type: application/json');

// input validation
if (empty($_REQUEST['action']) || !preg_match('/^\w+$/', $_REQUEST['action'])) {
	echo json_encode(array('failed'=>'undefined'));
	exit();
}

// The following calls the appropriate function based to the request parameter $_REQUEST['action'],
//   (e.g. When $_REQUEST['action'] is 'cat_insert', the function ierg4210_cat_insert() is called)
// the return values of the functions are then encoded in JSON format and used as output
try {
	if (($returnVal = call_user_func('ierg4210_' . $_REQUEST['action'])) === false) {
		if ($db && $db->errorCode()) 
			error_log(print_r($db->errorInfo(), true));
		echo json_encode(array('failed'=>'1'));
	}
	echo 'while(1);' . json_encode(array('success' => $returnVal));
} catch(PDOException $e) {
	error_log($e->getMessage());
	echo json_encode(array('failed'=>'error-db'));
} catch(Exception $e) {
	// echo 'while(1);' . json_encode(array('failed' => $e->getMessage()));
}
?>