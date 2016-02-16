<?php

$scope_array = array();

require_once '../FaceBookClient/FaceBookClient.php';

session_start();

$client = new FaceBookClient();
// Application Name 設定
$client->setApplicationName("FaceBook Sample App");

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
include(dirname(__FILE__).'/facebook_index.html');

?>