<?php


require_once '../OAuth/Oauth.php';

class TwitterAccessTokenStorage extends AccessTokenStorage
{
	public function setAccessToken($accesstoken){
	    $_SESSION['Twitter']['access_token'] = $accesstoken;
	}

    public function getAccessToken(){
        return $_SESSION['Twitter']['access_token'];
    }
   
    public function getAccessTokenSecret(){
        return $_SESSION['Twitter']['access_token_secret'];
    }
    
    public function setAccessTokenSecret($accesstokenSecret){
        $_SESSION['Twitter']['access_token_secret'] = $accesstokenSecret;
    }
    
    public function setRequestToken($requesttoken) {
        $_SESSION['Twitter']['request_token'] = $requesttoken;
    }

    public function getRequestToken() {
        return $_SESSION['Twitter']['request_token'];
    }
    
    public function getRequestTokenSecret() {
        return $_SESSION['Twitter']['request_token_secret'];
    }
    
    public function setRequestTokenSecret($requesttokenSecret) {  
        $_SESSION['Twitter']['request_token_secret'] = $requesttokenSecret;
    }
}

class TwitterOAuth extends OAuth{
   
  static $oauthVersion = "1.0";
   
  static $userAgent = 'TwitterOAuth v0.2.0-beta2';
  
  static $connectionTime = 30;
  
  static $timeOut = 30;
  
  static $requestFlatType = "text/html; charset=UTF-8";
  
  static $requestJsonType = "application/x-www-form-urlencoded; charset=UTF-8";
  
  static $requestTokenUrl = "https://api.twitter.com/oauth/request_token";
  
  static $loginAuthUrl = "https://api.twitter.com/oauth/authenticate";
  
  static $accessTokenUrl = "https://api.twitter.com/oauth/access_token";
  
  static $basic_url = "https://api.twitter.com/";
  
  
  public function getBaseUrl(){
     return self::$basic_url;
  }
  
  public function __construct($config = array()) {
     $this->setAccessTokenStorage(new TwitterAccessTokenStorage());
     parent::__construct($config);
  }
  
  protected function getOauthVersion(){
     return  self::$oauthVersion;
  }
  
  protected function getUserAgent() {
     return self::$userAgent;
  }
  
  protected function getConnectTimeout() {
    return self::$connectionTime;
  }
  
  protected function getTimeout() {
    return self::$timeOut;
  }
  
  protected function getRequestHeader($returnType) {
    if(strcasecmp($returnType,'json')==0){
       return NULL;
       //return array(self::$requestJsonType);
    }else{
       return NULL;
    }
  }
  
  protected function getAuthUrlParameter(){
     return NULL;
  }
  
  protected function getOAuth1_Auth_URL(){
     return self::$requestTokenUrl;
  }
  
  protected function getOAuth1_LoginToken_URL() {
     return self::$loginAuthUrl;
  }
   
  protected function getOAuth1_AccessToken_URL() {
     return self::$accessTokenUrl;
  }
  
  protected function processRequestToken($response){
      $resultArray = OAuth::parse_parameters($response);
      $this->setRequestTokenSecret($resultArray['oauth_token_secret']);
      $this->setRequestToken($resultArray['oauth_token']);
  }
  
  protected function processAccessToken($response) {
      $resultArray = OAuth::parse_parameters($response);
      $this->setAccessTokenSecret($resultArray['oauth_token_secret']);
      $this->setAccessToken($resultArray['oauth_token']);
  }
}

?>
