<?php

require_once '../TwitterClient/TwitterClient.php';

session_start();

$client = new TwitterClient();

$client->setApplicationName("Twitter Sample App");

$accessTokenInfo = array(); 

$message;
$accessToken;
$accessTokenSecret;
$requestToken;
$requestTokenSecret;
$loginSuccess;
if($client->validateAccessToken()){
  $accessToken = $client->getAccessToken();
  $accessTokenSecret = $client->getAccessTokenSecurty();
  $requestToken = $client->getRequestToken();
  $requestTokenSecret = $client->getRequestTokenSecurty();
  $loginSuccess=TRUE;
  $message = "ログイン完了しました。";
}else{
  $loginSuccess=FALSE;
  $message = "ログイン失敗しました。";
}

include(dirname(__FILE__).'/twitter_index.html');
  
?>