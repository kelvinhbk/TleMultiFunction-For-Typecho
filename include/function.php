<?php
function checkUser($user,$pass,$token){
	$data=array(
		"user"=>$user,
		"pass"=>$pass,
		"token"=>$token
	);
	$url = 'http://api.tongleer.com/open/login.php';
	$client = Typecho_Http_Client::get();
	if ($client) {
		$str = "";
		foreach ( $data as $key => $value ) { 
			$str.= "$key=" . urlencode( $value ). "&" ;
		}
		$data = substr($str,0,-1);
		$client->setData($data)
			//->setHeader('Authorization','Bearer '.$token)
			->setTimeout(30)
			->send($url);
		$status = $client->getResponseStatus();
		$rs = $client->getResponseBody();
		$arr=json_decode($rs,true);
		return $arr['code'];
	}
	return 0;
}
?>