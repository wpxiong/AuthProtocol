<?php

require_once '../FaceBookClient/FaceBookClient.php';

session_start();

$client = new FaceBookClient();

$client->setApplicationName("FaceBook Sample App");

$client->logout();

include(dirname(__FILE__).'/facebook_out.html');

?>
