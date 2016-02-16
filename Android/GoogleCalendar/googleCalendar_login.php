<?php


require_once '../GoogleClient/GoogleClient.php';

session_start();

$scope_array = array('');

$client = new GoogleClient();

// Application Name �ݒ�
$client->setApplicationName("Google Calendar Sample App");

// Oauth2 Scope �ݒ�
$client->setScopes($scope_array);

if($client->validateAccessToken()){
  header('Location: ' . 'googleCalendar_index.php'); 
}else{
  $authurl = $client->getAuthUrl();
  header('Location: ' . $authurl); 
}
?>