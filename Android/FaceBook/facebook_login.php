<?php


require_once '../FaceBookClient/FaceBookClient.php';

session_start();

$client = new FaceBookClient();

// Application Name РЁТи
$client->setApplicationName("FaceBook Sample App");

if($client->validateAccessToken()){
  header('Location: ' . 'facebook_index.php'); 
}else{
  $authurl = $client->getAuthUrl();
  header('Location: ' . $authurl); 
}
?>