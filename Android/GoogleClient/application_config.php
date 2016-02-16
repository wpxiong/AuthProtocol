<?php

global $app_config;

$app_config = array(
   
    'use_objects' => false,
   
    'application_name' => 'xxxxx',
   
    'oauth2_client_id' => 'xxxxxxxxm',
    
    'oauth2_client_secret' => 'xxxxxxxxxx',
    
    'oauth2_redirect_uri' => 'http://xxxxxxxxxxx/Android/GoogleCalendar/googleCalendar_index.php',
    
    'developer_key' => '',
  
    'site_name' => 'www.example.org',
  
   
      
    'services' => array(
      'calendar' => array(
          'scope' => array(
              "https://www.googleapis.com/auth/calendar",
              "https://www.googleapis.com/auth/calendar.readonly",
          )
      )
    )
);

?>