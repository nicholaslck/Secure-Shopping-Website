<?php 

// session_regenerate_id();
function auth(){
	if(!empty($_SESSION['t4210'])){
		$user_email = $_SESSION['t4210']['em'];
		if(!empty($_COOKIE['t4210'])){
			if($t = json_decode(stripslashes($_COOKIE['t4210']),true)){
				if(time() > $t['exp']){
					setcookie('t4210', '', time()-3600);
					session_destroy();
					header('Location: ../login.php', true, 302);
					exit();
				}
				global $db;
				$db = ierg4210_DB();
				$q= $db->prepare('SELECT * FROM user WHERE email=?');
				$q->execute(array($t['em']));
				if($r = $q->fetch()){
					$realk = hash_hmac('sha1', $t['exp'].$r['saltedPassword'], $r['salt']);
					if($realk == $t['k']){
						$_SESSION['t4210'] = $t;
						return 1;
					}
				}
			}
		}
	}else{
		$user_email = 'unknown';
		unset($_COOKIE['t4210']);
		session_destroy();
		header('Location: ../login.php', true, 302);
		exit();
	}
}
?>