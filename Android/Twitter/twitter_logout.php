<?php

require_once '../TwitterClient/TwitterClient.php';

session_start();

$client = new TwitterClient();

$client->setApplicationName("Twitter Sample App");

$client->logout();

include(dirname(__FILE__).'/twitter_out.html');

?>
