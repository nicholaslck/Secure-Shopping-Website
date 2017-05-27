	<?php 
require('PaypalIPN.php');

include_once('admin/lib/db_inc.php');


use PaypalIPN;

$ipn = new PaypalIPN();

// Use the sandbox endpoint during testing.
$ipn->useSandbox();
$verified = $ipn->verifyIPN();
if ($verified) {
    /*
     * Process IPN
     * A list of variables is available here:
     * https://developer.paypal.com/webapps/developer/docs/classic/ipn/integration-guide/IPNandPDTVariables/
     */
    global $db;
    $db = ierg4210_DB();

    $txn_id = $_POST['txn_id'];
	$q = $db->prepare("SELECT * FROM orders WHERE tid = ?");    
	$q->execute(array($txn_id));
	if ($q->rowCount() >= 1) {
		//duplicate payment
		return;
	}

	// error_log(print_r($_POST,true));

	$num_cart_items = $_POST['num_cart_items'];
	$digest = 'currency_code='.$_POST['mc_currency'];
	$digest = $digest.'&business='.$_POST['business'];
	for ($i=1; $i <= $num_cart_items; $i++) { 
		$digest = $digest.'&pid='.$_POST['item_number'.$i];
		$digest = $digest.'&quantity='.$_POST['quantity'.$i];
		$digest = $digest.'&current_price='.($_POST['mc_gross_'.$i] / $_POST['quantity'.$i]);
	}
	$digest = $digest.'&total_price='.($_POST['mc_gross']/1);

	$oid = $_POST['invoice'];
	$salt; $salteddigest;
	$q2 = $db->prepare('SELECT * FROM orders WHERE oid = ? ');
	if ($q2->execute(array($oid))) {
		$result = $q2->fetchAll();
		$salt = $result[0]['salt'];
		$salteddigest = $result[0]['digest'];
	}
	$salteddigest_paypal = hash_hmac('sha1', $digest, $salt);
	error_log('$database salt: '.$salt);
	error_log('$digest: '.$digest);
	error_log('$salteddigest from DB: '.$salteddigest);
	error_log('$salteddigest from paypal: '.$salteddigest_paypal);
	if ( strcmp($salteddigest, $salteddigest_paypal) == 0 ) {
		$q3 = $db->prepare("UPDATE orders SET tid = ? WHERE oid = ?");
		$q3->execute(array($txn_id, $oid));
		header("HTTP/1.1 200 OK");
	}


}

// Reply with an empty 200 response to indicate to paypal the IPN was received correctly