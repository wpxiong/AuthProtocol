<?php

require_once '../TwitterClient/TwitterClient.php';

session_start();

$client = new TwitterClient();

$client->setApplicationName("Twitter Sample App");
if($client->validateAccessToken()){
  header('Location: ' . 'twitter_index.php'); 
}else{
  $client->createRequestURL();
  $authurl = $client->getAuthUrl();
  header('Location: ' . $authurl); 
}

?>
