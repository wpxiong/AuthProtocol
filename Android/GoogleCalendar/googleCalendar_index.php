<?php

$scope_array = array();

require_once '../GoogleClient/GoogleClient.php';

session_start();

$scope_array = array('');

$client = new GoogleClient();

// Application Name 設定
$client->setApplicationName("Google Calendar Sample App");

// Oauth2 Scope 設定
$client->setScopes($scope_array);

$message;
$accessToken;
$loginSuccess;

if($client->validateAccessToken()){
  $loginSuccess=TRUE;
  $accessToken = $client->getAccessToken();
  $message = "ログイン完了しました。";
}else{
  $loginSuccess=FALSE;
  $message = "ログイン失敗しました。";
}
include(dirname(__FILE__).'/googleCalendar_index.html');

?>