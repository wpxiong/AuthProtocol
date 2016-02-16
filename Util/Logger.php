<?php

class Logger {

   public static $logger = NULL;
   
   private function __construct() {
   
   }
   
   public function debug($var) {
     var_dump($var);
   }
   
   public static function getLogger(){
     if(is_null(self::$logger)){
       self::$logger = new Logger();
     }
     return self::$logger;
   }
   
}


class CasheDataUtil {

 
 static $cacheFile;
 
 static $config_path = 'cache.txt';
 
 private $ini_array;
 
 private function __construct(){
    $this->read_php_ini();
 }
 
 public function readTwitterAccessTokenValue(){
    return $this->ini_array['Twitter']['accessToken'];
 }
 
 public function readTwitterAccessTokenSecretValue(){
    return $this->ini_array['Twitter']['accessTokenSecret'];
 }
 
 public function readTwitterRequestTokenValue(){
    return $this->ini_array['Twitter']['requestToken'];
 }
 
 public function readTwitterRequestTokenSecretValue(){
    return $this->ini_array['Twitter']['requestTokenSecret'];
 }
    
 public function readFaceBookAccessTokenValue(){
    return $this->ini_array['FaceBook']['accessToken'];
 }
 
 public function readGoogleAccessTokenValue(){
    return $this->ini_array['Google']['accessToken'];
 }
 
 public function getTwitterMessage(){
    return $this->ini_array['TwitterMessage']['message'];
 }
 
 public function refreshFaceBookTokenValue($accessToken,$expires){
  $accessTokenInfo = array();
   if(isset($accessToken)){
      $accessTokenInfo['accessToken'] = $accessToken;
   } 
   if(isset($expires)){
       $accessTokenInfo['expires'] = $expires;
   }
   $this->ini_array['FaceBook']['accessToken'] = $accessTokenInfo;
   $this->write_php_ini($this->ini_array,self::getCacheFilePath());
 }
 
 public function refreshGoogleTokenValue($accessToken,$expires,$tokenType,$refreshToken){
   $accessTokenInfo = array();
   if(isset($accessToken)){
     $accessTokenInfo['accessToken'] = $accessToken;
   }
   if(isset($expires)){
     $accessTokenInfo['expires'] = $expires;
   }
   if(isset($tokenType)){
     $accessTokenInfo['tokenType'] = $tokenType;
   }
   if(isset($refreshToken)){
     $accessTokenInfo['refreshToken'] = $refreshToken;
   }
   $this->ini_array['Google']['accessToken'] = $accessTokenInfo;
   $this->write_php_ini($this->ini_array,self::getCacheFilePath());
 }
 
 
 public function refreshAccessTokenValue($accessToken, $accessTokenSecret,$requestToken,$requestTokenSecret){
  
   if(isset($accessToken)){
       $this->ini_array['Twitter']['accessToken'] = $accessToken;
   }
   if(isset($accessTokenSecret)){
       $this->ini_array['Twitter']['accessTokenSecret'] = $accessTokenSecret;
   }
   if(isset($requestToken)){
       $this->ini_array['Twitter']['requestToken'] = $requestToken;
   }
   if(isset($requestTokenSecret)){
       $this->ini_array['Twitter']['requestTokenSecret'] = $requestTokenSecret;
   }
   $this->write_php_ini($this->ini_array,self::getCacheFilePath());
 }
 
 
 public static function getCacheFilePath(){
    return __DIR__ . "\\" . self::$config_path;
 }
 
 public static function getInstance(){
   if(!isset(self::$cacheFile)){
       self::$cacheFile = new CasheDataUtil();
   }
   return self::$cacheFile;
 }
 

 function read_php_ini(){
    $this->ini_array = parse_ini_file(self::getCacheFilePath(), true);
 }
 
 function write_php_ini($array, $file)
 {
    $res = array();
    foreach($array as $key => $val)
    {
        if(is_array($val))
        {
            $res[] = "[$key]";
            foreach($val as $skey => $sval) {
                if (is_array($sval)) {
                    foreach ($sval as $i=>$v) {
                        $res[] = "{$skey}[$i] = $v";
                    }
                }
                else {
                    $res[] = "$skey = $sval";
                }
            }
        }
        else $res[] = "$key = $val";
    }
    $this->safefilerewrite($file, implode("\r\n", $res));
 }

 function safefilerewrite($fileName, $dataToSave)
 {    
    file_put_contents($fileName, $dataToSave, LOCK_EX);    
 }

}


?>
