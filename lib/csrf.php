<?php
function csrf_getNonce($action){
	// Generate a nonce with mt_rand()
	$nonce = mt_rand();
	
	// With regard to $action, save the nonce in $_SESSION 
	if (!isset($_SESSION['csrf_nonce'])) 
		$_SESSION['csrf_nonce'] = array();
	$_SESSION['csrf_nonce'][$action] = $nonce;
	
	// Return the nonce2
	return $nonce;
}

// Check if the nonce returned by a form matches with the stored one.
function csrf_verifyNonce($action, $receivedNonce){
	// We assume that $REQUEST['action'] is already validated
	// error_log(array_key_exists('cat_insert', $_SESSION['csrf_nonce']));
	// error_log(array_key_exists($action, $_SESSION['csrf_nonce']));
	if (isset($receivedNonce) && ($_SESSION['csrf_nonce'][$action] == $receivedNonce)) {
		// comment the line below for AJAX form submissions
		unset($_SESSION['csrf_nonce'][$action]);
		return true;
	}
	
	throw new Exception('csrf-attack');
}
?>