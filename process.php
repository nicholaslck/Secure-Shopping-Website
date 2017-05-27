<?php
include_once('admin/lib/db_inc.php');

function ierg4210_cart_checkout(){
	

	$digest = 'currency_code=HKD&business=nicholaslck-facilitator@gmail.com';
	$cart = $_POST['cart'];
	$total_price = 0;
	// DB manipulation
	global $db;
	$db = ierg4210_DB();

	foreach ($cart as $item) {
		$pid = $item['pid'];
		$quantity = $item['quantity'];
		$q = $db->prepare("SELECT price FROM products WHERE pid = ? ;");
		if ($q->execute(array($pid))) {
			$result = $q->fetchAll();
			$digest = $digest.'&pid='.$pid.'&quantity='.$quantity.'&current_price='.(int)$result[0]['price'];
			$total_price += $quantity * $result[0]['price'];
		}
	}

	$digest = $digest.'&total_price='.$total_price;
	error_log("Server digest: ".$digest);
	$salt = mt_rand();
	$salteddigest = hash_hmac('sha1', $digest, $salt);
	$time = date("Y-m-d h:i:sa");

	$q2 = $db->prepare("INSERT INTO orders (digest, salt, createdtime, price) VALUES (?, ?, ?, ?)");
	$q2->execute(array($salteddigest, $salt, $time, $total_price));

	$lastInsertId = $db->lastInsertId();

	$response = array("invoice"=>$lastInsertId, "custom"=>$salteddigest, "currency"=>"HKD");
	return $response;
}

function ierg4210_cart_checkout_getPrice(){
	$_POST['pid'] = (int)$_POST['pid'];

	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT price FROM products WHERE pid = ?");
	if ($q->execute(array($_POST['pid'])))
		$result = $q->fetchAll();

	$response = array("price"=>(int)$result[0]['price']);
	return $response;
}




function ierg4210_cat_fetchall() {
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT * FROM categories LIMIT 100;");
	if ($q->execute())
		return $q->fetchAll();
}

function ierg4210_prod_fetchAllFromCatid() {
	//input validation
	if (!preg_match('/^[\d]+$/', $_REQUEST['catid']))
		throw new Exception("invalid-catid");
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT * FROM products WHERE catid = ?");
	if ($q->execute(array($_REQUEST['catid'])))
		return $q->fetchAll();
}

function ierg4210_prod_fetch1FromCatid() {
	//input validation
	if (!preg_match('/^[\d]+$/', $_POST['catid']))
		throw new Exception("invalid-catid");
	// DB manipulation
	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT pid, catid, name, price FROM products WHERE catid = ?");
	if ($q->execute(array($_POST['catid'])))
		return $q->fetchAll();
}

function ierg4210_prod_fetchProdDetail(){
	$_POST['pid'] = (int) $_POST['pid'];

	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT * FROM products WHERE pid = ?");
	if ($q->execute(array($_POST['pid'])))
		return $q->fetchAll();
}

function ierg4210_prod_fetchProdDetail_1(){
	$_POST['pid'] = (int) $_POST['pid'];

	global $db;
	$db = ierg4210_DB();
	$q = $db->prepare("SELECT pid, name, price FROM products WHERE pid = ?");
	if ($q->execute(array($_POST['pid'])))
		return $q->fetchAll();
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
	echo 'while(1);' . json_encode(array('failed' => $e->getMessage()));
}
?>