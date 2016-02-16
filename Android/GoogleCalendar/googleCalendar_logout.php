<?php

require_once '../GoogleClient/GoogleClient.php';

session_start();

$scope_array = array('');

$client = new GoogleClient();

// Application Name Ý’è
$client->setApplicationName("Google Calendar Sample App");

// Oauth2 Scope Ý’è
$client->setScopes($scope_array);

$client->logout();

include(dirname(__FILE__).'/googleCalendar_out.html');

?>
